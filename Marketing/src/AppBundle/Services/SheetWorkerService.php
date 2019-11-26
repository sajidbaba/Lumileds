<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Country;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Segment;
use AppBundle\Entity\SheetQueue;
use AppBundle\Entity\Technology;
use AppBundle\Exception\EmptyLookupException;
use AppBundle\Exception\UploadSheetException;
use AppBundle\Indicators\Input\InputIndicatorInterface;
use AppBundle\Indicators\Output\OutputIndicatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Liuggio\ExcelBundle\Factory as ExcelFactory;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SheetWorkerService
{
    /**
     * Market column position in sheet.
     */
    const MARKET_COLUMN = 'A';

    /**
     * Segment column position in sheet.
     */
    const SEGMENT_COLUMN = 'B';

    /**
     * Indicator column position in sheet.
     */
    const INDICATOR_COLUMN = 'C';

    /**
     * Technology column position in sheet.
     */
    const TECHNOLOGY_COLUMN = 'D';

    /**
     * Year columns position in sheet.
     */
    const YEAR_COLUMNS = [
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q'
    ];

    /**
     * Actual data in the sheet starts from this row.
     */
    const START_ROW = 2;

    /** @var ExcelFactory|null */
    private $excelService = null;

    /** @var EntityManagerInterface */
    private $em;

    /** @var IndicatorService */
    private $indicatorRegistry;

    /** @var CellValidator */
    private $validator;

    /** @var VersioningService */
    private $versioningService;

    /** @var ProducerInterface */
    private $queueProducer;

    /**
     * Path where split spreadsheet are stored prior to calculation.
     *
     * @var string
     */
    private $sheetSaveTmpDirectory = null;

    /**
     * Initial upload sheet headers.
     *
     * @var array
     */
    private $sheetHeaders = [];

    /**
     * All country names
     *
     * @var string[]
     */
    private $countryNames = [];

    /**
     * All segment names
     *
     * @var string[]
     */
    private $segmentNames = [];

    /**
     * SheetWorkerService constructor.
     *
     * @param ExcelFactory $excelService
     * @param EntityManagerInterface $em
     * @param IndicatorService $indicatorRegistry
     * @param ProducerInterface $queueProducer
     * @param CellValidator $validator
     * @param VersioningService $versioningService
     */
    public function __construct(
        ExcelFactory $excelService,
        EntityManagerInterface $em,
        IndicatorService $indicatorRegistry,
        ProducerInterface $queueProducer,
        CellValidator $validator,
        VersioningService $versioningService
    ) {
        $this->excelService = $excelService;
        $this->em = $em;
        $this->indicatorRegistry = $indicatorRegistry;
        $this->queueProducer = $queueProducer;
        $this->validator = $validator;
        $this->versioningService = $versioningService;

        $this->countryNames = $em->getRepository(Country::class)->findAllNames();
        $this->segmentNames = $em->getRepository(Segment::class)->findAllNames();

        // TODO: Might come from config.
        $this->sheetSaveTmpDirectory = '/tmp/lumileds/'.date('Ymd-His');
    }

    /**
     * Reads the file contents and creates an
     * excel object representation.
     *
     * @param string $filePath
     *
     * @return \PHPExcel
     */
    public function readSheet($filePath): \PHPExcel
    {
        $excelObject = $this->excelService->createPHPExcelObject($filePath);

        return $excelObject;
    }

    /**
     * Handles the sheet upload.
     *
     * @param UploadedFile $file Uploaded sheet object.
     *
     * @throws \PHPExcel_Exception
     * @throws UploadSheetException
     *
     * @return string
     */
    public function handleUpload(UploadedFile $file) : string
    {
        $this->validator->removeUploadErrors();
        $excelObject = $this->readSheet($file->getRealPath());

        $this->readSheetHeader($excelObject);
        $rows = $this->parseSheet($excelObject);
        $sheetResults = $this->splitSheetRows($rows);

        foreach ($sheetResults as $sheetResult) {
            $sheetQueueEntity = new SheetQueue();
            $sheetQueueEntity->setFilePath($sheetResult);
            $sheetQueueEntity->setProcessed(false);
            $this->em->persist($sheetQueueEntity);
            $this->em->flush();

            $this->queueProducer->publish($sheetQueueEntity->getId());
        }

        if (!isset($sheetQueueEntity)) {
            throw new UploadSheetException();
        }

        return $sheetQueueEntity->getHash();
    }

    /**
     * Splits sheet rows into separate sheets.
     *
     * One market and segment per sheet.
     *
     * @param array $rows
     *
     * @throws \PHPExcel_Exception
     *
     * @return array
     */
    private function splitSheetRows(array $rows): array
    {
        if (!$rows) {
            return [];
        }

        $rows = $this->sortRows($rows);

        $countryName = $this->prepareName($rows[0]['market']);
        $segmentName = $this->prepareName($rows[0]['segment']);
        $sheetName = $this->prepareName($countryName).'-'.$this->prepareName($segmentName);

        $sheetObject = null;
        if ($this->isCountrySegmentValid($countryName, $segmentName)) {
            $sheetObject = $this->createEmptySheet($this->sheetHeaders);
        }

        $currentRow = self::START_ROW;
        $resultSheets = [];

        foreach ($rows as $row) {
            $countryName = $this->prepareName($row['market']);
            $segmentName = $this->prepareName($row['segment']);

            if (!$this->isCountrySegmentValid($countryName, $segmentName)) {
                continue;
            }

            $newSheetName = $countryName.'-'.$segmentName;
            if ($newSheetName != $sheetName) {
                if ($sheetObject) {
                    $resultSheets[] = $this->saveSheetTmpFile($sheetObject, $sheetName);
                }

                $sheetName = $newSheetName;
                $sheetObject = $this->createEmptySheet($this->sheetHeaders);
                $currentRow = self::START_ROW;
            }

            // Set market.
            $sheetObject->getActiveSheet()->setCellValue(
                self::MARKET_COLUMN.$currentRow,
                $row['market']
            );

            // Set segment.
            $sheetObject->getActiveSheet()->setCellValue(
                self::SEGMENT_COLUMN.$currentRow,
                $row['segment']
            );

            // Set indicator.
            $sheetObject->getActiveSheet()->setCellValue(
                self::INDICATOR_COLUMN.$currentRow,
                $row['indicator']
            );

            // Set technology.
            $sheetObject->getActiveSheet()->setCellValue(
                self::TECHNOLOGY_COLUMN.$currentRow,
                $row['technology']
            );

            // Set cell value per year.
            foreach (self::YEAR_COLUMNS as $k => $yearColumn) {
                $yearValues = array_values($row['year_value']);
                if (isset($yearValues[$k])) {
                    $sheetObject->getActiveSheet()->setCellValue(
                        $yearColumn.$currentRow,
                        $yearValues[$k]
                    );
                }
            }

            $currentRow++;
        }

        $resultSheets[] = $this->saveSheetTmpFile($sheetObject, $sheetName);

        return $resultSheets;
    }

    /**
     * Validate if country and segment names are valid
     *
     * @param string $countryName
     * @param string $segmentName
     *
     * @return bool
     */
    private function isCountrySegmentValid(string $countryName, string $segmentName): bool
    {
        if (!in_array($countryName, $this->countryNames)) {
            $this->validator->addError('validation.country_error', ['%param%' => $countryName]);
            return false;
        }

        if (!in_array($segmentName, $this->segmentNames)) {
            $this->validator->addError('validation.segment_error', ['%param%' => $segmentName]);
            return false;
        }

        return true;
    }

    /**
     * @param array $headers
     *
     * @return \PHPExcel
     * @throws \PHPExcel_Exception
     */
    public function createEmptySheet(array $headers = [])
    {
        $sheetObject = $this->excelService->createPHPExcelObject();
        $sheetObject->setActiveSheetIndex(0);

        if (!empty($headers)) {
            $this->setSheetHeaders($sheetObject, $headers);
        }

        return $sheetObject;
    }

    /**
     * @param \PHPExcel $sheet
     * @param array $headers
     *
     * @throws \PHPExcel_Exception
     */
    public function setSheetHeaders(\PHPExcel $sheet, array $headers = [])
    {
        foreach ($headers as $column => $headerValue) {
            $sheet->getActiveSheet()
                ->setCellValue($column.(self::START_ROW - 1), $headerValue);
        }
    }

    /**
     * @param \PHPExcel $sheet
     * @param $fileName
     *
     * @return string
     * @throws \PHPExcel_Writer_Exception
     */
    public function saveSheetTmpFile(\PHPExcel $sheet, $fileName)
    {
        $filePath = $this->getSheetSaveTmpDirectory().'/'.$fileName.'.xlsx';

        $writer = $this->excelService->createWriter($sheet, 'Excel2007');
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * @return string
     */
    private function getSheetSaveTmpDirectory()
    {
        if (!is_dir($this->sheetSaveTmpDirectory)) {
            mkdir($this->sheetSaveTmpDirectory, 0755, true);
        }

        return $this->sheetSaveTmpDirectory;
    }

    /**
     * Parses the excel file object into array of rows.
     *
     * @param \PHPExcel $sheet
     *
     * @return array
     *   A set of rows with data.
     *
     * @throws \PHPExcel_Exception
     */
    public function parseSheet(\PHPExcel $sheet): array
    {
        $activeSheet = $sheet->getActiveSheet();

        $rows = [];
        for ($rowIndex = self::START_ROW; $rowIndex <= $activeSheet->getHighestRow(); $rowIndex++) {
            $rowData = [
                'market' => $activeSheet->getCell(self::MARKET_COLUMN.$rowIndex)->getValue(),
                'segment' => $activeSheet->getCell(self::SEGMENT_COLUMN.$rowIndex)->getValue(),
                'indicator' => $activeSheet->getCell(self::INDICATOR_COLUMN.$rowIndex)->getValue(),
                'technology' => $activeSheet->getCell(self::TECHNOLOGY_COLUMN.$rowIndex)->getValue(),
            ];

            if (empty(array_filter($rowData))) {
                continue;
            }

            foreach (self::YEAR_COLUMNS as $yearColumn) {
                // self::START_ROW  - 1 points to the row that is above the data,
                // in other words - the table header with column titles.
                // Needed to pick year values from columns header.
                $year = $activeSheet->getCell($yearColumn.(self::START_ROW - 1))->getValue();

                if (empty($year)) {
                    break;
                }

                $rowData['year_value'][$year] = $activeSheet->getCell($yearColumn.$rowIndex)
                    ->getValue();
            }

            $rows[] = $rowData;
        }

        return $rows;
    }

    /**
     * @param \PHPExcel $sheet
     *
     * @return void
     * @throws \PHPExcel_Exception
     * @throws UploadSheetException
     */
    private function readSheetHeader(\PHPExcel $sheet): void
    {
        $activeSheet = $sheet->getActiveSheet();

        $headerRowIndex = self::START_ROW - 1;

        $this->sheetHeaders = [
            self::MARKET_COLUMN => $activeSheet->getCell(self::MARKET_COLUMN.$headerRowIndex)->getValue(),
            self::SEGMENT_COLUMN => $activeSheet->getCell(self::SEGMENT_COLUMN.$headerRowIndex)->getValue(),
            self::INDICATOR_COLUMN => $activeSheet->getCell(self::INDICATOR_COLUMN.$headerRowIndex)->getValue(),
            self::TECHNOLOGY_COLUMN => $activeSheet->getCell(self::TECHNOLOGY_COLUMN.$headerRowIndex)->getValue(),
        ];

        foreach (self::YEAR_COLUMNS as $yearColumn) {
            $year = $activeSheet->getCell($yearColumn.$headerRowIndex)->getValue();
            $isYear = $year > 1970;

            if (!$isYear) {
                $this->validator->addError(
                    'validation.upload.year_error',
                    [
                        '%coordinates%' => $yearColumn.$headerRowIndex,
                        '%value%' => $year,
                    ]
                );
                $this->em->flush();

                throw new UploadSheetException();
            }

            $this->sheetHeaders[$yearColumn] = $year;
        }
    }

    /**
     * Creates cell entities from the parsed set of excel rows.
     *
     * @param array $input
     *   A set of rows of data.
     */
    public function createCells(array $input)
    {
        /** @var Cell|null $previousCell */
        $previousCell = null;

        $isInitialUploadInProgress = $this->em->getRepository(SheetQueue::class)->isInitialUploadInProgress();

        foreach ($input as $item) {
            try {
                $marketEntity = $this->lookupMarket($item['market']);
                $segmentEntity = $this->lookupSegment($item['segment']);
                $technologyEntity = $this->lookupTechnology($item['technology']);
                $indicatorEntity = $this->lookupIndicator($item['indicator'], $technologyEntity);
            } catch (EmptyLookupException $e) {
                $this->handleLookupError($e);
                continue;
            }

            $indicatorClass = $this->indicatorRegistry->getIndicatorById($indicatorEntity->getId());

            foreach ($item['year_value'] as $year => $value) {
                $cellEntity = $this->lookupCell($marketEntity, $segmentEntity, $indicatorEntity, $technologyEntity, $year);

                if (!$cellEntity) {
                    $cellEntity = new Cell();

                    $cellEntity->setCountry($marketEntity);
                    $cellEntity->setSegment($segmentEntity);
                    $cellEntity->setIndicator($indicatorEntity);
                    $cellEntity->setTechnology($technologyEntity);
                    $cellEntity->setYear($year);
                    $cellEntity->setIndicatorClass($indicatorClass);

                    $this->em->persist($cellEntity);
                } else {
                    $this->versioningService->trackCellOldValue($cellEntity->getId(), $cellEntity->getValue());
                }

                // Format only input indicators data.
                $isInputIndicator = $indicatorClass instanceof InputIndicatorInterface;
                $isOutputIndicator = $indicatorClass instanceof OutputIndicatorInterface;
                $isBoth = $isInputIndicator && $isOutputIndicator;

                if ($isBoth && !$indicatorClass->isEditable($cellEntity) && !$isInitialUploadInProgress) {
                    // For mixed indicators assume it's value as a
                    // formula, so clean the value.
                    $cellEntity->setValue(null);
                } elseif ($isInputIndicator || $isInitialUploadInProgress) {
                    if (is_numeric($value) || empty($value)) {
                        if ($previousCell && $cellEntity->getYear() - $previousCell->getYear() !== 1) {
                            $previousCell = null;
                        }

                        $cellEntity->setValue((float)$value);
                        $this->validator->validate($cellEntity, $previousCell);

                        $previousCell = $cellEntity;

                        if ($cellEntity->getId()) {
                            $this->versioningService->trackCellNewValue($cellEntity->getId(), $cellEntity->getValue());
                        }
                    } else {
                        $this->validator->addError(
                            'validation.cell.not_number',
                            [
                                '%indicator%' => $cellEntity->getIndicator()->getName(),
                                '%technology%' => $cellEntity->getTechnology()->getName(),
                                '%segment%' => $cellEntity->getSegment()->getName(),
                                '%country%' => $cellEntity->getCountry()->getName(),
                                '%year%' => $cellEntity->getYear(),
                                '%value%' => $value,
                            ]
                        );
                        continue;
                    }
                } else {
                    $cellEntity->setValue('');
                }
            }
        }

        $this->validator->persistQueuedErrors();
        $this->em->flush();
    }

    /**
     * Fetches the market entity based on it's name.
     *
     * @param string $name Market name.
     *
     * @throws EmptyLookupException
     * @throws NonUniqueResultException
     *
     * @return Country Entity object on success, null otherwise.
     */
    private function lookupMarket($name): Country
    {
        $name = $this->prepareName($name);
        $marketEntity = $this->em->getRepository(Country::class)->findOneActiveByName($name);

        if ($marketEntity === null) {
            throw new EmptyLookupException('validation.country_error', $name);
        }

        return $marketEntity;
    }

    /**
     * Fetches the segment entity based on it's name.
     *
     * @param string $name Segment name.
     *
     * @throws EmptyLookupException
     *
     * @return Segment Entity object on success, null otherwise.
     */
    private function lookupSegment($name): Segment
    {
        $name = $this->prepareName($name);
        $segmentEntity = $this->em->getRepository(Segment::class)->findOneByName($name);

        if ($segmentEntity === null) {
            throw new EmptyLookupException('validation.segment_error', $name);
        }

        return $segmentEntity;
    }

    /**
     * Fetches the indicator entity based on it's name and,
     * optionally, technology name.
     *
     * @param string $name Indicator name.
     * @param Technology|null $technology Technology entity object.
     *
     * @throws EmptyLookupException
     *
     * @return Indicator Entity object on success, null otherwise.
     */
    private function lookupIndicator(
        $name,
        Technology $technology = null
    ): Indicator {
        $name = $this->prepareName($name);
        $indicatorEntity = $this->em->getRepository(Indicator::class) ->findByNameAndTechnology($name, $technology);

        if ($indicatorEntity === null) {
            throw new EmptyLookupException('validation.indicator_error', $name);
        }

        return $indicatorEntity;
    }

    /**
     * Fetches the technology entity based on it's name.
     *
     * @param string $name Technology name.
     *
     * @throws EmptyLookupException
     *
     * @return Technology|null Entity object on success, null otherwise.
     */
    private function lookupTechnology($name): ?Technology
    {
        // Sometimes, technology can be null for a certain indicator.
        if (!$name) {
            return null;
        }

        $name = $this->prepareName($name);
        $technologyEntity = $this->em->getRepository(Technology::class)->findOneByName($name);

        if ($technologyEntity === null) {
            throw new EmptyLookupException('validation.technology_error', $name);
        }

        return $technologyEntity;
    }

    /**
     * Fetches a cell entity based on a certain criteria.
     *
     * @param Country $market
     *   Market entity.
     * @param Segment $segment
     *   Segment entity.
     * @param Indicator $indicator
     *   Indicator entity.
     * @param Technology|null $technology
     *   Technology entity.
     * @param int $year
     *   Cell year value.
     *
     * @return Cell|null
     *   Cell entity on success, null otherwise.
     */
    private function lookupCell(
        Country $market,
        Segment $segment,
        Indicator $indicator,
        Technology $technology = null,
        int $year
    ): ?Cell {
        $cellEntity = $this->em->getRepository(Cell::class)->findOneBy([
            'country' => $market ?: null,
            'segment' => $segment ?: null,
            'indicator' => $indicator ?: null,
            'technology' => $technology ?: null,
            'year' => $year,
        ]);

        return $cellEntity;
    }

    /**
     * Handle lookup error
     *
     * @param EmptyLookupException $e
     */
    private function handleLookupError(EmptyLookupException $e)
    {
        $this->validator->addError($e->getMessage(), ['%param%' => $e->getName()]);
    }

    /**
     * Remove inconsiderable characters
     *
     * @param string $name
     *
     * @return string
     */
    private function prepareName(string $name): string
    {
        return trim($name);
    }

    /**
     * Sort rows by market and segment
     *
     * @param array $rows
     *
     * @return array
     */
    private function sortRows(array $rows): array
    {
        usort($rows, function ($a, $b) {
            return $a['market'] <=> $b['market'] ?: $a['segment'] <=> $b['segment'];
        });

        return $rows;
    }
}
