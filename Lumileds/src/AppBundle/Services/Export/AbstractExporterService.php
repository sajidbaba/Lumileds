<?php

namespace AppBundle\Services\Export;

use AppBundle\Entity\Cell;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Technology;
use AppBundle\Repository\CellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liuggio\ExcelBundle\Factory as ExcelFactory;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractExporterService
{
    /** @var ExcelFactory|null */
    protected $excelService = null;

    /** @var \PHPExcel */
    protected $excelObject;

    /** @var array */
    protected $filters = [];

    /** @var CellRepository */
    private $repo;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param ExcelFactory $excelService
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        ExcelFactory $excelService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->excelService = $excelService;
        $this->repo = $em->getRepository(Cell::class);
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Handle export
     *
     * @return StreamedResponse
     */
    public function handleExport(): StreamedResponse
    {
        $this->generateExcel();
        $response = $this->createResponse();

        return $response;
    }

    /**
     * Create new excel object with data
     */
    private function generateExcel(): void
    {
        $this->excelObject = $this->excelService->createPHPExcelObject();
        $this->excelObject->setActiveSheetIndex(0);

        $this->fillValues();
    }

    abstract protected function fillValues();

    /**
     * Create streamed response with generated excel file
     *
     * @return StreamedResponse
     */
    private function createResponse(): StreamedResponse
    {
        $writer = $this->excelService->createWriter($this->excelObject, 'Excel2007');
        $response =  $this->excelService->createStreamedResponse($writer);

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'export-'.date('Y-m-d-h-i-s').'.xlsx'
        );
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('set-cookie', 'fileDownload=true; path=/', false);

        return $response;
    }

    /**
     * @param array $filters
     */
    public function setFilters(array $filters = []): void
    {
        if (
            $filters['reporting'] ||
            (
                $this->tokenStorage->getToken() &&
                $this->tokenStorage->getToken()->getUser() &&
                !$this->tokenStorage->getToken()->getUser()->isAdmin()
            )
        ) {
            $this->filters = [
                'indicators' => [
                    Indicator::INDICATOR_MARKET_VOLUME,
                    Indicator::INDICATOR_MARKET_VALUE_USD,
                    Indicator::INDICATOR_LL_SALES_USD,
                    Indicator::INDICATOR_PARC,
                ],
                'technologies' => [
                    Technology::TECHNOLOGY_HL_HALOGEN,
                    Technology::TECHNOLOGY_HL_LED_RF,
                    Technology::TECHNOLOGY_HL_NON_HALOGEN,
                    Technology::TECHNOLOGY_HL_XENON,
                    Technology::TECHNOLOGY_SL_LED_RF,
                    Technology::TECHNOLOGY_SL_HIPER,
                    Technology::TECHNOLOGY_SL_CONV,
                    null
                ],
            ];
        } else {
            $this->filters = $filters;
        }
    }
}
