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

            // GESTION RENDEZ-VOUS DE L'UTILISATEUR FORMULAIRE
            $user = $this->getUser()->getId();
            $resultat = $repoClient->findBy(['user'=>$user]);
            $idclient = $resultat[0]->getId();
            $rdv->setClient($resultat[0]);
            $rdv->setCoach($coach);
            $rdv->setHeure($rdv->getHeure());
        
            $test = ($request->request->get('rdv'));
            $rdv->setTotal($prix * $test['duree']);
            
            // FIN GESTION RENDEZ-VOUS UTILISATEUR FORMULAIRE
            

            // VARIABLES DONNEES RDV 
            $jourUtilisateur = $rdv->getJour();
            dump($jourUtilisateur);
            $heureUtilisateur = $rdv->getHeure();
            
            $dureeUtilisateur = $rdv->getDuree();
            $lieuUtilisateur = $rdv->getLieu();
            // FIN VARIABLES DONNEES RDV 
           
            
            // CONTROLE SI RDV EXISTE DEJA DANS BDD OU NON, REMPLISSAGE DES PLAGES HORRAIRES
            $jourBdd=($repoRdv->findBy(['jour'=>$jourUtilisateur, 'heure'=>$heureUtilisateur]));
            if(!empty($jourBdd)){
                dump('Rdv déjà pris');
            } else {
                date_modify($heureUtilisateur,"-1 hours");
                for($i=0; $i<$dureeUtilisateur;$i++){
                    $rdvPlage = new Rdv();
                    $rdvPlage->setClient($resultat[0]);
                    $rdvPlage->setCoach($coach);
                    $rdvPlage->setDuree($dureeUtilisateur);
                    $rdvPlage->setJour($jourUtilisateur);
                    $rdvPlage->setHeure(date_modify($heureUtilisateur, "+1 hours"));
                    $rdvPlage->setLieu($lieuUtilisateur);
                    if($i==0){
                        $rdvPlage->setTotal($prix * $test['duree']);
                    }
                    $manager->persist($rdvPlage);
                    $manager->flush();
                }
            }
           

            // GESTION PARTIE EMAIL LORS DE L'INSCRIPTION
            $clientemail = $this->getUser()->getEmail();
            $coachRequette = $repoUser->findBy(['id'=> $coach->getUser()]);
            $coachEmail = $coachRequette[0]->getEmail();
          

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
      
        }



    return $this->render('coach/fichefullcoach.html.twig', [
        'fichefull' => $coach,
        'formRdv' => $form->createView()
    ]);
    }

}
