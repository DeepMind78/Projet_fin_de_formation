<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Repository\CoachRepository;
use App\Repository\RdvRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ClientController extends AbstractController
{
    /**
     * @Route("/client/fiche/{user}", name="fiche.client")
     * @param Request $request
     * @param $security
     * @param Client|null $client
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, Security $security, EntityManagerInterface $manager, Client $client=null)
    {

        if(!$client){
            $client = new Client();
        }

        $form = $this->createForm(ClientType::class,$client);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user = $security->getUser();
            $client->setUser($user);
            $manager->persist($client);
            $manager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('client/index.html.twig', [
            'ficheClient' => $form->createView()
        ]);

    }

}
