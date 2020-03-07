<?php


namespace App\Service;
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
        $occurrence = $this->findOccurrence($products);
        $totalPrice = 0;
        /** @var Product $product */
        foreach($entities as $product) {
            if(!$product->getPromotion()) {
                $price = $occurrence[$product->getName()] * $product->getPrice();
                $totalPrice += $price;
                continue;
            }
            $price = 0;
            // calc how many promotions consist the product array
            $promotionsNum = floor($occurrence[$product->getName()] / $product->getPromotion()->getQuantity());

            if($promotionsNum > 0) {
                for ($i = 0; $i < $promotionsNum; $i++) {
                    $price += $product->getPromotion()->getTotalPrice();
                    $occurrence[$product->getName()] -= $product->getPromotion()->getQuantity();
                }
            }

            if($occurrence[$product->getName()] > 0) {
                $price += $occurrence[$product->getName()] * $product->getPrice();
            }

            $totalPrice += $price;
        }

        return $totalPrice;
    }

    public function findOccurrence(string $products) {
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

    public function convertArray(string $products) {
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
