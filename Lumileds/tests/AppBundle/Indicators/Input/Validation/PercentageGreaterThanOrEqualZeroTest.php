<?php

namespace AppBundle\Indicators\Input\Validation;

use AppBundle\Entity\Cell;
use PHPUnit\Framework\TestCase;

class PercentageGreaterThanOrEqualZeroTest extends TestCase
{
    public function data()
    {
        return [
            [true, 0],
            [true, 1],
            [true, 2],
            [false, -1],
            [true, '1'],
            [false, 'a'],
            [true, 1.0],
            [false, -1.0],
            [true, 0.0],
            [true, 0.5],
            [true, 0.55],
            [true, 0.555],
            [true, 1.5],
            [true, 1.55],
            [true, 1.556],
            [true, 1.554],
            [true, 1.555],
            [true, 0.005080645161290322],
        ];
    }

    /**
     * @dataProvider data
     *
     * @param bool $expected
     * @param mixed $value
     */
    public function testIsValid($expected, $value)
    {
        $cell = (new Cell())
            ->setValue($value);

        /** @var PercentageGreaterThanOrEqualZero $mock */
        $mock = $this->getMockForTrait(PercentageGreaterThanOrEqualZero::class);
        $this->assertEquals($expected, $mock->isValid($cell));
    }
}
