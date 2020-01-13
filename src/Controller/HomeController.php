<?php

namespace App\Controller;

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

        // $coachlist = $repo->findAll();
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
        if (!empty($_POST)) {
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $email = $_POST['email'];
            $message = $_POST['message'];
            $result = $mailer->sendContact($nom, $prenom, $email, $message, 'contact.html.twig');

            $json = json_encode([
                'result' => 'ok'
            ]);

            return new JsonResponse($json);
        }
    }
}