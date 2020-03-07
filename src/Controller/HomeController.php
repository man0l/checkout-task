<?php

namespace App\Controller;

use App\Form\ConfirmType;
use App\Service\Calculator;
use App\Form\OrderType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Order;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Route;

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
    public function receipt($products, Calculator $calculator, ProductRepository $repository, Request $request, EntityManagerInterface $entityManager) {

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

            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('thank_you');
        }

        return $this->render('home/receipt.html.twig', [
            'products' => $entities,
            'occurence' => $occurrence,
            'discounted_total' => $discountedTotal,
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/thankyou", name="thank_you")
     */
    public function thankyou() {
        return $this->render('home/thankyou.html.twig');
    }
}
