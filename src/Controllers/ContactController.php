<?php

namespace App\Controllers;

use App\Helpers\CSRF;
use App\Helpers\GlobalHelper;
use App\Model\Contact;
use App\Router\Router;
use App\Helpers\Mailer;
use App\Validator\ContactValidator;

class ContactController extends AbstractController
{
    /**
     * Show index page
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Router $router The route object
     * @return void
     */
    public function index(Router $router)
    {
        if (GlobalHelper::method() === 'POST') {
            CSRF::verifToken(GlobalHelper::post('token'), $router, 'contact');
            $contact = (new Contact())
                ->setFirstname(GlobalHelper::post('firstname'))
                ->setLastname(GlobalHelper::post('lastname'))
                ->setEmail(GlobalHelper::post('email'))
                ->setMessage(GlobalHelper::post('message'));
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
            } elseif (empty($errors)) {
                $mailer = new Mailer();
                $mailer->sendMessageContact($contact, $router);
            }
        } elseif (GlobalHelper::method() === 'GET') {
            return $this->twig->render(
                '/contact/contact.html.twig',
                [
                'router' => $router
                ]
            );
        }
    }
}
