<?php

namespace App\Controllers;

use App\Model\User;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\GlobalHelper;
use App\Router\Router;
use App\Helpers\Mailer;
use App\Helpers\QueryBuilder;
use App\Helpers\SessionHelper;
use App\Validator\UserValidator;
use App\Validator\SignInValidator;

class AuthentificationController extends AbstractController
{
    /**
     * Undocumented function
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Router $router
     * @return void
     */
    public function login(Router $router)
    {
        if (GlobalHelper::method() === 'POST') {
            CSRF::verifToken(GlobalHelper::post('token'), $router, 'login');
            $user = new User();
            $user->setEmail(GlobalHelper::post('email') ? GlobalHelper::post('email') : null);
            $user->setPassword(GlobalHelper::post('password') ? GlobalHelper::post('password') : null);
            $isValidate = $this->validateUser(0, $user, $router, '/authentification/login.html.twig');
            if (!$isValidate) {
                $this->checkUser($user, $router);
            }
        } elseif (GlobalHelper::method() === 'GET') {
            return $this->twig->render(
                '/authentification/login.html.twig',
                [
                    'router' => $router
                ]
            );
        }
    }
    /**
     * Undocumented function
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Router $router
     * @return void
     */
    public function signIn(Router $router)
    {
        // TODO: Faire une redirection si connecter
        if (GlobalHelper::method() === 'POST') {
            CSRF::verifToken(GlobalHelper::post('token'), $router, 'signin');
            $errors = [];
            $user = new User();
            $user->setUsername(GlobalHelper::post('username') ? GlobalHelper::post('username') : null);
            $user->setEmail(GlobalHelper::post('email') ? GlobalHelper::post('email') : null);
            $user->setPassword(GlobalHelper::post('password') ? GlobalHelper::post('password') : null);
            $user->setRoleId(2);
            if (GlobalHelper::post('password') !== GlobalHelper::post('repassword') || empty(GlobalHelper::post('repassword'))) {
                $errors['repassword'] = array("Les mots ne passe ne sont pas équivalent");
            }
            $user->setValidate(0);
            $isValidate = $this->validateUser(1, $user, $router, '/authentification/signin.html.twig', $errors);
            if (!$isValidate) {
                /** @var QueryBuilder */
                $query =  $this->signInUser($user);
                if ($query === false) {
                    header('Location: ' . $router->generate('signin') . "?insert=0");
                }
                $mailer = (new Mailer())->sendMessageSubscribe($user);
                if ($mailer) {
                    header('Location: ' . $router->generate('signin') . "?insert=1&mail=1");
                } elseif ($mailer === false) {
                    header('Location: ' . $router->generate('signin') . "?insert=1&mail=0");
                }
            }
        } elseif (GlobalHelper::method() === 'GET') {
            return $this->twig->render(
                '/authentification/signin.html.twig',
                [
                    'router' => $router
                ]
            );
        }
    }

    /**
     * Undocumented function
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @param Router $router
     * @return void
     */
    public function code(Router $router)
    {
        if (GlobalHelper::method() === 'POST') {
            SessionHelper::sessionStart();
            $obj = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            $userArray = json_decode($obj, true);
            $code = GlobalHelper::post('code') ? GlobalHelper::post('code') : null;
            $user = new User();
            $user->setId($userArray['id']);
            $user->setEmail($userArray['email']);
            $user->setPassword($userArray['password']);
            $user->setRoleId($userArray['role_id']);
            $user->setCode($userArray['code']);
            if ($code === $user->getCode()) {
                $user->setCode(0);
                $user->setValidate(1);
                /** @var Query */
                $query = $this->updateUser($user, $router);
                if ($query === false) {
                    http_response_code(302);
                    header('Location:' . $router->generate('login') . '?created=0');
                    die;
                } elseif ($query !== false) {
                    SessionHelper::sessionStart();
                    $_SESSION['auth'] = json_encode(
                        array(
                            "id" => $user->getId(),
                            "role" => $user->getRoleId()
                        )
                    );
                    http_response_code(302);
                    header('Location: ' . $router->generate('index') . '?granted=1');
                    die;
                }
            } elseif ($user === null || $user->getCode() === null) {
                http_response_code(302);
                header('Location:' . $router->generate('login') . '?code=0');
                die;
            } elseif ($user !== null || $user->getCode() !== null) {
                http_response_code(302);
                header('Location:' . $router->generate('login') . '?code=2');
                die;
            }
        }
    }

    /**
     * Undocumented function
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Router $router
     * @return void
     */
    public function logout(Router $router)
    {
        Auth::disconnect($router);
    }

    /**
     * Undocumented function
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @param User $user
     * @param Router $router
     * @return void
     */
    private function checkUser(User $user, Router $router)
    {
        $query = (new QueryBuilder())
                    ->select()
                    ->from('User')
                    ->where('email = :email')
                    ->params(['email' => $user->getEmail()])
                    ->execute();
        $query->setFetchMode(\PDO::FETCH_CLASS, User::class);
        if ($query === false) {
            http_response_code(302);
            header('Location: ' . $router->generate('login') . '?code=1');
            die;
        }
        $user2 = $query->fetch();
        /** @var User|false */
        if ($user2 === false) {
            http_response_code(302);
            header('Location: ' . $router->generate('login') . '?unkrown=1');
            die;
        }
        if (GlobalHelper::post('password')) {
            if (password_verify($_POST['password'], $user2->getPassword())) {
                (new SessionHelper())->sessionStart();
                if ($user2->getValidate() === "0") {
                    $_SESSION['user'] = json_encode($user2->getArrayFromObject());
                    http_response_code(302);
                    header('Location: ' . $router->generate('login') . '?code=1');
                    die;
                }
                $_SESSION['auth'] = json_encode(
                    array(
                        "id" => $user2->getId(),
                        "role" => $user2->getRoleId()
                    )
                );
                http_response_code(302);
                header('Location: ' . $router->generate('index') . '?granted=1');
                die;
            }
            return $this->twig->render(
                '/authentification/login.html.twig',
                [
                    'router' => $router,
                    'errordenied' => 1,
                    'errors' => [
                        'password' =>
                            [
                                'Mot de passe incorrect'
                            ]
                    ]
                ]
            );
        }
    }
    private function validateUser(int $type, User $user, Router $router, $url, array $errors = []): bool
    {
        $data = $user->getArrayFromObject();
        $userValidator = $type === 1 ?  new SignInValidator($data) : new UserValidator($data);
        $userValidator->isValid();
        
        if ($userValidator->error()) {
            $errors = array_merge($userValidator->error(), $errors);
            return $this->twig->render(
                $url,
                [
                    'post' => $user,
                    'errors' => $errors,
                    'router' => $router
                    ]
            );
            return true;
        }
        return false;
    }

    private function signInUser(User $user)
    {
        $user->setCode(random_int(10000, 99999));
        $keys =  array_keys($user->getArrayFromObject());
        array_shift($keys);
        $keys = implode(",", $keys);
        $values =  array_values($user->getArrayFromObject());
        array_shift($values);
        $values = implode("§", $values);
        return (new QueryBuilder())
            ->insert('User')
            ->columns($keys)
            ->values($values)
            ->execute();
    }

    private function updateUser(User $user)
    {
        return (new QueryBuilder())
            ->update('User')
            ->set("code = :code")
            ->set("validate = :validate")
            ->where('id = :id')
            ->params(
                [
                    'id' => $user->getId(),
                    'code' => $user->getCode(),
                    'validate' => $user->getValidate(),
                ]
            )
            ->execute();
    }
}
