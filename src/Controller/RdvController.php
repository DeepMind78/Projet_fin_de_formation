<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\CoachRepository;
use App\Repository\RdvRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @Route ("/rdvClient", name="client.rdv")
     */

    public function rdvclient(ClientRepository $repoClient, RdvRepository $repoRdv, CoachRepository $repoCoach) {
        $user = $this->getUser()->getId();
        $resultat = $repoClient->findBy(['user'=>$user]);
        $idclient = $resultat[0]->getId();
        $rdv = $repoRdv->findBy(['client'=> $idclient]);
        //dump($idclient);
        //dump($rdv);

        //$coach = $repoRdv->findBy('coach_id')
        //$resultat1 = $repoCoach->findBy(['user'=>$user]);
        //$nomcoach = $resultat1[0]->getId();
        //dump($nomcoach);

        return $this->render('/rdv/rdvclient.html.twig', [
            'rdvclients' => $rdv
        ]);
    }

}
