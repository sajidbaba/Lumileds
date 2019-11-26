<?php

namespace AppBundle\Controller;

use AppBundle\Entity\SavedFilter;
use AppBundle\Services\Export\ChartExporterService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Rest\Route("reporting")
 */
class ReportingController extends Controller
{
    /**
     * @Rest\Get("", name="reporting")
     *
     * @param EntityManagerInterface $em
     *
     * @Template()
     */
    public function reportingAction(EntityManagerInterface $em): array
    {
        return [
            'savedFilter' => $em->getRepository(SavedFilter::class)->findOneBy(['user' => $this->getUser()]),
        ];
    }

    /**
     * @deprecated it was replaced by download functionality
     *
     * Export chart table
     *
     * @Rest\Get("/export/{id}", name="reporting.export", requirements={"id"="\d+"})
     *
     * @Rest\QueryParam(map=true, name="years", requirements="\d+")
     * @Rest\QueryParam(map=true, name="segments", requirements="(LV|HV|2W)")
     * @Rest\QueryParam(map=true, name="regions", requirements="\d+")
     * @Rest\QueryParam(map=true, name="markets", requirements="\d+")
     * @Rest\QueryParam(name="technologySet")
     *
     * @param int $id
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return StreamedResponse
     */
    private function exportAction(int $id, ParamFetcherInterface $paramFetcher): StreamedResponse
    {
        $filters = $paramFetcher->all();

        /** @var ChartExporterService $chartExporter */
        $chartExporter = $this->get(ChartExporterService::class);
        $chartExporter->setFilters($filters);
        $chartExporter->setChart($id);
        $response = $chartExporter->handleExport();

        return $response;
    }
}
