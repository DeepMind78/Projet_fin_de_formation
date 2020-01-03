<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Form\CoachType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CoachController extends AbstractController
{
    /**
     * @Route("/coach", name="coach")
     */
    public function index(Request $request, EntityManagerInterface $manager)
    {
        $coach = new Coach;
        $form = $this->createForm(CoachType::class,$coach);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->setUser();
            $manager->persist($coach);
            $manager->flush();
        }

        return $this->render('coach/index.html.twig', [
            'ficheCoach' => $form->createView()
        ]);
    }
}
