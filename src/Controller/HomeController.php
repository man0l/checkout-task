<?php

namespace App\Controller;

use App\Form\ConfirmType;
use App\Service\Calculator;
use App\Entity\Product;
use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Order;
use App\Repository\ProductRepository;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        $form = $this->createForm(OrderType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute("receipt_products", ['products' => $data['products']]);
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @Route("/receipt/{products}", name="receipt_products")
     */
    public function receipt($products, Calculator $calculator, ProductRepository $repository, Request $request) {

        $productArray = $calculator->convertArray($products);
        $entities     = $repository->findBy(['name' => $productArray]);

        $occurrence      = $calculator->findOccurrence($products);
        $discountedTotal = $calculator->calculate($products);

        $form = $this->createForm(ConfirmType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            // save order
            $order = new Order();
            foreach($entities as $product) {
                $order->addProduct($product);
            }

            $order->setTotal($discountedTotal);


        }

        return $this->render('home/receipt.html.twig', [
            'products' => $entities,
            'occurence' => $occurrence,
            'discounted_total' => $discountedTotal,
            'form' => $form->createView()
        ]);

    }
}
