<?php

namespace App\Controller;

use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Order;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        $entity = new Order;

        $form = $this->createForm(OrderType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
        }


        return $this->render('home/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
