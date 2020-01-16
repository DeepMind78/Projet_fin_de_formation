<?php

namespace App\Controller;

use DateTime;
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

    public function afficherFiche (CoachRepository $repoCoach, $id, Request $request, EntityManagerInterface $manager, ClientRepository $repoClient, UserRepository $repoUser, RdvRepository $repoRdv){
        $coach = $repoCoach->find($id);
        $prix = $coach->getPrix();
        $rdv = new Rdv();
        $form = $this->createForm(RdvType::class,$rdv);
        $form->handleRequest($request);
        $error= false;
        $succesPaiement=false;
        $this->addFlash('prix', $prix);
        $this->addFlash('coach', $id);
        

        // Token is created using Stripe Checkout or Elements!
        // Get the payment token ID submitted by the form:

            // dump($request);
        
            
            
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

           

            // VARIABLES DONNEES RDV 
            $jourUtilisateur = $rdv->getJour();
            // dump($jourUtilisateur);
            $heureUtilisateur = $rdv->getHeure();
            
            $dureeUtilisateur = $rdv->getDuree();
            $lieuUtilisateur = $rdv->getLieu();
            // FIN VARIABLES DONNEES RDV 

            // GESTION PARTIE VARIABLES EMAIL LORS DE L'INSCRIPTION
            
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

            // FONCTION QUI TEST SI RDV EXISTE, VOIR PLUS BAS 
            $variable = $this->rdvExist($heureTest,$dureeUtilisateur,$repoRdv,$jourUtilisateur, $coach);
            if ($variable == true){
                $error = true;
            } else {
                return $this->redirectToRoute('confirmation.rdv', [
                    'request' => $request,
                    'id' => $id,
                    'lieu'=> $lieuUtilisateur,
                    'duree'=> $dureeUtilisateur,
                    'heure'=> $heureUtilisateur, 
                    'jour'=> $jourUtilisateur, 
            
                    
                ], 307);
            }    
             
        } 
        

    return $this->render('coach/fichefullcoach.html.twig', [
        'fichefull' => $coach,
        'formRdv' => $form->createView(),
        'error' => $error,
    ]);
    }

    public function rdvExist($heure, $duree, $repo, $jour, $coach){
            
            $jourUnBdd=($repo->findBy(['jour'=>$jour, 'heure'=>$heure, 'coach'=>$coach]));
            
            if(!empty($jourUnBdd)){
                return true;
            }
            for($i=1;$i<$duree;$i++){
                $jourBdd=($repo->findBy(['jour'=>$jour, 'heure'=>date_modify($heure, "+1 hours")]));
                
                if(!empty($jourBdd)){
                    
                    return true;
                    break;
                } else {
                
                }
            }

    }
    
    /**
     * @Route("/confirmation/rdv", name="confirmation.rdv")
    */
    public function confirmation(Request $request, ClientRepository $repoClient, EntityManagerInterface $manager, MailerService $mailer, CoachRepository $repoCoach, UserRepository $repoUser){
        $token=null;

        $prixFlash = $this->get('session')->getFlashBag()->get('prix');
        $coachFlash = $this->get('session')->getFlashBag()->get('coach');
        $idCoach = $_GET['id'];
        dump($idCoach);
        $rdv = $request->get('rdv');
        $coachRequete = $repoCoach->findBy(['id'=>$idCoach]);
        $coach = $coachRequete[0];
        $coachNom = $coach->getNom();
        $coachPrenom = $coach->getPrenom();
        

        // TROUVER COACH DANS TABLE USER
        $idUserCoach= $coach->getUser();
        $coachUserRequete = $repoUser->findBy(['id'=>$idUserCoach]);
        $coachUser = $coachUserRequete[0];
        $coachEmail = $coachUser->getEmail();
        $coachTelephone = $coach->getTelephone();


        // RECUPERATION DONNEES RDV UTILISATEUR 
        
        $jour = date_create($_GET['jour']['date']); 
        $heure = date_create($_GET['heure']['date']);
        $jourMailer = date_format($jour, 'd-m-Y');
        $heureMailer = date_format($heure, 'h');
        $duree = $_GET['duree'];
        $lieu = $_GET['lieu'];
        $prix = $coach->getPrix();
        $amount = $duree*$prix;
        dump($amount);
        // FIN RECUPERATION 

        // RECUPERATION INFOS CLIENT
        $user = $this->getUser()->getId();
        $requeteClient = $repoClient->findBy(['user'=>$user]);
        $clientNom = $requeteClient[0]->getNom();
        $clientPrenom = $requeteClient[0]->getPrenom();
        $clientTelephone = $requeteClient[0]->getTelephone();
        $clientEmail = $this->getUser()->getEmail();
        


        // Mettre sa clé sk
        \Stripe\Stripe::setApiKey('clé secrete');

        if(isset($_POST['stripeToken'])){
            $token = $_POST['stripeToken'];

            $charge = \Stripe\Charge::create([
            'amount' => $amount*100,
            'currency' => 'eur',
            'description' => 'Client:' ." ". $clientNom ." ". $clientPrenom ." ". '-' ." ". 'Coach:' ." ". $coachNom ." ". $coachPrenom ,
            'source' => $token,
            ]);
            
            $heure = date_modify($heure, '-'.$duree.' hours');
            for($i=0; $i<$duree;$i++){
                    
                    $rdvPlage = new Rdv();
                    $rdvPlage->setClient($requeteClient[0]);
                    $rdvPlage->setCoach($coach);
                    $rdvPlage->setDuree($duree);
                    $rdvPlage->setJour($jour);
                    $rdvPlage->setHeure(date_modify($heure, "+1 hours"));
                    $rdvPlage->setLieu($lieu);
                    if($i==0){
                        $rdvPlage->setTotal($amount);
                    }
                    $manager->persist($rdvPlage);
                    $manager->flush();
                }
                $mailer->sendRdvCoach($coachEmail,$clientNom,$clientTelephone,$duree,$heureMailer, $jourMailer, $lieu,$amount,'confirmationRdvCoach.html.twig');
                $mailer->sendRdvClient($clientEmail,$coachNom,$coachTelephone,$duree,$heureMailer, $jourMailer, $lieu,$amount,'confirmationRdvClient.html.twig');

             return $this->redirectToRoute('confirmation.paiement');   
        }

    
        return $this->render('/client/confirmationRdv.html.twig', [
            'coachNom' => $coachNom, 
            'coachPrenom' => $coachPrenom,
            'heureRdv' => $heure,
            'duree' => $duree, 
            'total' => $amount, 
            'lieu' => $lieu, 
            'jour' => $jour
        ]);

    }

    /**
     * @Route("/confirmation/paiement", name="confirmation.paiement")
    */
    public function paymentDone(){

        return $this->render('/client/confirmationPaiement.html.twig');
    }


}
