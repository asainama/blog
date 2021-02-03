<?php

namespace App\Controllers;

use App\Helpers\CSRF;
use App\Model\Contact;
use App\Router\Router;
use App\Helpers\Mailer;
use App\Validator\ContactValidator;

class ContactController extends AbstractController
{
    /**
     * Show index page
     *
     * @param Router $router The route object
     * @return void
     */
    public function index(Router $router)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verifToken($_POST['token'], $router, 'contact');
            $contact = (new Contact())
                ->setFirstname($_POST['firstname'])
                ->setLastname($_POST['lastname'])
                ->setEmail($_POST['email'])
                ->setMessage($_POST['message']);
            $data = $contact->getArrayFromObject();
            $contactValidator = new ContactValidator($data);
            $contactValidator->isValid();
            $errors = $contactValidator->error();
            if (!empty($errors)) {
                return $this->twig->render(
                    '/contact/contact.html.twig',
                    [
                    'contact' => $contact,
                    'errors' => $errors,
                    'router' => $router
                    ]
                );
            } else {
                $mailer = new Mailer();
                $mailer->sendMessageContact($contact, $router);
            }
        } else {
            return $this->twig->render(
                '/contact/contact.html.twig',
                [
                'router' => $router
                ]
            );
        }
    }
}
