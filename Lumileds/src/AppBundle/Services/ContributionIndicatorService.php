<?php

namespace AppBundle\Services;

use AppBundle\Entity\ContributionIndicatorRequest;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Technology;
use AppBundle\Model\Table;
use OutOfBoundsException;
use Symfony\Component\Translation\TranslatorInterface;

class ContributionIndicatorService
{
    /**
     * @var int
     */
    private $indicatorGroup;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ContributionIndicatorRequestService constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return int
     */
    public function getIndicatorGroup(): int
    {
        return $this->indicatorGroup;
    }

    /**
     * @param int $indicatorGroup
     *
     * @return ContributionIndicatorService
     */
    public function setIndicatorGroup(int $indicatorGroup): self
    {
        $this->indicatorGroup = $indicatorGroup;

        return $this;
    }

    /**
     * Return indicators that correspond to indicators group for table 1
     *
     * @return array
     */
    public function getIndicatorsTable1(): array
    {
        switch ($this->getIndicatorGroup()) {
            case ContributionIndicatorRequest::INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY:
                $indicators = [
                    Indicator::INDICATOR_TECH_SPLIT,
                    Indicator::INDICATOR_MARKET_VOLUME,
                    Indicator::INDICATOR_MARKET_VALUE_USD,
                ];
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_UPGRADE_TAKE_RATE:
                $indicators = [
                    Indicator::INDICATOR_UPGRADE_TAKE_RATE,
                    Indicator::INDICATOR_MARKET_VOLUME,
                    Indicator::INDICATOR_MARKET_VALUE_USD,
                ];
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_PRICE_DEVELOPMENT:
                $indicators = [
                    Indicator::INDICATOR_PRICE_DEVELOPMENT,
                    Indicator::INDICATOR_ASP_LC,
                    Indicator::INDICATOR_MARKET_VOLUME,
                    Indicator::INDICATOR_MARKET_VALUE_USD,
                ];
                break;
            default:
                throw new OutOfBoundsException(
                    sprintf('Indicator group %d has no related indicators for table 1', $this->getIndicatorGroup())
                );
        }

        return $indicators;
    }

    /**
     * Return indicators that correspond to indicators group for table 2
     *
     * @return array
     */
    public function getIndicatorsTable2(): array
    {
        switch ($this->getIndicatorGroup()) {
            case ContributionIndicatorRequest::INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY:
                $indicators = [
                    Indicator::INDICATOR_TECH_SPLIT,
                    Indicator::INDICATOR_MARKET_VOLUME,
                    Indicator::INDICATOR_MARKET_VALUE_USD,
                ];
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_UPGRADE_TAKE_RATE:
                $indicators = [
                    Indicator::INDICATOR_UPGRADE_TAKE_RATE,
                    Indicator::INDICATOR_MARKET_VOLUME,
                    Indicator::INDICATOR_MARKET_VALUE_USD,
                ];
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_PRICE_DEVELOPMENT:
                $indicators = [
                    Indicator::INDICATOR_PRICE_DEVELOPMENT,
                    Indicator::INDICATOR_ASP_LC,
                    Indicator::INDICATOR_MARKET_VOLUME,
                    Indicator::INDICATOR_MARKET_VALUE_USD,
                ];
                break;
            default:
                throw new OutOfBoundsException(
                    sprintf('Indicator group %d has no related indicator for table 2', $this->getIndicatorGroup())
                );
        }

        return $indicators;
    }

    /**
     * @return array
     */
    public function getTechnologiesTable1(): array
    {
        switch ($this->getIndicatorGroup()) {
            case ContributionIndicatorRequest::INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY:
                $technologies = [
                    Technology::TECHNOLOGY_HL_HALOGEN,
                    Technology::TECHNOLOGY_HL_NON_HALOGEN,
                    Technology::TECHNOLOGY_HL_XENON,
                    Technology::TECHNOLOGY_HL_LED,
                    Technology::TECHNOLOGY_HL_LED_RF,
                ];
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_UPGRADE_TAKE_RATE:
                $technologies = [
                    Technology::TECHNOLOGY_HL_HALOGEN,
                    Technology::TECHNOLOGY_HL_NON_HALOGEN,
                    Technology::TECHNOLOGY_HL_XENON,
                    Technology::TECHNOLOGY_HL_LED_RF,
                ];
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_PRICE_DEVELOPMENT:
                $technologies = [
                    Technology::TECHNOLOGY_HL_HALOGEN,
                    Technology::TECHNOLOGY_HL_NON_HALOGEN,
                    Technology::TECHNOLOGY_HL_XENON,
                    Technology::TECHNOLOGY_HL_LED_RF,
                ];
                break;
            default:
                throw new OutOfBoundsException(
                    sprintf('Indicator group %d has no related technologies for table 1', $this->getIndicatorGroup())
                );
        }

        return $technologies;
    }

