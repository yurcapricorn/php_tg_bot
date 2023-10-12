<?php

namespace Tests\Unit;

use App\Tradebot\LRI\Math;
use Tests\TestCase;

class LRITest extends TestCase
{
    public function testMath_1() {
        $arr = [
            'x' => [0,1,2,4,5],
            'y' => [2.1,2.4,2.6,2.8,3.0]
        ];
        $expected = ['a' => 0.165, 'b' => 2.184, 'stdev' => round(sqrt(0.019/sizeof($arr['x'])),3)];
        $actual = Math::getData($arr);
        $this->assertEquals($expected, $actual);
    }

    public function testMath_2() {
        $arr = [
            'x' => [1.7, 1.6, 2.8, 5.6, 1.3, 2.2, 1.3, 1.1, 3.2, 1.5, 5.2, 4.6, 5.8, 3.0],
            'y' => [3.7, 3.9, 6.7, 9.5, 3.4, 5.6, 3.7, 2.7, 5.5, 2.9, 10.7, 7.6, 11.8, 4.1]
        ];
        $expected = ['b' => 0.964, 'a' => 1.67];
        $actual = Math::getData($arr);
        $this->assertEquals($expected, ['a' => $actual['a'],'b' => $actual['b']]);
    }
}