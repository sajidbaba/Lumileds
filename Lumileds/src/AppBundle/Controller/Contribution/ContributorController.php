<?php

namespace AppBundle\Controller\Contribution;

use AppBundle\Entity\ContributionCountryRequest;
use AppBundle\Entity\ContributionIndicatorRequest;
use AppBundle\Services\ContributionIndicatorService;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Rest\Route("contribution/contributor")
 *
 * @Security("has_role('ROLE_CONTRIBUTOR')")
 */
class ContributorController extends Controller
{
    /**
     * @Rest\Get("/", name="contributions.contributor.list")
     *
     * @Template()
     *
     * @return array
     */
    public function listAction(): array
    {
        return [];
    }

    /**
     * @Rest\Get("/{country}", name="contributions.contributor.country")
     *
     * @Template()
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return array
     */
    public function countryAction(ContributionCountryRequest $contributionCountryRequest): array
    {
        return [
            'contributionCountryRequest' => $contributionCountryRequest,
        ];
    }

    /**
     * @Rest\Get("/indicator/{id}", name="contributions.contributor.indicator")
     *
     * @Template()
     *
     * @param ContributionIndicatorRequest $contributionIndicatorRequest
     *
     * @return array
     */
    public function indicatorAction(ContributionIndicatorRequest $contributionIndicatorRequest): array
    {
        $indicatorGroupId = $contributionIndicatorRequest->getIndicatorGroup();
        $countryId = $contributionIndicatorRequest->getContributionCountryRequest()->getCountry()->getId();

        $contributionIndicatorService = $this->get(ContributionIndicatorService::class);
        $contributionIndicatorService->setIndicatorGroup($indicatorGroupId);

        return [
            'title' => $contributionIndicatorService->getTitle(),
            'indicatorGroup' => $contributionIndicatorService->getIndicatorGroup(),
            'contributionIndicatorRequest' => $contributionIndicatorRequest,
            'countryId' => $countryId,
        ];
    }
}
