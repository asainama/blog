<?php

namespace App\Twig;

use PDO;
use App\Helpers\Url;
use Twig\TwigFilter;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Router\Router;
use Twig\TwigFunction;
use App\Helpers\QueryBuilder;
use Twig\Extension\AbstractExtension;
use App\Exception\AccessDeniedException;

class AppExtension extends AbstractExtension
{
    /**
     * Init Filters
     * @return void
     */
    public function getFilters()
    {
        return [
            new TwigFilter('excerpt', [$this, 'excerpt'], ['is_safe' => ['html']]),
        ];
    }
    /**
     * Return truncate string or null
     * @param string $content content to update
     * @param int    $limit   limit to truncate content
     * @return string|null
     */
    public function excerpt(string $content, int $limit = 60): ?string
    {
        if (mb_strlen($content) <= $limit) {
            return $content;
        }
        return substr($content, 0, $limit) . '...';
    }
    /**
     * Init Functions
     * @return void
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('assets', [$this,'assets'], ['is_safe' => ['html']]),
            new TwigFunction('nextLink', [$this,'nextLink'], ['is_safe' => ['html']]),
            new TwigFunction('previousLink', [$this,'previousLink'], ['is_safe' => ['html']]),
            new TwigFunction('nextArticleLink', [$this,'nextArticleLink'], ['is_safe' => ['html']]),
            new TwigFunction('previousArticleLink', [$this,'previousArticleLink'], ['is_safe' => ['html']]),
            new TwigFunction('showError', [$this,'showError'], ['is_safe' => ['html']]),
            new TwigFunction('isCheck', [$this,'isCheck'], ['is_safe' => ['html']]),
            new TwigFunction('isConnect', [$this,'isConnect'], ['is_safe' => ['html']]),
            new TwigFunction('isAdmin', [$this,'isAdmin'], ['is_safe' => ['html']]),
            new TwigFunction('csrf', [$this,'csrf'], ['is_safe' => ['html']]),
        ];
    }

    public function csrf(): ?string
    {
        return CSRF::createToken();
    }

    public function showError($errors): ?string
    {
        $content = "<div class='form_errors'>";
        foreach ($errors as $e) {
            $content .= "<span>$e</span>";
        }
        return $content .= "</div>";
    }
    public function isCheck(string $name)
    {
        if (isset($_GET[$name]) && $_GET[$name] === "1") {
            return 1;
        } elseif (isset($_GET[$name]) && $_GET[$name] === "0") {
            return 0;
        }
        return 2;
    }
    public function isConnect(): bool
    {
        try {
            Auth::isConnect();
        } catch (AccessDeniedException $e) {
            return false;
        }
        return true;
    }
    public function isAdmin(): bool
    {
        return Auth::isAdmin();
    }
    public function nextArticleLink(Router $router, array $params): ?string
    {
        $id = $params['id'];
        $query = $this->whereArticleLink("id < :id", $id);
        $query->setFetchMode(PDO::FETCH_CLASS, Post::class);
        /** @var Post|false Return Post|false */
        $post = $query->fetch();
        if (empty($post) || $post === false) {
            return null;
        }
        $link = $router->generate(
            'show',
            [
                    'id' => $post['id'],
                    'slug' => $post['slug']
            ]
        );
        return $this->getLink($link, 'Page suivante');
    }

    public function previousArticleLink(Router $router, array $params): ?string
    {
        $id = $params['id'];
        $query = $this->whereArticleLink("id > :id", $id);
        $query->setFetchMode(PDO::FETCH_CLASS, Post::class);
        /** @var Post|false Return Post|false */
        $post = $query->fetch();
        if (empty($post) || $post === false) {
            return null;
        }
        $link = $router->generate(
            'show',
            [
                    'id' => $post['id'],
                    'slug' => $post['slug']
            ]
        );
        return $this->getLink($link, 'Page précédente');
    }

    private function whereArticleLink($condition, $id)
    {
        return ((new QueryBuilder()))
            ->select('id', 'slug')
            ->from('Post')
            ->where($condition)
            ->params(['id' => $id])
            ->orderBy("created_at", "DESC")
            ->limit(12)
            ->page($this->getCurrentPage())
            ->execute();
    }
    /**
     * Return url to currentPage
     * @param string $link    url to update
     * @param int    $current numero to the current page
     * @return string|null
     */
    public function nextLink(string $link, $pages): ?string
    {
        $currentPage = $this->getCurrentPage();
        if ($currentPage >= $pages) {
            return null;
        }
        $link = $link . '?page=' . ($currentPage + 1);
        return $this->getLink($link, 'Page suivante');
    }

    private function getLink(string $link, string $content): ?string
    {
        return <<<HTML
        <a href="$link" class="btn primary">$content</a>
HTML;
    }

    public function previousLink(string $link): ?string
    {
        $currentPage = $this->getCurrentPage();
        if ($currentPage <= 1) {
            return null;
        }
        if ($currentPage > 2) {
            $link = $link . '?page=' . ($currentPage - 1);
        }
        return $this->getLink($link, 'Page précédente');
    }

    private function getCurrentPage(): int
    {
        return Url::getPositiveInt('page', 1);
    }
    /**
     * Return path assets
     * @param string|null $file    url to update
     * @param array       $options numero to the current page
     * @return string|null
     */
    public function assets(string $file = null, array $options = []): ?string
    {
        $path = null;
        if ($file !== null) {
            switch ($options['type']) {
                case "css":
                    // $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$file;
                    $path = '/assets/css/' . $file;
                    break;
                case "js":
                    // $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.$file;
                    $path = '/assets/js/' . $file;
                    break;
                case "img":
                    // $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.$file;
                    $path = '/assets/images/' . $file;
                    break;
            }
        }
        return $path;
    }
}
