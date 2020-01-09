<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\InscriptionType;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Response;
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
            return $this->redirectToRoute('confirmationpage');
        }

        return $this->render('inscription/coach.html.twig', [
            'formInscription' => $form->createView(), 'last_username' => $lastUsername, 'error' => $error,
            ]);
    }

    /**
     * @Route("/confirmationpage", name="confirmationpage")
     */
    public function confirmationpage(){
        return $this->render('inscription/confirmation.html.twig');
    }

    /**
     * @Route("/compte/confirmation/{token}/{pseudo}", name="confirme_compte")
     * @param $token
     * @param $pseudo
     */
    public function confirmAccount($token, $pseudo, EntityManagerInterface $manager, MailerService $mailerService)
    {
        $user = $manager->getRepository(User::class)->findOneBy(['pseudo' => $pseudo]);
        $tokenExist = $user->getConfirmationToken();
        $email = $user->getEmail();
        $pseudo = $user->getPseudo();

        if($user->getRoles()[0] === "ROLE_CLIENT"){
            $mailerService->sendWelcome($email, $pseudo, 'welcomeClient.html.twig');
        } elseif ($user->getRoles()[0] === "ROLE_COACH"){
            $mailerService->sendWelcome($email, $pseudo, 'welcomeCoach.html.twig');
        }

        if($token === $tokenExist) {
            $user->setConfirmationToken(null);
            $user->setEnabled(true);
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('app_login');
        } else {
            return $this->render('security/login.html.twig');
        }
    }


    /**
     * @Route("/inscription/client", name="inscriptionClient")
     * @throws \Exception
     */
    public function clientInscription(AuthenticationUtils $authenticationUtils, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, MailerService $mailerService)
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
            $user->setRoles(['ROLE_CLIENT']);
            $user->setEnabled(false);
            $manager->persist($user);
            $manager->flush();
            $token = $user->getConfirmationToken();
            $email = $user->getEmail();
            $pseudo = $user->getPseudo();
            $mailerService->sendToken($token, $email, $pseudo, 'emailconfirm.html.twig');
            $this->addFlash('user-error', 'Votre inscription a été validée, vous allez recevoir un email de confirmation pour activer votre compte et pouvoir vous connectez');
            return $this->redirectToRoute('confirmationpage');
        }

        return $this->render('inscription/client.html.twig', [
            'formInscription' => $form->createView(), 'last_username' => $lastUsername, 'error' => $error,
        ]);
    }
    /**
     * @Route("/mot-de-passe-oublie", name="forgottenPassword")
     * @param Request $request
     * @param MailerService $mailerService
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function forgottenPassword(Request $request, MailerService $mailerService, \Swift_Mailer $mailer): \Symfony\Component\HttpFoundation\Response
    {
        if($request->isMethod('POST')) {
            $email = $request->get('email');
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
            if($user === null) {
                $this->addFlash('user-error', 'Utilisateur non identifié ');
                return $this->redirectToRoute('app_login');
            }
            $user->setTokenPassword(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));
            $user->setCreatedTokenPasswordAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $token = $user->getTokenPassword();
            $email = $user->getEmail();
            $pseudo = $user->getUsername();
            $mailerService->sendTokenPassword($token, $email, $pseudo, 'forgottenPassword.html.twig');
            return $this->redirectToRoute('home');
        }
        return $this->render('/security/forgottenPassword.html.twig');
    }

    /**
     * @Route("/reset-password/{token}", name="resetPassword")
     * @param Request $request
     * @param $token
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetPassword(Request $request, $token, UserPasswordEncoderInterface $passwordEncoder): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('POST')) {
            $user = $em->getRepository(User::class)->findOneBy(['tokenPassword' => $token]);
            if($user === null) {
                $this->addFlash('not-user-exist', 'Utilisateur non identifié');
                return $this->redirectToRoute('app_login');
            }
            $user->setTokenPassword(null);
            $user->setCreatedTokenPasswordAt(null);
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $request->get('password')
                )
            );
            $em->flush();
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/newPassword.html.twig');
    }

}
