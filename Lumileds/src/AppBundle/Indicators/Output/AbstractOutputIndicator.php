<?php

namespace AppBundle\Indicators\Output;

use AppBundle\Entity\Cell;
use AppBundle\Indicators\AbstractIndicator;

abstract class AbstractOutputIndicator extends AbstractIndicator implements OutputIndicatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEditable(Cell $cell): bool
    {
        return false;
    }
}
