<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\InscriptionType;
use App\Service\MailerService;
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
     * @throws \Exception
     */
    public function coachInscription(AuthenticationUtils $authenticationUtils, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, MailerService $mailerService)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = new User;
        $form = $this->createForm(InscriptionType::class,$user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user->setConfirmationToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
            $hash= $encoder->encodePassword($user, $user->getPassword()); // Il saura faire le lien car nous avons déclaré quel algorithme utiliser pour cette classe $user dans les configurations. 
            $user->setPassword($hash);
            $user->setRoles(['ROLE_COACH']);
            $user->setEnabled(false);
            $manager->persist($user);
            $manager->flush();
            $token = $user->getConfirmationToken();
            $email = $user->getEmail();
            $pseudo = $user->getPseudo();
            $mailerService->sendToken($token, $email, $pseudo, 'emailconfirm.html.twig');
            $this->addFlash('user-error', 'Votre inscription a été validée, vous allez recevoir un email de confirmation pour activer votre compte et pouvoir vous connectez');
            return $this->redirectToRoute('login');
        }

        return $this->render('inscription/coach.html.twig', [
            'formInscription' => $form->createView(), 'last_username' => $lastUsername, 'error' => $error,
            ]);
    }

    /**
     * @Route("/compte/confirmation/{token}/{pseudo}", name="confirme_compte")
     * @param $token
     * @param $pseudo
     */
    public function confirmAccount($token, $pseudo, EntityManagerInterface $manager)
    {
        $user = $manager->getRepository(User::class)->findOneBy(['pseudo' => $pseudo]);
        $tokenExist = $user->getConfirmationToken();
        if($token === $tokenExist) {
            $user->setConfirmationToken(null);
            $user->setEnabled(true);
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('app_login');
        } else {
            return $this->render('inscription/login.html.twig');
        }
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
            $hash= $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $user->setRoles(['ROLE_CLIENT']);
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('login');
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
        dump($lastUsername);
        return $this->render('inscription/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

}
