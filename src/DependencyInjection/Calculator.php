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

    function calculate(\string $products)
    {
        $productArray = $this->convertArray($products);
        $entities = $this->repository->findBy(['name' => $productArray]);
        $occurence = $this->findOccurence($products);
        $totalPrice = 0;
        /** @var Product $product */
        foreach($entities as $product) {
            if(!$product->getPromotion()) {
                continue;
            }

            $price = $occurence[$product->getName()] * $product->getPrice();
            if($occurence[$product->getName()] >= $product->getPromotion()->getQuantity()) {
                $price -= $product->getPromotion()->getQuantity() * $product->getPrice();
                $price += $product->getPromotion()->getQuantity() * $product->getPromotion()->getPrice();
            }

            $totalPrice += $price;
        }

        return $totalPrice;
    }

    private function findOccurence(\string $products) {
        $occurence = [];
        $len = strlen($products);
        for($i = 0; $i < $len; $i++) {
            $occurence[$products[$i]] += 1;
        }

        return $occurence;
    }

    private function convertArray(\string $products) {
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
