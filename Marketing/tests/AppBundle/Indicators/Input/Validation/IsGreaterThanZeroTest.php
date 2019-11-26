<?php

namespace AppBundle\Indicators\Input\Validation;

use AppBundle\Entity\Cell;
use PHPUnit\Framework\TestCase;

class IsGreaterThanZeroTest extends TestCase
{
    public function data()
    {
        return [
            [false, 0],
            [true, 1],
            [true, 2],
            [false, -1],
            [true, '1'],
            [false, 'a'],
            [true, 1.0],
            [false, -1.0],
            [false, 0.0],
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

        /** @var IsGreaterThanZero $mock */
        $mock = $this->getMockForTrait(IsGreaterThanZero::class);
        $this->assertEquals($expected, $mock->isValid($cell));
    }
}
