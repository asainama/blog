<?php

namespace App\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

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
}
