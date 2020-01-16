<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\CoachSearch;
use App\Form\CoachSearchType;
use App\Service\MailerService;
use App\Repository\CoachRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Symfony\Component\Serializer\SerializerInterface;
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

        // $coachlist2 = $repo->findAll();
        $coachlist = $paginator->paginate(
            $repo->findGoodCoach($search),
            $request->query->getInt('page', 1),
            12
        );


        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'form' => $form->createView(),
            'coachs' => $coachlist
        ]);



    }

    /**
     * @Route("/contact", name="contact", methods={"POST"})
     */

    public function contact(MailerService $mailer, SerializerInterface $serializer)
    {
        
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $email = $_POST['email'];
            $message = $_POST['message'];
            $mailer->sendContact($nom, $prenom, $email, $message, 'contact.html.twig');
            $json = $serializer->serialize([
                'result' => 'ok'
            ],'json');
            header('Content-Type: application/json');

            
           
            return new Response($json);

    }

    /**
     * @Route("/search", name="search", methods={"POST"})
     */
    public function search(SerializerInterface $serializer, Request $request, CoachRepository $repo){
        $search = new CoachSearch;
        // $search->setVille($_POST['ville'])->setSport($_POST['sport']);
        $ville = $_POST['coach_search']['ville'];
        $sport = $_POST['coach_search']['sport'];

        $search->setVille($ville)->setSport($sport);

        $coachlist = $repo->findGoodCoach($search);
        
        $rows = array();
        foreach($coachlist as $coach){
            $rows[] = array("nom"=>$coach->getNom(), "prenom"=>$coach->getPrenom(), "ville" => $coach->getVille(), "prix"=>$coach->getPrix(), 'id'=> $coach->getId(),'description'=>$coach->getDescriptionCoach(),'domaine'=>$coach->getDomaine(), 'image'=>$coach->getFilename());
        }

        $json = json_encode($rows);


        header('Content-Type: application/json');
        return new Response($json);

    }
}