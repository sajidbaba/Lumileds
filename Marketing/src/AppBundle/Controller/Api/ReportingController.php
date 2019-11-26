<?php

namespace AppBundle\Controller\Api;

use AppBundle\Model\Reporting\Reporting;
use AppBundle\Services\ReportingManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("api/reporting")
 */
class ReportingController extends FOSRestController
{
    /**
     * @Rest\Get("/parc-segment", name="api.reporting.parc_segment")
     * @Cache(public=true)
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getParcBySegmentAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getParcBySegment($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/parc-region", name="api.reporting.parc-region")
     * @Cache(public=true)
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getParcByRegionAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getParcByRegion($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/parc-technology", name="api.reporting.parc_technology")
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getParcByTechnologyAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getParcByTechnology($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/market-volume-region", name="api.reporting.market_volume_region")
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getMarketVolumeByRegionAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getMarketVolumeByRegion($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/market-size-region", name="api.reporting.market_size_region")
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getMarketSizeByRegionAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getMarketSizeByRegion($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/market-volume-segment", name="api.reporting.market_volume_segment")
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getMarketVolumeBySegmentAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getMarketVolumeBySegment($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/market-size-segment", name="api.reporting.market_size_segment")
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getMarketSizeBySegmentAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getMarketSizeBySegment($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/market-volume-technology", name="api.reporting.market_volume_technology")
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getMarketVolumeByTechnologyAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getMarketVolumeByTechnology($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/market-size-technology", name="api.reporting.market_size_technology")
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getMarketSizeByTechnologyAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getMarketSizeByTechnology($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/market-share-region", name="api.reporting.market_share_region")
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getMarketShareByRegionAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getMarketShareByRegion($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Get("/market-share-technology", name="api.reporting.market_share_technology")
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getMarketShareByTechnologyAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getMarketShareByTechnology($this->getUser(), $request->query->all());
    }

    /**
     * @Rest\Post("/simple-indicator-chart", name="api.reporting.simple_indicator_chart")
     * @Cache(public=true)
     *
     * @param Request $request
     *
     * @return Reporting
     */
    public function getSimpleIndicatorChartAction(Request $request): Reporting
    {
        return $this->get(ReportingManager::class)->getSimpleIndicatorChart($request->request->all());
    }
}
