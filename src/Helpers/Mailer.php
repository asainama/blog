<?php

namespace App\Helpers;

use Swift_Mailer;
use App\Model\User;
use App\Model\Comment;
use App\Model\Contact;
use App\Router\Router;
use App\Exception\MailerException;

class Mailer
{
    /**
     * Dotenv Object
     *
     * @var Dotenv\Dotenv $_dotenv Object Dotenv
     */
    private $dotenv;

    private $mailer;

    /**
     * Init Database
     * @return void
     */
    public function __construct()
    {
        $transport = (new \Swift_SmtpTransport(getenv('MAILER_TRANSPORT'), getenv('MAILER_PORT'), getenv('MAILER_PROTOCOLE')))
        ->setUsername(getenv('MAILER_USER'))
        ->setPassword(getenv('MAILER_PASS'));
        $this->mailer = new Swift_Mailer($transport);
    }

    public function sendMessageSubscribe(User $user): bool
    {
        $message = (new \Swift_Message('Contact: Message de contact'))
        ->setFrom([getenv('MAILER_USER') => 'Sylvain Ainama'])
        ->setTo([$user->getEmail() => $user->getUsername()])
        ->setBody(
            "Votre code de validation est : " . $user->getCode()
            . "\n Pour poursuivre veuillez-vous connecter."
        );
        try {
            $this->mailer->send($message);
            return true;
        } catch (\Swift_TransportException $e) {
            return false;
        }
    }

    /**
     * Undocumented function
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @param Contact $contact
     * @param Router $router
     * @return void
     */
    public function sendMessageContact(Contact $contact, Router $router)
    {
        $message = (new \Swift_Message('Contact: Message de contact'))
        ->setFrom([getenv('MAILER_USER') => 'Sylvain Ainama'])
        ->setTo([$contact->getEmail() => $contact->getFirstname() . ' ' . $contact->getLastname()])
        ->setBody($contact->getMessage());
        try {
            $this->mailer->send($message);
            header('Location: ' . $router->generate('contact') . '?mailervalidate=1');
            die;
        } catch (\Swift_TransportException $e) {
            header('Location: ' . $router->generate('contact') . '?mailertransport=1');
            die;
        }
    }

    public function send(\Swift_Message $message)
    {
        try {
            $this->mailer->send($message);
        } catch (\Swift_TransportException $e) {
            return false;
        }
        return true;
    }
}