    /**
     * @return array
     */
    public function getTechnologiesTable2(): array
    {
        switch ($this->getIndicatorGroup()) {
            case ContributionIndicatorRequest::INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY:
                $technologies = [
                    Technology::TECHNOLOGY_SL_POSL_CONV,
                    Technology::TECHNOLOGY_SL_POSL_LED,
                    Technology::TECHNOLOGY_SL_POSL_LED_RF,
                    Technology::TECHNOLOGY_SL_CONV,
                    Technology::TECHNOLOGY_SL_LED_RF,
                ];
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_UPGRADE_TAKE_RATE:
                $technologies = [
                    Technology::TECHNOLOGY_SL_CONV,
                    Technology::TECHNOLOGY_SL_LED_RF,
                ];
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_PRICE_DEVELOPMENT:
                $technologies = [
                    Technology::TECHNOLOGY_SL_CONV,
                    Technology::TECHNOLOGY_SL_LED_RF,
                ];
                break;
            default:
                throw new OutOfBoundsException(
                    sprintf('Indicator group %d has no related technologies for table 2', $this->getIndicatorGroup())
                );
        }

        return $technologies;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        switch ($this->getIndicatorGroup()) {
            case ContributionIndicatorRequest::INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY:
                $title = 'contribution.indicator.title.tech_split';
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_UPGRADE_TAKE_RATE:
                $title = 'contribution.indicator.title.upgrade_take_rate';
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_PRICE_DEVELOPMENT:
                $title = 'contribution.indicator.title.price_development';
                break;
            default:
                throw new OutOfBoundsException(
                    sprintf('Indicator group %d has no title', $this->getIndicatorGroup())
                );
        }

        return $this->translator->trans($title);
    }

    /**
     * @return string
     */
    public function getTitleTable1(): string
    {
        switch ($this->getIndicatorGroup()) {
            case ContributionIndicatorRequest::INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY:
            case ContributionIndicatorRequest::INDICATOR_GROUP_UPGRADE_TAKE_RATE:
            case ContributionIndicatorRequest::INDICATOR_GROUP_PRICE_DEVELOPMENT:
                $title = 'contribution.indicator.table.hl';
                break;
            default:
                throw new OutOfBoundsException(
                    sprintf('Indicator group %d has no table title 1', $this->getIndicatorGroup())
                );
        }

        return $this->translator->trans($title);
    }

    /**
     * @return string
     */
    public function getTitleTable2(): string
    {
        switch ($this->getIndicatorGroup()) {
            case ContributionIndicatorRequest::INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY:
            case ContributionIndicatorRequest::INDICATOR_GROUP_UPGRADE_TAKE_RATE:
            case ContributionIndicatorRequest::INDICATOR_GROUP_PRICE_DEVELOPMENT:
                $title = 'contribution.indicator.table.sl';
                break;
            default:
                throw new OutOfBoundsException(
                    sprintf('Indicator group %d has no table title 2', $this->getIndicatorGroup())
                );
        }

        return $this->translator->trans($title);
    }

    /**
     * @param Table $table
     *
     * @return Table
     */
    public function changeLabels(Table $table): Table
    {
        $technologies = [
            'HL Halogen' => 'Halogen',
            'HL Non-Halogen' => 'Non-Halogen',
            'HL Xenon' => 'Xenon',
            'HL LED' => 'LED OEM',
            'HL LED RF' => 'LED RF',
            'SL PosL Conventional' => 'Conventional',
            'SL PosL LED' => 'LED OEM',
            'SL PosL LED RF' => 'LED RF',
            'SL Conventional' => 'Conventional',
            'SL LED RF' => 'LED RF',
        ];

        $indicators = [
            'C2 PI' => 'C2 Price Index',
            'C3 PI' => 'C3 Price Index',
            'Chinese PI' => 'Chinese Price Index',
            'Other PI' => 'Other Price Index',
            'Osram Volume Share' => 'Osram',
            'C2 VS' => 'C2',
            'C3 VS' => 'C3',
            'Chinese VS' => 'Chinese',
            'Other VS' => 'Other',
            'Lumileds Volume Share' => 'Lumileds',
            'LL Price LC' => 'Lumileds ASP LC',
            'ASP LC' => 'Market ASP LC',
            'ASP USD' => 'Market ASP USD',
        ];

        foreach ($table->getRows() as $row) {
            $technology = $row->getTechnology();
            $indicator = $row->getIndicator();

            if (array_key_exists($technology, $technologies)) {
                $row->setTechnology($technologies[$technology]);
            }

            if (array_key_exists($indicator, $indicators)) {
                $row->setIndicator($indicators[$indicator]);
            }
        }

        $years = $table->getYears();

        foreach ($years as &$year) {
            if (array_key_exists($year, $technologies)) {
                $year = $technologies[$year];
            }
        }

        $table->setYears($years);

        return $table;
    }

    /**
     * @return string
     */
    public function getInstructions(): string
    {
        switch ($this->getIndicatorGroup()) {
            case ContributionIndicatorRequest::INDICATOR_GROUP_PARK_SPLIT_BY_TECHNOLOGY:
                $title = 'contribution.indicator.instructions.tech_split';
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_UPGRADE_TAKE_RATE:
                $title = 'contribution.indicator.instructions.upgrade_take_rate';
                break;
            case ContributionIndicatorRequest::INDICATOR_GROUP_PRICE_DEVELOPMENT:
                $title = 'contribution.indicator.instructions.price_development';
                break;
            default:
                throw new OutOfBoundsException(
                    sprintf('Indicator group %d has no instructions', $this->getIndicatorGroup())
                );
        }

        return $this->translator->trans($title);
    }
}
