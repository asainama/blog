<?php

namespace App\Controllers;

use App\Helpers\GlobalHelper;
use PDO;
use DateTime;
use Exception;
use App\Model\Post;
use App\Helpers\Url;
use App\Model\Comment;
use App\Notify\Notify;
use App\Router\Router;
use App\Helpers\QueryBuilder;
use App\Helpers\SessionHelper;
use App\Manager\CommentManager;
use App\Validator\CommentValidator;

class BlogController extends AbstractController
{
    /**
     * Show index page
     *
     * @param Router $router The route object
     * @return void
     */
    public function index(Router $router)
    {
        $currentPage = (new Url())->getPositiveInt('page', 1);
        /** @var QueryBuilder */
        $count = (new QueryBuilder())->from("Post")->count();
        $pages = ceil($count / 12);
        if ($currentPage > $pages) {
            $query = (new QueryBuilder())
                    ->from('Post')
                    ->execute();
            if (empty($query->fetchAll())) {
                return $this->twig->render(
                    '/post/index.html.twig',
                    [
                        'posts' => [],
                        'router' => $router,
                        'pages' => $pages
                    ]
                );
            }
            throw new Exception('Cette page n\'existe pas');
        }
        $query = (new QueryBuilder())
        ->select()
        ->from('Post')
        ->where('draft = 1')
        ->orderBy("created_at", "DESC")
        ->limit(12)
        ->page($currentPage)
        ->execute();
        $posts = $query
        ->fetchAll(
            PDO::FETCH_CLASS,
            Post::class
        );
        return $this->twig->render(
            '/post/index.html.twig',
            [
                'posts' => $posts,
                'router' => $router,
                'pages' => $pages
            ]
        );
    }
    /**
     * Show (show) page
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @param Router $router The route object
     * @return void
     */
    public function show(Router $router, array $params)
    {
        if (array_key_exists("id", $params) && array_key_exists("slug", $params)) {
            $slug = $params['slug'];
            $id = $params['id'];
            $query = (new QueryBuilder())
            ->select()
            ->from('Post')
            ->where('id = :id')
            ->where('draft = 1')
            ->params(['id' => $id])
            ->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, Post::class);
            /** @var Post|false Return Post|false */
            $post = $query->fetch();
            $comments = $this->getCommentByPost($post->getId());
            if ($post === false) {
                throw new \Exception('Aucun article ne correspond à cet ID');
            }
            if ($post->getSlug() !== $slug) {
                $url = $router->generate(
                    'show',
                    [
                        'slug' => $post->getSlug(),
                        'id' => $id
                    ]
                );
                http_response_code(302);
                header('Location: ', $url);
            }
            if (GlobalHelper::method() === 'POST') {
                SessionHelper::sessionStart();
                $id = json_decode($_SESSION['auth'], true)['id'];
                $comment = new Comment();
                $comment->setContent(GlobalHelper::post('content'));
                $comment->setCreatedAt(new DateTime());
                $comment->setValidate(0);
                $comment->setUserId($id);
                $comment->setPostId($params['id']);
                $data = $comment->getArrayFromObject();
                $commentValidator = new CommentValidator($data);
                $commentValidator->isValid();
                $errors = $commentValidator->error();
                if (!empty($errors)) {
                    return $this->twig->render(
                        '/post/show.html.twig',
                        [
                            'post' => $post,
                            'comments' => $comments,
                            'errors' => $errors,
                            'router' => $router,
                            'comment' => $comment
                        ]
                    );
                }
                $notify = new Notify();
                $commentManager = new CommentManager();
                $commentManager->attach($notify);
                $query = $commentManager->insertComment($comment);
                if ($query === false) {
                    return $this->twig->render(
                        '/post/show.html.twig',
                        [
                            'post' => $post,
                            'comments' => $comments,
                            'isValid' => false,
                            'router' => $router
                        ]
                    );
                }
                // TODO: Email de demande de Validation de la part de l'utilisateur
                // Oberserver Pattern
                return $this->twig->render(
                    '/post/show.html.twig',
                    [
                        'post' => $post,
                        'comments' => $comments,
                        'isValid' => true,
                        'router' => $router
                    ]
                );
            } elseif (GlobalHelper::method() === 'GET') {
                return $this->twig->render(
                    '/post/show.html.twig',
                    [
                        'post' => $post,
                        'comments' => $comments,
                        'router' => $router,
                    ]
                );
            }
        }
    }

    private function getCommentByPost($postid): ?array
    {
        $query = (new QueryBuilder())
            ->select()
            ->from('Comment, User')
            ->where('post_id = :post_id')
            ->where('User.id = Comment.user_id')
            ->where('Comment.validate = 1')
            ->params(['post_id' => $postid])
            ->execute();
        $comments = $query->fetchAll(\PDO::FETCH_CLASS, Comment::class);
        if ($query === false) {
            return null;
        }
        return $comments;
    }
}
