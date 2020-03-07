<?php

namespace App\Tests;

use App\Service\Calculator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CalculationTest extends WebTestCase
{
    private $calculator;
    private $repository;

    function setUp() {
        self::bootKernel();

        $this->repository = self::$container->get('App\Repository\ProductRepository');

        /** @var Calculator calculator */
        $this->calculator = new Calculator($this->repository);
    }

    /**
     * @dataProvider getInputData
     */
    public function testCalculation($totalPrice, $products)
    {
        $result = $this->calculator->calculate($products);
        self::assertEquals($totalPrice, $result);
    }

    public function getInputData() {
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
