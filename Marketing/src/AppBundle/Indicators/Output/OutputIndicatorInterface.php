<?php

namespace AppBundle\Indicators\Output;

use AppBundle\Entity\Cell;
use Doctrine\Common\Persistence\ObjectManager;

interface OutputIndicatorInterface
{
    /**
     * Calculates the value of the indicator.
     *
     * @param Cell $cell
     * @param ObjectManager $em
     *
     * @param bool|null $isUpload
     * @return mixed
     *   Calculated value.
     */
    public function getCalculatedValue(Cell $cell, ObjectManager $em, $isUpload);
}
