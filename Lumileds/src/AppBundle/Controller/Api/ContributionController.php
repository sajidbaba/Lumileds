<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\ContributionCountryRequest;
use AppBundle\Entity\ContributionIndicatorRequest;
use AppBundle\Entity\Region;
use AppBundle\Services\ContributionManager;
use AppBundle\Services\MailerService;
use AppBundle\Services\RegionService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Contribution;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Rest\Route("api")
 *
 * @Security("has_role('ROLE_CONTRIBUTOR')")
 */
class ContributionController extends FOSRestController
{
    /**
     * @Rest\Post("/regions/{region_id}/contribution", name="api.contribution_request.request", requirements={"region_id"="\d+"})
     *
     * @Security("has_role('ROLE_CONTRIBUTOR')")
     *
     * @ParamConverter("region", options={"mapping"={"region_id"="id"}})
     * @Rest\RequestParam(name="deadline", nullable=false)
     * @Rest\RequestParam(name="carbonCopy", nullable=true)
     *
     * @param Region $region
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function requestContributionAction(Region $region, ParamFetcherInterface $paramFetcher): View
    {
        $contributionRequest = $this->get(ContributionManager::class)->requestContribution(
            $region,
            $paramFetcher->get('deadline')
        );

        $this->get(MailerService::class)->sendFeedbackRequest(
            $this->getUser(),
            $contributionRequest,
            $paramFetcher->get('carbonCopy')
        );

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/country/{country}/send-deadline-reminder", name="api.contribution_request.send_deadline_reminder", requirements={"country"="\d+"})
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return View
     */
    public function sendDeadlineReminderToCountryAction(ContributionCountryRequest $contributionCountryRequest): View
    {
        $this->get(MailerService::class)->sendDeadlineReminderMailToCounty(
            $this->getUser(),
            $contributionCountryRequest
        );

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/contribution/{country}/table", name="api.contribution.admin.table", requirements={"country"="\d+"})
     *
     * @Rest\QueryParam(name="segment", nullable=false)
     * @ParamConverter("contributionCountryRequest",
     *     class="AppBundle:ContributionCountryRequest",
     *     options={"repository_method" = "findWithJoins"}
     * )
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return \AppBundle\Model\Table
     */
    public function adminContributionTableAction(
        ContributionCountryRequest $contributionCountryRequest,
        ParamFetcherInterface $paramFetcher
    ) {
        return $this->get(ContributionManager::class)->getAdminTable(
            $this->getUser(),
            $contributionCountryRequest,
            $paramFetcher->get('segment')
        );
    }

    /**
     * @Rest\Get("/contribution/contributor/{id}/table", name="api.contribution.contributor.table", requirements={"id"="\d+"})
     *
     * @Rest\QueryParam(name="segment", nullable=false)
     * @ParamConverter("contributionIndicatorRequest", class="AppBundle:ContributionIndicatorRequest")
     *
     * @param ContributionIndicatorRequest $contributionIndicatorRequest
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return \AppBundle\Model\Table
     */
    public function contributorContributionTableAction(
        ContributionIndicatorRequest $contributionIndicatorRequest,
        ParamFetcherInterface $paramFetcher
    ) {
        return $this->get(ContributionManager::class)->getContributorTables(
            $this->getUser(),
            $contributionIndicatorRequest,
            $paramFetcher->get('segment')
        );
    }

    /**
     * @Rest\Get("/contribution/{country}/comments", name="api.contribution.list_comments", requirements={"country"="\d+"})
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return Contribution[]|array
     */
    public function listContributionCommentsAction(ContributionCountryRequest $contributionCountryRequest)
    {
        return $contributionCountryRequest
            ->getContributions()
            ->filter(function(Contribution $contribution) {
                return $contribution->getComment() !== null;
            });
    }

    /**
     * @Rest\Post("/contribution/{country}/contributor-comment", name="api.contribution.contributor_comment", requirements={"country"="\d+"})
     *
     * @Rest\RequestParam(name="comment", nullable=true)
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function saveContributorCommentAction(ContributionCountryRequest $contributionCountryRequest, ParamFetcherInterface $paramFetcher): View
    {
        $this->get(ContributionManager::class)->saveContributorComment(
            $this->getUser(),
            $contributionCountryRequest,
            $paramFetcher->get('comment')
        );

        $this->get(MailerService::class)->sendNewFeedbackMailToContributor($contributionCountryRequest);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/contribution/{country}/contributor-feedback", name="api.contribution.contributor_feedback", requirements={"country"="\d+"})
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return View
     */
    public function saveContributorFeedbackAction(ContributionCountryRequest $contributionCountryRequest): View
    {
        if ($contributionCountryRequest->getStatus() === ContributionCountryRequest::STATUS_APPROVED) {
            throw new NotFoundHttpException();
        }

        $this->get(ContributionManager::class)->saveContributorFeedback($this->getUser(), $contributionCountryRequest);
        $this->get(MailerService::class)->sendNewFeedbackMail($contributionCountryRequest);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/contribution/{id}/contributor-indicator-feedback", name="api.contribution.contributor_indicator_feedback", requirements={"id"="\d+"})
     *
     * @Rest\RequestParam(name="trackedCells", nullable=true)
     * @Rest\RequestParam(name="segment", nullable=false)
     *
     * @param ContributionIndicatorRequest $contributionIndicatorRequest
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function saveContributorIndicatorFeedbackAction(ContributionIndicatorRequest $contributionIndicatorRequest, ParamFetcherInterface $paramFetcher): View
    {
        /**
        if ($contributionIndicatorRequest->getStatus() === ContributionCountryRequest::STATUS_APPROVED) {
            throw new NotFoundHttpException();
        }
        */

        $this->get(ContributionManager::class)->saveContributorIndicatorFeedback(
            $this->getUser(),
            $contributionIndicatorRequest,
            $paramFetcher->get('trackedCells'),
            $paramFetcher->get('segment')
        );

        $this->get(MailerService::class)->sendNewFeedbackMailToContributor($contributionIndicatorRequest->getContributionCountryRequest());

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/contribution/{country}/admin-feedback", name="api.contribution.admin_feedback", requirements={"country"="\d+"})
     *
     * @Rest\RequestParam(name="comment", nullable=true)
     * @Rest\RequestParam(name="segment", nullable=false)
     * @Rest\RequestParam(name="reviewedRows", nullable=true)
     * @Rest\RequestParam(name="trackedCells", nullable=true)
     * @Rest\RequestParam(name="approveType", nullable=true)
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return View
     */
    public function saveAdminFeedbackAction(ContributionCountryRequest $contributionCountryRequest, ParamFetcherInterface $paramFetcher): View
    {
        $this->get(ContributionManager::class)->saveAdminFeedback(
            $this->getUser(),
            $contributionCountryRequest,
            $paramFetcher->get('comment'),
            $paramFetcher->get('segment'),
            $paramFetcher->get('reviewedRows'),
            $paramFetcher->get('trackedCells'),
            $paramFetcher->get('approveType')
        );

        $this->get(MailerService::class)->sendNewFeedbackMail($contributionCountryRequest);

        return View::create(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/user/regions", name="api.user.regions")
     *
     * @Security("has_role('ROLE_CONTRIBUTOR')")
     */
    public function regionsRelatedToUserAction()
    {
        return $this->get(RegionService::class)->getRegionsRelatedToUser($this->getUser());
    }

    /**
     * @Rest\Get("/contribution-country-request/{country}", name="api.contribution_country_request", requirements={"country"="\d+"})
     *
     * @Security("has_role('ROLE_CONTRIBUTOR')")
     *
     * @ParamConverter("contributionCountryRequest",
     *     class="AppBundle:ContributionCountryRequest",
     *     options={"repository_method" = "findWithJoins"}
     * )
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return ContributionCountryRequest
     */
    public function contributionCountryRequestAction(ContributionCountryRequest $contributionCountryRequest)
    {
        return $contributionCountryRequest;
    }
}
