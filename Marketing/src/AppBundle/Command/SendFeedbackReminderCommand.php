<?php

namespace AppBundle\Command;

use AppBundle\Entity\Region;
use AppBundle\Services\MailerService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class SendFeedbackReminderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:send-feedback-reminder')
            ->setDescription('Send feedback reminder 3 days before the deadline')
            ->setHelp('This command allows you to send feedback reminder 3 days before the deadline');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Region $regions */
        $regions = $this->getContainer()->get('doctrine')->getRepository(Region::class)->getBeforeDeadline();

        foreach ($regions as $region) {
            $this->getContainer()->get(MailerService::class)->sendDeadlineReminderMailToRegion($region);
        }
    }
}
