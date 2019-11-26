<?php

namespace AppBundle\Services\Queue;

use AppBundle\Entity\Country;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Segment;
use AppBundle\Entity\SheetQueue;
use AppBundle\Entity\Technology;
use AppBundle\Exception\NotFoundDependencyException;
use AppBundle\Services\CellValidator;
use AppBundle\Services\CellWorkerService;
use AppBundle\Services\SheetWorkerService;
use AppBundle\Services\VersioningService;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Stopwatch\Stopwatch;

class SheetProcessorConsumer implements ConsumerInterface
{
    /** @var SheetWorkerService */
    private $sheetWorker;

    /** @var CellWorkerService */
    private $cellWorker;

    /** @var EntityManagerInterface */
    private $em;

    /** @var VersioningService */
    private $versioningService;

    /** @var LoggerInterface */
    private $logger;

    /** @var Stopwatch */
    private $stopwatch;

    /** @var CellValidator */
    private $cellValidator;

    /**
     * SheetProcessorConsumer constructor.
     *
     * @param SheetWorkerService $sheetWorker
     * @param CellWorkerService $cellWorker
     * @param EntityManagerInterface $em
     * @param VersioningService $versioningService
     * @param LoggerInterface $logger
     * @param Stopwatch $stopwatch
     * @param CellValidator $cellValidator
     */
    public function __construct(
        SheetWorkerService $sheetWorker,
        CellWorkerService $cellWorker,
        EntityManagerInterface $em,
        VersioningService $versioningService,
        LoggerInterface $logger,
        Stopwatch $stopwatch,
        CellValidator $cellValidator
    ) {
        $this->sheetWorker = $sheetWorker;
        $this->cellWorker = $cellWorker;
        $this->em = $em;
        $this->versioningService = $versioningService;
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
        $this->cellValidator = $cellValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(AMQPMessage $msg): void
    {
        $sheetId = $msg->getBody();

        $this->stopwatch->start('queue-'.$sheetId);
        $this->logger->log(Logger::INFO, 'Queue item: '.$sheetId);

        /** @var SheetQueue $queuedEntity */
        $queuedEntity = $this->em->getRepository(SheetQueue::class)->find($sheetId);

        if (!$queuedEntity) {
            $this->logger->log(Logger::NOTICE, 'Queued item not found: '.$sheetId);
            return;
        }

        $this->versioningService->initVersion(
            $queuedEntity->getUser(),
            null,
            $queuedEntity->getHash()
        );

        try {
            $file = new File($queuedEntity->getFilePath());
        } catch (FileNotFoundException $e) {
            $this->logger->log(Logger::ERROR, 'Exception: '.$e->getMessage());
            return;
        }

        if ($file->isReadable()) {
            $this->cellWorker->setFilter($this->createFilter($file));

            try {
                try {
                    $excelObject = $this->sheetWorker->readSheet($file->getRealPath());
                    $sheetRows = $this->sheetWorker->parseSheet($excelObject);
                    $this->sheetWorker->createCells($sheetRows);
                    $this->cellWorker->processCells(true);
                } catch (NotFoundDependencyException $exception) {
                    $indicator = $this->em->getRepository(Indicator::class)->find($exception->getIndicatorId());
                    $technology = $this->em->getRepository(Technology::class)->find($exception->getTechnologyId());
                    $segment = $this->em->getRepository(Segment::class)->find($exception->getSegmentId());

                    $this->cellValidator->addError('validation.file_error',
                        [
                            '%indicator%' => $indicator->getName(),
                            '%technology%' => $technology->getName(),
                            '%segment%' => $segment->getName(),
                            '%country%' => $exception->getCountryName(),
                        ]
                    );

                    throw $exception;
                }
            } catch (\Throwable $exception) {
                $message = $exception->getMessage()."\n".
                    "\t".$exception->getFile()."\n".
                    "\t".$exception->getLine();
                $this->logger->log(Logger::CRITICAL, $message);

                $queuedEntity->setProcessed(true);
                $this->em->flush();

                return;
            }

            $queuedEntity->setProcessed(true);
            $this->em->flush();

            $this->versioningService->createVersion();

            $event = $this->stopwatch->stop('queue-'.$sheetId);

            $this->logger->log(
                Logger::INFO,
                sprintf(
                    'Parsed: %s (%d row(s)), Time: %s, Memory: %s',
                    $file->getRealPath(),
                    count($sheetRows),
                    $this->formatDuration($event->getDuration()),
                    $this->formatMemory($event->getMemory())
                )
            );

            $this->em->clear();
        }
    }

    /**
     * @param File $file
     *
     * @return array
     */
    private function createFilter(File $file): array
    {
        list($market, $segment) = explode('-', $file->getBasename('.'.$file->guessExtension()));

        /** @var Country $marketEntity */
        $marketEntity = $this->em->getRepository(Country::class)->findOneByName($market);

        /** @var Segment $segmentEntity */
        $segmentEntity = $this->em->getRepository(Segment::class)->findOneByName($segment);

        return [
            'countryId' => $marketEntity->getId(),
            'segmentId' => $segmentEntity->getId(),
        ];
    }

    /**
     * @param int $bytes Memory in bytes
     *
     * @return string
     */
    private function formatMemory($bytes): string
    {
        return round($bytes / 1000 / 1000, 2).' MB';
    }

    /**
     * @param int $microseconds Time in microseconds
     *
     * @return string
     */
    private function formatDuration($microseconds): string
    {
        return ($microseconds / 1000).' s';
    }
}
