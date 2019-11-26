<?php

namespace AppBundle\Indicators\Input\Validation;

use AppBundle\Entity\Cell;
use PHPUnit\Framework\TestCase;

class PercentageTest extends TestCase
{
    public function data()
    {
        return [
            [true, 0],
            [true, 1],
            [false, 2],
            [false, -1],
            [true, '1'],
            [false, 'a'],
            [true, 1.0],
            [false, -1.0],
            [true, 0.0],
            [true, 0.5],
            [true, 0.55],
            [true, 0.555],
            [false, 1.5],
            [false, 1.55],
            [false, 1.556],
            [false, 1.554],
            [false, 1.555],
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

        /** @var Percentage $mock */
        $mock = $this->getMockForTrait(Percentage::class);
        $this->assertEquals($expected, $mock->isValid($cell));
    }
}
