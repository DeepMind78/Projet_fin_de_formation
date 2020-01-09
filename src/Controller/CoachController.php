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
use App\Repository\UserRepository;
use App\Service\MailerService;
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

    public function afficherFiche (CoachRepository $repoCoach, $id, Request $request, EntityManagerInterface $manager, ClientRepository $repoClient, UserRepository $repoUser, MailerService $mailer, RdvRepository $repoRdv){
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

            // Données envoyées par l'utilisateur pour le souhait de rdv. 
            $jourUtilisateur = $rdv->getJour();
            dump($jourUtilisateur);
            $heureUtilisateur = $rdv->getHeure();
            dump($heureUtilisateur);

            // pour trouver si jour existe dans bdd
            $jourBdd=($repoRdv->findBy(['jour'=>$jourUtilisateur]));
            
            if(!empty($jourBdd)){
                foreach($jourBdd as $jour){
                    if( $jour->getHeure() == $heureUtilisateur ){
                        dump('Rdv déjà pris');
                    } else {
                        dump('Rdv libre');
                    }
                }
            } else {
                dump('rdv libre');
            }

            
            $rdv->setTotal($prix * $test['duree']);
            $clientemail = $this->getUser()->getEmail();
            $coachRequette = $repoUser->findBy(['id'=> $coach->getUser()]);
            $coachEmail = $coachRequette[0]->getEmail();
           // dump($test['duree']);

                $jourTab = $test['jour'];
                $jourRdv = $jourTab['day'] .'/'. $jourTab['month'] . '/'. $jourTab['year'];

                $heureTab = $test['heure'];
                $heure = $heureTab['hour'] .'h'. $heureTab['minute'];

                $lieu = $test['lieu'];

                $total = $prix * $test['duree'];

                $duree = $test['duree'];
                $infoClient = $resultat[0]->getNom() . ' '. $resultat[0]->getPrenom();
                $infoCoach = $coach->getPrenom() . ' ' . $coach->getNom();

                $mailer->sendRdvCoach($coachEmail,$infoClient,$duree,$heure, $jourRdv, $lieu,$total,'confirmationRdvCoach.html.twig');
                $mailer->sendRdvClient($clientemail,$infoCoach,$duree,$heure, $jourRdv, $lieu,$total,'confirmationRdvClient.html.twig');



            $manager->persist($rdv);
            $manager->flush();
        }



    return $this->render('coach/fichefullcoach.html.twig', [
        'fichefull' => $coach,
        'formRdv' => $form->createView()
    ]);
    }

}
