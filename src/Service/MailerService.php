<?php
namespace App\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
class MailerService extends AbstractController
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param $token
     * @param $pseudo
     * @param $template
     * @param $to
     */
    public function sendToken($token, $to, $pseudo, $template)
    {
        $message = (new \Swift_Message('Mail de confirmation'))
            ->setFrom('cullellsullivan78@gmail.com')
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    'email/' . $template,
                    [
                        'token' => $token,
                        'pseudo' => $pseudo
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendTokenPassword($token, $to, $pseudo, $template)
    {
        $message = (new \Swift_Message('Réinitialisation de mot de passe'))
            ->setFrom('cullellsullivan78@gmail.com')
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    'email/' . $template,
                    [
                        'token' => $token,
                        'pseudo' => $pseudo
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendWelcome($to, $pseudo, $template)
    {
        $message = (new \Swift_Message('Bienvenue'))
            ->setFrom('cullellsullivan78@gmail.com')
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    'email/' . $template,
                    [
                        'pseudo' => $pseudo
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendRdvCoach($to, $infoClient, $telephone ,$duree, $heure, $jour,  $lieu, $total, $template)
    {
        $message = (new \Swift_Message('Nouveau rendez-vous'))
            ->setFrom('cullellsullivan78@gmail.com')
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    'email/' . $template,
                    [
                        'infoClient' => $infoClient,
                        'duree' => $duree,
                        'lieu' => $lieu,
                        'total' => $total,
                        'heure'=>$heure,
                        'jour'=>$jour, 
                        'telephone'=> $telephone

                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendRdvClient($to, $infoCoach, $telephone, $duree, $heure, $jour, $lieu, $total, $template)
    {
        $message = (new \Swift_Message('Nouveau rendez-vous'))
            ->setFrom('cullellsullivan78@gmail.com')
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    'email/' . $template,
                    [
                        'infoCoach' => $infoCoach,
                        'duree' => $duree,
                        'lieu' => $lieu,
                        'total' => $total,
                        'heure'=>$heure,
                        'jour'=>$jour, 
                        'telephone'=>$telephone
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendContact($nom, $prenom, $email, $messageUtilisateur, $template) {
        $message = (new \Swift_Message('Contact SAV'))
            ->setFrom('cullellsullivan78@gmail.com')
            ->setTo('cullellsullivan78@gmail.com')
            ->setBody(
                $this->renderView(
                    'email/' . $template,
                    [
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'message' => $messageUtilisateur,

                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}