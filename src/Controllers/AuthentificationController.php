<?php

namespace App\Controllers;

use App\Model\User;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Router\Router;
use App\Helpers\Mailer;
use App\Helpers\QueryBuilder;
use App\Helpers\SessionHelper;
use App\Validator\UserValidator;
use App\Validator\SignInValidator;

class AuthentificationController extends AbstractController
{
    public function login(Router $router)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verifToken($_POST['token'], $router, 'login');
            $user = new User();
            $user->setEmail(isset($_POST['email']) ? $_POST['email'] : null);
            $user->setPassword(isset($_POST['password']) ? $_POST['password'] : null);
            $isValidate = $this->validateUser(0, $user, $router, '/authentification/login.html.twig');
            if (!$isValidate) {
                $this->checkUser($user, $router);
            }
        } else {
            return $this->twig->render(
                '/authentification/login.html.twig',
                [
                    'router' => $router
                ]
            );
        }
    }
    public function signIn(Router $router)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verifToken($_POST['token'], $router, 'signin');
            $errors = [];
            $user = new User();
            $user->setUsername(isset($_POST['username']) ? $_POST['username'] : null);
            $user->setEmail(isset($_POST['email']) ? $_POST['email'] : null);
            $user->setPassword(isset($_POST['password']) ? $_POST['password'] : null);
            $user->setRoleId(2);
            if ($_POST['password'] !== $_POST['repassword'] || !isset($_POST['repassword']) || empty($_POST['repassword'])) {
                $errors['repassword'] = array("Les mots ne passe ne sont pas équivalent");
            }
            $user->setValidate(0);
            $isValidate = $this->validateUser(1, $user, $router, '/authentification/signin.html.twig', $errors);
            if (!$isValidate) {
                /** @var QueryBuilder */
                $query =  $this->signInUser($user);
                if ($query === false) {
                    header('Location: ' . $router->generate('signin') . "?insert=0");
                } else {
                    $mailer = (new Mailer())->sendMessageSubscribe($user);
                    if ($mailer) {
                        header('Location: ' . $router->generate('signin') . "?insert=1&mail=1");
                    } else {
                        header('Location: ' . $router->generate('signin') . "?insert=1&mail=0");
                    }
                }
            }
        } else {
            return $this->twig->render(
                '/authentification/signin.html.twig',
                [
                    'router' => $router
                ]
            );
        }
    }
    public function code(Router $router)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            SessionHelper::sessionStart();
            $obj = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            $userArray = json_decode($obj, true);
            $code = isset($_POST['code']) ? $_POST['code'] : null;
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
                // TODO: Update pas insert
                $query = $this->updateUser($user, $router);
                if ($query === false) {
                    // TODO: Erreur redirection
                    //  Message la création du compte a échoué
                    http_response_code(302);
                    header('Location:' . $router->generate('login') . '?created=0');
                    exit();
                } else {
                    SessionHelper::sessionStart();
                    $_SESSION['auth'] = json_encode(
                        array(
                            "id" => $user->getId(),
                            "role" => $user->getRoleId()
                        )
                    );
                    http_response_code(302);
                    header('Location: ' . $router->generate('index') . '?granted=1');
                    exit();
                }
            } elseif ($user === null || $user->getCode() === null) {
                http_response_code(302);
                header('Location:' . $router->generate('login') . '?code=0');
                exit();
            } else {
                http_response_code(302);
                header('Location:' . $router->generate('login') . '?code=2');
                exit();
            }
        }
    }

    public function logout(Router $router)
    {
        Auth::disconnect($router);
    }

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
            exit();
        } else {
            $user2 = $query->fetch();
            /** @var User|false */
            if ($user2 === false) {
                http_response_code(302);
                header('Location: ' . $router->generate('login') . '?unkrown=1');
                exit();
            }
            if (isset($_POST['password']) && !empty($_POST['password'])) {
                if (password_verify($_POST['password'], $user2->getPassword())) {
                    SessionHelper::sessionStart();
                    if ($user2->getValidate() === "0") {
                        $_SESSION['user'] = json_encode($user2->getArrayFromObject());
                        http_response_code(302);
                        header('Location: ' . $router->generate('login') . '?code=1');
                        exit();
                    }
                    $_SESSION['auth'] = json_encode(
                        array(
                            "id" => $user2->getId(),
                            "role" => $user2->getRoleId()
                        )
                    );
                    http_response_code(302);
                    header('Location: ' . $router->generate('index') . '?granted=1');
                    exit();
                } else {
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
        } else {
            return false;
        }
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
