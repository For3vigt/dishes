<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Form\DishType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DishController extends AbstractController
{
    #[Route('/dish/new', name: 'dish_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dish = new Dish();
        $form = $this->createForm(DishType::class, $dish);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dish);
            $entityManager->flush();

            return $this->redirectToRoute('dish_list');
        }

        return $this->render('dish/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/dishes', name: 'dish_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $dishes = $entityManager->getRepository(Dish::class)->findAll();

        return $this->render('dish/list.html.twig', [
            'dishes' => $dishes,
        ]);
    }
}
