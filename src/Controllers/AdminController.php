<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;
use DateTime;
use Exception;
use App\Model\Post;
use App\Helpers\Url;
use App\Helpers\CSRF;
use App\Model\Comment;
use App\Router\Router;
use App\Notify\AdminNotify;
use App\Helpers\GlobalHelper;
use App\Helpers\QueryBuilder;
use App\Manager\AdminManager;
use App\Validator\PostValidator;

class AdminController extends AbstractController
{
    /**
     * Return Post index View
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Router $router
     * @return void
     */
    public function postindex(Router $router)
    {
        $currentPage = Url::getPositiveInt('page', 1);
        /** @var QueryBuilder */
        $count = (new QueryBuilder())->from("Post")->count();
        $pages = ceil($count / 12);
        if ($currentPage > $pages) {
            $query = (new QueryBuilder())
                    ->from('Post')
                    ->execute();
            if (empty($query->fetchAll())) {
                return $this->twig->render(
                    'admin/post/index.html.twig',
                    [
                        'posts' => [],
                        'router' => $router,
                        'pages' => $pages
                    ]
                );
            }
            throw new \Exception('Cette page n\'existe pas');
        }
        $query = (new QueryBuilder())
                ->select('Post.*,
                    (SELECT COUNT(*) 
                    FROM Comment 
                    WHERE Comment.post_id = Post.id 
                    and Comment.validate = 0)
                    AS comments')
                ->from('Post')
                ->orderBy("Post.created_at", "DESC")
                ->limit(12)
                ->page($currentPage)
                ->execute();
        $posts = $query->fetchAll(\PDO::FETCH_CLASS, Post::class);
        if ($posts !== false) {
            return $this->twig->render(
                'admin/post/index.html.twig',
                [
                    'posts' => $posts,
                    'router' => $router,
                    'pages' => $pages
                ]
            );
        } else {
            // TODO: Il n' y a aucun commentaires
        }
        // TODO : si pas de posts
        // Creer un tableau de commentaires
        // Match avec post_id et un count
        // Ou
        // Je met tout mes commentaires dans Post comments[]
        // form button post
    }

    /**
     * Return Post View Edit
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Router $router
     * @param array $params
     * @return void
     */
    public function postEdit(Router $router, array $params)
    {
        $id = $params['id'];
        if (GlobalHelper::method() === 'POST') {
            CSRF::verifToken(GlobalHelper::post('token'), $router, 'admin_post_edit', $id);
            /** @var Post|null */
            $post = $this->initPost($id);
            $this->validatePost($post, $router, 'admin/post/edit.html.twig', 'update', 2);
        } elseif (GlobalHelper::method() === 'GET') {
            $query = (new QueryBuilder())
            ->select()
            ->from('Post')
            ->where('id = :id')
            ->params(['id' => $id])
            ->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, Post::class);
            $post = $query->fetch();
            return $this->twig->render(
                'admin/post/edit.html.twig',
                [
                    'post' => $post,
                    'router' => $router
                ]
            );
        }
    }

    /**
     * Return Post New View
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Router $router
     * @return void
     */
    public function postNew(Router $router)
    {
        if (GlobalHelper::method() === 'POST') {
            CSRF::verifToken(GlobalHelper::post('token'), $router, 'admin_post_new');
            $post = $this->initPost();
            $this->validatePost($post, $router, 'admin/post/new.html.twig', 'created', 1);
        }
        return $this->twig->render(
            'admin/post/new.html.twig',
            [
                'router' => $router
            ]
        );
    }

    /**
     * Delete Post with id
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param Router $router
     * @param array $params
     * @return void
     */
    public function postDelete(Router $router, array $params)
    {
        if (array_key_exists('id', $params)) {
            $id = $params['id'];
            $query = (new QueryBuilder())
                ->delete()
                ->from('Post')
                ->where('id = :id')
                ->params(['id' => $id])
                ->execute();
            if ($query === false) {
                throw new Exception("Impossible de supprimer l'article $id dans la table Post");
            }
            header('Location: ' . $router->generate('admin_posts') . '?delete=1');
        }
    }

