<?php

namespace App\Controller;

use App\Entity\CoachSearch;
use App\Form\CoachSearchType;
use App\Repository\CoachRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, CoachRepository $repo, PaginatorInterface $paginator)
    {
        $search = new CoachSearch;
        $form = $this->createForm(CoachSearchType::class, $search);
        $form->handleRequest($request);

        // $coachlist = $repo->findAll();
        $coachlist = $paginator->paginate(
            $repo->findGoodCoach($search),
            $request->query->getInt('page',1),
            12
        );

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form'=>$form->createView(),
            'coachs'=>$coachlist
        ]);
    }
}
