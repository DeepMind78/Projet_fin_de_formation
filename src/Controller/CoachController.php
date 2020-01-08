<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\User;
use App\Entity\Coach;
use App\Form\CoachType;
use App\Form\RdvType;
use App\Repository\ClientRepository;
use App\Repository\CoachRepository;
use App\Repository\RdvRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CoachController extends AbstractController
{
    /**
     * @Route("/coach/fiche/{user}", name="fiche.coach")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Security $security
     * @param CoachRepository $repo
     * @param Coach|null $coach
     * @return Response
     */
    public function index(Request $request, EntityManagerInterface $manager, Security $security, CoachRepository $repo, Coach $coach=null)
    {
        // if ($repo->findBy(['user'=>$id])){
        //     $coach = $repo->findBy(['user'=>$id]);
        // } else {
        //     $coach = new Coach;
        // }

        if(!$coach){
            $coach = new Coach();
        }
        // $coach = new Coach;
        $form = $this->createForm(CoachType::class,$coach);
        $form->handleRequest($request);
        
        
        if($form->isSubmitted() && $form->isValid()){
            $user = $security->getUser();
            $coach->setUser($user);
            $manager->persist($coach);
            $manager->flush();
            $id = $coach->getId();
            return $this->redirectToRoute('fichefullcoach',[
                'id' => $id
            ] );
        }

        return $this->render('coach/index.html.twig', [
            'ficheCoach' => $form->createView()
        ]);
    }

    /**
     * @Route("/coach/fichecomplet/{id}", name="fichefullcoach")
     * @param CoachRepository $repoCoach
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param ClientRepository $repoClient
     * @return Response
     */

    public function afficherFiche (CoachRepository $repoCoach, $id, Request $request, EntityManagerInterface $manager, ClientRepository $repoClient){
        $coach = $repoCoach->find($id);
        $prix = $coach->getPrix();
        $rdv = new Rdv();
        $form = $this->createForm(RdvType::class,$rdv);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user = $this->getUser()->getId();
            $resultat = $repoClient->findBy(['user'=>$user]);
            $idclient = $resultat[0]->getId();
            $rdv->setClient($resultat[0]);
            $rdv->setCoach($coach);
            $test = ($request->request->get('rdv'));
            $rdv->setTotal($prix * $test['duree']);

           // dump($test['duree']);

            $manager->persist($rdv);
            $manager->flush();
        }



    return $this->render('coach/fichefullcoach.html.twig', [
        'fichefull' => $coach,
        'formRdv' => $form->createView()
    ]);
    }


    /**
     * @Route ("/rdvCoach", name="coach.rdv")
     */

    public function rdvcoach(CoachRepository $repoCoach, RdvRepository $repoRdv)
    {
        $user = $this->getUser()->getId();
        $resultat = $repoCoach->findBy(['user' => $user]);
        $idcoach = $resultat[0]->getId();
        $rdv = $repoRdv->findBy(['coach' => $idcoach]);
        //dump($idcoach);
        dump($rdv);

        return $this->render('/rdv/rdvcoach.html.twig', [
            'rdvcoachs' => $rdv
        ]);
    }
}
