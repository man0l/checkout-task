<?php


namespace App\DependencyInjection;
use App\Entity\Product;
use App\Repository\ProductRepository;

class Calculator
{

    private $repository;
    function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    function calculate(string $products)
    {
        $productArray = $this->convertArray($products);
        $entities = $this->repository->findBy(['name' => $productArray]);
        $occurence = $this->findOccurence($products);
        $totalPrice = 0;
        /** @var Product $product */
        foreach($entities as $product) {
            if(!$product->getPromotion()) {
                $price = $occurence[$product->getName()] * $product->getPrice();
                $totalPrice += $price;
                continue;
            }
            $price = 0;
            // calc how many promotions consist the product array
            $promotionsNum = floor($occurence[$product->getName()] / $product->getPromotion()->getQuantity());

            if($promotionsNum > 0) {
                for ($i = 0; $i < $promotionsNum; $i++) {
                    $price += $product->getPromotion()->getTotalPrice();
                    $occurence[$product->getName()] -= $product->getPromotion()->getQuantity();
                }
            }

            if($occurence[$product->getName()] > 0) {
                $price += $occurence[$product->getName()] * $product->getPrice();
            }

            $totalPrice += $price;
        }

        return $totalPrice;
    }

    private function findOccurence(string $products) {
        $occurence = [];
        $len = strlen($products);
        for($i = 0; $i < $len; $i++) {
            if(isset($occurence[$products[$i]])) {
                $occurence[$products[$i]] += 1;
            } else {
                $occurence[$products[$i]] = 1;
            }

        }

        return $occurence;
    }

    private function convertArray(string $products) {
        $productArray = [];
        $len = strlen($products);
        for($i = 0; $i < $len; $i++) {

            if(!$this->validate($products[$i])) {
                continue;
            }

            if(!in_array($products[$i], $productArray)) {
                $productArray[] = $products[$i];
            }
        }

        return $productArray;
    }

    private function validate($char) {
        return preg_match("/A|B|C|D/", $char);
    }
}
