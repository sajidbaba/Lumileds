<?php

namespace AppBundle\Services;

use AppBundle\Entity\ContributionCountryRequest;
use AppBundle\Entity\Region;
use AppBundle\Entity\ContributionRequest;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Templating\EngineInterface;

class MailerService
{
    /** @var \Swift_Mailer */
    private $mailer;

    /** @var EngineInterface */
    private $templateEngine;

    /** @var EntityManagerInterface */
    private $em;

    /** @var string*/
    private $sender;

    /**
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $templateEngine
     * @param EntityManagerInterface $em
     * @param string $sender
     */
    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $templateEngine,
        EntityManagerInterface $em,
        string $sender
    ) {
        $this->mailer = $mailer;
        $this->templateEngine = $templateEngine;
        $this->em = $em;
        $this->sender = $sender;
    }

    /**
     * @param Region $region
     *
     * @return void
     */
    public function sendDeadlineReminderMailToRegion(Region $region): void
    {
        $message = (new \Swift_Message())
            ->setSubject('Deadline reminder')
            ->setFrom($this->sender)
            ->setBody(
                $this->templateEngine->render(
                    '@App/Emails/deadline_reminder_to_region.html.twig',
                    [
                        'regionName' => $region->getName(),
                        'country' => $region->getName(),
                        'deadline' => $region->getContributionRequest()->getDeadline(),
                    ]
                ),
                'text/html'
            );

        // Collect all users related to region
        foreach ($region->getCountries() as $country) {
            foreach ($country->getUsers() as $user) {
                $message->addTo($user->getEmail());
            }
        }

        if ($this->mailer->send($message)) {
            // Set status REMINDED to all requests
            foreach ($region->getCountries() as $country) {
                $country->getContributionCountryRequest()->setStatus(ContributionCountryRequest::STATUS_REMINDED);
            }

            $this->em->flush();
        }
    }

    /**
     * @param User $requester
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return void
     */
    public function sendDeadlineReminderMailToCounty(User $requester, ContributionCountryRequest $contributionCountryRequest): void
    {
        $country = $contributionCountryRequest->getCountry();

        $message = (new \Swift_Message())
            ->setSubject('Deadline reminder')
            ->setFrom($this->sender)
            ->setBody(
                $this->templateEngine->render(
                    '@App/Emails/deadline_reminder_to_country.html.twig',
                    [
                        'countryName' => $country->getName(),
                        'countryId' => $country->getId(),
                        'deadline' => $country->getRegion()->getContributionRequest()->getDeadline(),
                        'requester' => $requester->getUsername(),
                    ]
                ),
                'text/html'
            );

        foreach ($country->getUsers() as $user) {
            $message->addTo($user->getEmail());
        }

        if ($this->mailer->send($message)) {
            $contributionCountryRequest->setStatus(ContributionCountryRequest::STATUS_REMINDED);
            $this->em->flush();
        }
    }

    /**
     * @param User $requester
     * @param ContributionRequest $contributionRequest
     * @param string|null $carbonCopy
     *
     * @return void
     */
    public function sendFeedbackRequest(User $requester, ContributionRequest $contributionRequest, ?string $carbonCopy): void
    {
        $message = (new \Swift_Message())
            ->setSubject('Feedback request')
            ->setFrom($this->sender)
            ->setBody(
                $this->templateEngine->render(
                    '@App/Emails/feedback_request.html.twig',
                    [
                        'regionName' => $contributionRequest->getRegion()->getName(),
                        'deadline' => $contributionRequest->getDeadline(),
                        'requester' => $requester->getUsername(),
                    ]
                ),
                'text/html'
            );


        // Send email to all contributors from region
        foreach ($contributionRequest->getRegion()->getCountries() as $country) {
            foreach ($country->getUsers() as $user) {
                $message->addTo($user->getEmail());
            }
        }

        // Add in cc all who was marked in cc field
        foreach (explode(';', $carbonCopy) as $carbonCopyEmail) {
            if ($carbonCopyEmail) {
                $message->addCc($carbonCopyEmail);
            }
        }

        // Add in cc all administrators
        $administrators = $this->em->getRepository(User::class)->findByRole(User::ROLE_ADMIN);
        foreach ($administrators as $administrator) {
            $message->addCc($administrator->getEmail());
        }

        $this->mailer->send($message);
    }

    /**
     * @param User $user
     * @param string $password
     *
     * @return void
     */
    public function sendUserCreationConfirmation(User $user, string $password): void
    {
        $message = (new \Swift_Message())
            ->setSubject('Account creation')
            ->setFrom($this->sender)
            ->setTo($user->getEmail())
            ->setBody(
                $this->templateEngine->render(
                    '@App/Emails/user_creation_confirmation.html.twig',
                    [
                        'username' => $user->getUsername(),
                        'password' => $password,
                    ]
                ),
                'text/html'
            );

        $this->mailer->send($message);
    }

    /**
     * Send New Feedback mail to all
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     */
    public function sendNewFeedbackMail(ContributionCountryRequest $contributionCountryRequest): void
    {
        $this->sendNewFeedbackMailToAdmin($contributionCountryRequest);
        $this->sendNewFeedbackMailToContributor($contributionCountryRequest);
    }

    /**
     * Send New Feedback mail to contributor
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return void
     */
    public function sendNewFeedbackMailToContributor(ContributionCountryRequest $contributionCountryRequest): void
    {
        $country = $contributionCountryRequest->getCountry();

        $message = (new \Swift_Message())
            ->setSubject('New feedback')
            ->setFrom($this->sender)
            ->setBody(
                $this->templateEngine->render(
                    '@App/Emails/new_feedback_contributor.html.twig',
                    [
                        'countryName' => $country->getName(),
                        'countryId' => $country->getId(),
                    ]
                ),
                'text/html'
            );

        foreach ($contributionCountryRequest->getCountry()->getUsers() as $user) {
            $message->addTo($user->getEmail());
        }

        $this->mailer->send($message);
    }

    /**
     * Send New Feedback mail to admin
     *
     * @param ContributionCountryRequest $contributionCountryRequest
     *
     * @return void
     */
    public function sendNewFeedbackMailToAdmin(ContributionCountryRequest $contributionCountryRequest): void
    {
        $country = $contributionCountryRequest->getCountry();

        $message = (new \Swift_Message())
            ->setSubject('New feedback')
            ->setFrom($this->sender)
            ->setBody(
                $this->templateEngine->render(
                    '@App/Emails/new_feedback_admin.html.twig',
                    [
                        'countryName' => $country->getName(),
                        'countryId' => $country->getId(),
                    ]
                ),
                'text/html'
            );

        $admins = $this->em->getRepository(User::class)->findByRole(User::ROLE_ADMIN);
        foreach ($admins as $admin) {
            $message->addTo($admin->getEmail());
        }

        $this->mailer->send($message);
    }
}
