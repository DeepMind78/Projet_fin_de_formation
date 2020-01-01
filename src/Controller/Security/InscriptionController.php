<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InscriptionController extends AbstractController
{
    /**
     * @Route("/inscription/coach", name="inscriptionCoach")
     */
    public function coachInscription(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User;
        $form = $this->createForm(InscriptionType::class,$user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $hash= $encoder->encodePassword($user, $user->getPassword()); // Il saura faire le lien car nous avons déclaré quel algorithme utiliser pour cette classe $user dans les configurations. 
            $user->setPassword($hash);
            $user->setRoles(['ROLE_COACH']);
            $manager->persist($user);
            $manager->flush();
        }

        return $this->render('inscription/coach.html.twig', [
            'formInscription' => $form->createView()
        ]);
    }

    /**
     * @Route("/inscription/client", name="inscriptionClient")
     */
    public function clientInscription(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {

        $user = new User;
        $form = $this->createForm(InscriptionType::class,$user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $hash= $encoder->encodePassword($user, $user->getPassword()); // Il saura faire le lien car nous avons déclaré quel algorithme utiliser pour cette classe $user dans les configurations. 
            $user->setPassword($hash);
            $user->setRoles(['ROLE_CLIENT']);
            $manager->persist($user);
            $manager->flush();
        }

        return $this->render('inscription/client.html.twig', [
            'formInscription' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authentificationUtils)
    {
        $error = $authentificationUtils->getLastAuthenticationError();
        $lastUsername = $authentificationUtils->getLastUsername();
        return $this->render('inscription/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error
        ]);
    }
}