    /**
     * Return post View Comments
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @param Router $router
     * @param array $params
     * @return void
     */
    public function postComments(Router $router, array $params)
    {
        if (GlobalHelper::method() === 'POST') {
            CSRF::verifToken(GlobalHelper::post('token'), $router, 'admin_post_comments', $params['id']);
            $notify = new AdminNotify();
            $commentManager = new AdminManager();
            $commentManager->attach($notify);
            $errors = $commentManager->updateOrDeleteComment(GlobalHelper::allPost());
            if (empty($errors)) {
                http_response_code(302);
                header('Location: ' . $router->generate('admin_posts') . '?commentValidate=1');
                die;
            } elseif (!empty($errors)) {
                http_response_code(302);
                header('Location: ' . $router->generate('admin_posts') . '?commentValidate=0');
                die;
            }
        }
        if (array_key_exists('id', $params)) {
            $id = $params['id'];
            $qb = new QueryBuilder();
            $query = $qb
                ->select()
                ->from('Comment')
                ->where('post_id = :id')
                ->where('validate = 0')
                ->params(
                    [
                        'id' => $id
                    ]
                )
                ->execute();
            if ($query === false) {
                http_response_code(302);
                header('Location: ' . $router->generate('admin_posts') . '?comments=0');
                die;
            } elseif ($query !== false) {
                $comments = $query->fetchAll(PDO::FETCH_CLASS, Comment::class);
                return $this->twig->render(
                    '/admin/post/comments.html.twig',
                    [
                        'router' => $router,
                        'comments' => $comments
                    ]
                );
            }
        }
    }

    /**
     * Hydrate Post Object
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param string $id
     * @return Post|null
     */
    private function initPost(string $id = null): ?Post
    {
        $date = new DateTime();
        $post = new Post();
        $post->setId($id);
        $post->setTitle(GlobalHelper::post('title'));
        $post->setSlug($post->titleToSlug());
        $post->setDraft(GlobalHelper::post('draft') ? true : false);
        $post->setChapo(GlobalHelper::post('chapo'));
        $post->setContent(GlobalHelper::post('content'));
        $post->setCreatedAt($date);
        return $post;
    }

    private function updatePost(Post $post)
    {
        
        return (new QueryBuilder())
            ->update('Post')
            ->set("title = :title")
            ->set("content = :content")
            ->set("chapo = :chapo")
            ->set("draft = :draft")
            ->set("slug = :slug")
            ->set("created_at = :created_at")
            ->where('id = :id')
            ->params(
                [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'chapo' => $post->getChapo(),
                    'content' => $post->getContent(),
                    'draft' => $post->getDraft(),
                    'slug' => $post->getSlug(),
                    'created_at' => $post->getCreatedAt(),
                ]
            )
            ->execute();
    }

    private function validatePost(Post $post, Router $router, string $url, string $field, int $type)
    {
        $data = $post->getArrayFromObject();
        $postValidator = new PostValidator($data);
        $postValidator->isValid();
        if ($postValidator->error()) {
            $errors = $postValidator->error();
            return $this->twig->render(
                $url,
                [
                    'post' => $post,
                    'errors' => $errors,
                    'router' => $router
                    ]
            );
        } elseif ($postValidator->error() === false) {
            $query = ($type === 1) ? $this->insertPost($post) : $this->updatePost($post);
            if ($query === false) {
                header('Location: ' . $router->generate('admin_posts') . "?$field=0");
            } elseif ($query !== false) {
                header('Location: ' . $router->generate('admin_posts') . "?$field=1");
            }
        }
    }

    private function insertPost(Post $post)
    {
        $post->setUserId(1);
        $posts = $post->getArrayFromObject();
        array_pop($posts);
        $keys =  array_keys($posts);
        array_shift($keys);
        $keys = implode(",", $keys);
        $values =  array_values($posts);
        array_shift($values);
        $values = implode("§", $values);
        return (new QueryBuilder())
                ->insert('Post')
                ->columns($keys)
                ->values($values)
                ->execute();
    }
}
