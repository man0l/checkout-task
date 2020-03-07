<?php

namespace App\Tests;

use App\DependencyInjection\Calculator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CalculationTest extends WebTestCase
{
    private $calculator;

    function setUp() {
        self::bootKernel();

        /** @var Calculator calculator */
        $this->calculator = self::$container->get('App\DependencyInjection\Calculator');
    }

    /**
     * @dataProvider getInputData
     */
    public function testCalculation($totalPrice, $products)
    {
        $result = $this->calculator->calculate($products);
        self::assertEquals($totalPrice, $result);
    }

    private function getInputData() {
        return [
            [50, "A"],
            [80, "AB"],
            [110, "CDBA"],
            [100, "AA"],
            [130, "AAA"],
            [180, "AAAA"],
            [230, "AAAAA"],
            [260, "AAAAAA"],
            [160, "AAAB"],
            [175, "AAABB"],
            [185, "AAABBD"],
            [185, "DABABA"],
            [185, "DABABA"]
        ];
    }
}
