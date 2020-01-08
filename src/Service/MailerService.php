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
     * @param $template
     * @param $to
     */
    public function sendToken($token, $to, $template)
    {
        $message = (new \Swift_Message('Mail de confirmation'))
            ->setFrom('registration@al-houria.com')
            ->setTo($to)
            ->setBody(
                $this->renderView(
                    'emails/' . $template,
                    [
                        'token' => $token
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}