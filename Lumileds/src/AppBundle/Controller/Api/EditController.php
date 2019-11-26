<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Version;
use AppBundle\Model\Cell;
use AppBundle\Services\ApiManager;
use AppBundle\Services\Export\SheetExporterService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Rest\Route("api")
 */
class EditController extends FOSRestController
{
    /**
     * @Rest\Get("/table", name="api.table.get")
     *
     * @param Request $request
     *
     * @return \AppBundle\Model\Table
     */
    public function tableAction(Request $request)
    {
        return $this->get(ApiManager::class)->getTable($request->query->all());
    }

    /**
     * @Rest\Get("/version/{id}", name="api.table.version", requirements={"id"="\d+"})
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param int $id
     *
     * @return \AppBundle\Model\Table
     */
    public function tableVersionAction(Request $request, int $id)
    {
        return $this->get(ApiManager::class)->getTableVersion($id, $request->query->all());
    }

    /**
     * @Rest\Post("/calculate", name="api.table.calculate")
     *
     * @Security("has_role('ROLE_CONTRIBUTOR')")
     *
     * @param Request $request
     *
     * @return \AppBundle\Model\Cell[]
     */
    public function calculateAction(Request $request)
    {
        $cells = $this->get('jms_serializer')->deserialize(
            $request->getContent(),
            'array<'.Cell::class.'>',
            'json'
        );

        return $this->get(ApiManager::class)->calculate($cells);
    }

    /**
     * @Rest\Post("/save", name="api.table.save")
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function saveAction(Request $request): View
    {
        $cellArray = $this->get('jms_serializer')->deserialize(
            json_encode($request->get('cells')),
            'array<'.Cell::class.'>',
            'json'
        );

        $this->get(ApiManager::class)->saveCells($cellArray, $request->get('versionName'));

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/regions", name="api.regions.list")
     */
    public function regionsAction()
    {
        return $this->get(ApiManager::class)->getRegions();
    }

    /**
     * @Rest\Get("/countries", name="api.countries.list")
     * @Cache(public=true)
     */
    public function countriesAction()
    {
        return $this->get(ApiManager::class)->getCountries();
    }

    /**
     * @Rest\Get("/indicators", name="api.indicators.list")
     * @Cache(public=true)
     */
    public function indicatorAction()
    {
        return $this->get(ApiManager::class)->getIndicators();
    }

    /**
     * @Rest\Get("/technologies", name="api.technologies.list")
     * @Cache(public=true)
     */
    public function technologiesAction()
    {
        return $this->get(ApiManager::class)->getTechnologies();
    }

    /**
     * @Rest\Get("/years", name="api.years.list")
     * @Cache(public=true)
     */
    public function yearAction()
    {
        return $this->get(ApiManager::class)->getYears($this->getUser());
    }

    /**
     * @Rest\Get("/upload-status", name="api.upload.status")
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return array
     */
    public function getUploadStatusAction(Request $request): array
    {
        return [
            'status' => $this->get(ApiManager::class)->getUploadStatus($request->get('hash')),
        ];
    }

    /**
     * @Rest\Post("/version/{id}/edit-name", name="api.version.edit_name", options={"expose"=true})
     *
     * @Rest\RequestParam(name="value", nullable=false)
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Version $version
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function editVersionNameAction(Version $version, ParamFetcherInterface $paramFetcher): View
    {
        $version->setName($paramFetcher->get('value'))
            ->setCycle(true);

        $this->getDoctrine()->getManager()->flush();

        return View::create([], Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/sheet/export", name="api.sheet.export", options={"expose"=true})
     *
     * @Rest\QueryParam(name="segment", requirements="(LV|HV|2W)")
     * @Rest\QueryParam(map=true, name="regions", requirements="\d+")
     * @Rest\QueryParam(map=true, name="markets", requirements="\d+")
     * @Rest\QueryParam(map=true, name="technologies", requirements="\d+")
     * @Rest\QueryParam(map=true, name="indicators", requirements="\d+")
     * @Rest\QueryParam(name="reporting")
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return StreamedResponse
     */
    public function exportSheetAction(ParamFetcherInterface $paramFetcher): StreamedResponse
    {
        $sheetExport = $this->get(SheetExporterService::class);
        $sheetExport->setFilters($paramFetcher->all());
        $response = $sheetExport->handleExport();

        return $response;
    }
}
