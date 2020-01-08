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
}