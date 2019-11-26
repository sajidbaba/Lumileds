<?php

namespace AppBundle\Controller\Contribution;

use AppBundle\Entity\ContributionCountryRequest;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Rest\Route("contribution")
 *
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminController extends Controller
{
    /**
     * @Rest\Get("/", name="contributions.admin.list")
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
     * @Rest\Get("/{country}", name="contributions.admin.view")
     *
     * @Template()
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return array
     */
    public function viewAction(ContributionCountryRequest $contributionCountryRequest): array
    {
        return [
            'contributionCountryRequest' => $contributionCountryRequest,
        ];
    }
}
