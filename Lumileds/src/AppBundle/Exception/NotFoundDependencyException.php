<?php

namespace AppBundle\Exception;

class NotFoundDependencyException extends \RuntimeException
{
    /**
     * @var int
     */
    private $indicatorId;

    /**
     * @var int
     */
    private $technologyId;

    /**
     * @var int
     */
    private $segmentId;

    /**
     * @var string
     */
    private $countryName;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $message, $indicatorId, $technologyId, $segmentId, $countryName)
    {
        parent::__construct($message);

        $this->indicatorId = $indicatorId;
        $this->technologyId = $technologyId;
        $this->segmentId = $segmentId;
        $this->countryName = $countryName;
    }

    /**
     * @return int
     */
    public function getIndicatorId()
    {
        return $this->indicatorId;
    }

    /**
     * @return int
     */
    public function getTechnologyId()
    {
        return $this->technologyId;
    }

    /**
     * @return int
     */
    public function getSegmentId(): int
    {
        return $this->segmentId;
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        return $this->countryName;
    }
}
