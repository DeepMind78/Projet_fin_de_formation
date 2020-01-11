<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Form\RdvType;
use App\Repository\RdvRepository;
use App\Repository\CoachRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RdvController extends AbstractController
{
    /**
     * @Route("/rdv", name="rdv")
     */
    public function index()
    {
        return $this->render('rdv/index.html.twig', [
            'controller_name' => 'RdvController',
        ]);
    }

    /**
     * @Route ("/rdvCoach", name="coach.rdv")
     */

    public function rdvcoach(CoachRepository $repoCoach, RdvRepository $repoRdv, Request $request, EntityManagerInterface $manager)
    {
        $error = false;
        $rdvSubmit = new Rdv();
        $form = $this->createForm(RdvType::class,$rdvSubmit);
        $form->handleRequest($request);
        
        $user = $this->getUser()->getId();
        $resultat = $repoCoach->findBy(['user' => $user]);
        $idcoach = $resultat[0]->getId();
        $rdv = $repoRdv->findBy(['coach' => $idcoach]);

        if($form->isSubmitted() && $form->isValid()) {
            $heureUtilisateur = $rdvSubmit->getHeure();
            $dureeUtilisateur = $rdvSubmit->getDuree();
            $jourUtilisateur = $rdvSubmit->getJour();
            

            $variable = $this->rdvExist($heureUtilisateur,$dureeUtilisateur,$repoRdv,$jourUtilisateur);
        if($variable==true){
            $error = true;
        } else {
            date_modify($heureUtilisateur,'-'.($dureeUtilisateur).' hours');
            for($i=0; $i<$dureeUtilisateur;$i++){
                $rdvPlage = new Rdv();
                $rdvPlage->setCoach($resultat[0]);
                $rdvPlage->setDuree($dureeUtilisateur);
                $rdvPlage->setJour($jourUtilisateur);
                $rdvPlage->setHeure(date_modify($heureUtilisateur, "+1 hours"));
                $rdvPlage->setLieu('Repos');
                
                $manager->persist($rdvPlage);
                $manager->flush();
                
            }
            return $this->redirectToRoute('fichefullcoach', ['id' => $idcoach]);
           
        }
        }

        
        
        return $this->render('/rdv/rdvcoach.html.twig', [
            'rdvcoachs' => $rdv, 
            'form' => $form->createView(), 
            'error' => $error
        ]);
    }

    /**
     * @Route ("/rdvClient", name="client.rdv")
     */

    public function rdvclient(ClientRepository $repoClient, RdvRepository $repoRdv, CoachRepository $repoCoach) {
        $user = $this->getUser()->getId();
        $resultat = $repoClient->findBy(['user'=>$user]);
        $idclient = $resultat[0]->getId();
        $rdv = $repoRdv->findBy(['client'=> $idclient]);
        

        return $this->render('/rdv/rdvclient.html.twig', [
            'rdvclients' => $rdv
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
