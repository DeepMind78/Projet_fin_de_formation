<?php

namespace App\Controller;

use Stripe\Charge;
use Stripe\Stripe;
use App\Entity\Rdv;
use App\Entity\User;
use App\Entity\Coach;
use App\Form\RdvType;
use App\Form\CoachType;
use App\Service\MailerService;
use App\Repository\RdvRepository;
use App\Repository\UserRepository;
use App\Repository\CoachRepository;
use App\Repository\ClientRepository;
use CalendarBundle\Event\CalendarEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\EventSubscriber\CalendarSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function index(Request $request, EntityManagerInterface $manager, Security $security, CoachRepository $repo, Coach $coach=null, $user)
    {
        
        $userCheck = $this->getUser()->getId();
        $error = false;

        if(!$coach){
            $coach = new Coach();
        } elseif($userCheck != $user) {
            $error = true;
            
        }
        
        $form = $this->createForm(CoachType::class,$coach);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $userConnected = $security->getUser();
            $coach->setUser($userConnected);
            $manager->persist($coach);
            $manager->flush();
            $id = $coach->getId();
            return $this->redirectToRoute('fichefullcoach',[
                'id' => $id
            ] );
        }

        return $this->render('coach/index.html.twig', [
            'ficheCoach' => $form->createView(),
            'error'=>$error
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
        $error= false;
        $succesPaiement=false;


        \Stripe\Stripe::setApiKey('sk_test_5U8RJ7GIFIWcstBQDaX6u0Ot00gWCe0UJJ');

        // Token is created using Stripe Checkout or Elements!
        // Get the payment token ID submitted by the form:

            // dump($request);
        
            if($_POST){
                $token = $_POST['stripeToken'];
            }
            
            
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
            $amount= $prix*$test['duree'];
            
            // FIN GESTION RENDEZ-VOUS UTILISATEUR FORMULAIRE

            // GESTION PAIEMENT RESERVATION 
            $charge = \Stripe\Charge::create([
                'amount' => $amount*100,
                'currency' => 'eur',
                'description' => 'Client: '.$resultat[0]->getNom() . ' ' . $resultat[0]->getPrenom() . '- Coach: ' . $coach->getNom() . ' ' . $coach->getPrenom(),
                'source' => $token,
                ]);
        
            // FIN GESTION PAIEMENT RESERVATION
            

            // VARIABLES DONNEES RDV 
            $jourUtilisateur = $rdv->getJour();
            // dump($jourUtilisateur);
            $heureUtilisateur = $rdv->getHeure();
            
            $dureeUtilisateur = $rdv->getDuree();
            $lieuUtilisateur = $rdv->getLieu();
            // FIN VARIABLES DONNEES RDV 

            // GESTION PARTIE VARIABLES EMAIL LORS DE L'INSCRIPTION
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
            // FIN PARTIE GESTION VARIABLES EMAIL

            $heureTest = $heureUtilisateur;
            $variable = $this->rdvExist($heureTest,$dureeUtilisateur,$repoRdv,$jourUtilisateur);
            dump($variable);
            

            
            // CONTROLE SI RDV EXISTE DEJA DANS BDD OU NON, REMPLISSAGE DES PLAGES HORRAIRES
            // $jourBdd=($repoRdv->findBy(['jour'=>$jourUtilisateur, 'heure'=>$heureUtilisateur]));
            if($variable==true){
                $error = true;
            } else {
                dump($variable);
                dump($error);
                date_modify($heureUtilisateur,'-'.($dureeUtilisateur).' hours');
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
                $mailer->sendRdvCoach($coachEmail,$infoClient,$duree,$heure, $jourRdv, $lieu,$total,'confirmationRdvCoach.html.twig');
                $mailer->sendRdvClient($clientemail,$infoCoach,$duree,$heure, $jourRdv, $lieu,$total,'confirmationRdvClient.html.twig');
                $succesPaiement = true;
                
            }
           
        } 
        

    return $this->render('coach/fichefullcoach.html.twig', [
        'fichefull' => $coach,
        'formRdv' => $form->createView(),
        'error' => $error,
        'success' => $succesPaiement
    ]);
    }

    public function rdvExist($heure, $duree, $repo, $jour){
            
            $jourUnBdd=($repo->findBy(['jour'=>$jour, 'heure'=>$heure]));
            if(!empty($jourUnBdd)){
                return true;
            }
            for($i=1;$i<$duree;$i++){
                $jourBdd=($repo->findBy(['jour'=>$jour, 'heure'=>date_modify($heure, "+1 hours")]));
                // print_r('test');
                if(!empty($jourBdd)){
                    // dump($heure);
                    return true;
                    break;
                } else {
                    
                    // return false;
                    // dump($heure);
                
                }
            }
    }


}
