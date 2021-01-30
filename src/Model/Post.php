<?php

namespace App\Model;

class Post
{
    private $id;

    private $title;

    private $content;

    private $chapo;

    private $created_at;

    private $slug;

    private $user_id;
    
    private $draft;

    private $comments = [];

    /**
     * Get the value of draft
     */
    public function getDraft()
    {
        return $this->draft;
    }

    /**
     * Set the value of draft
     *
     * @return  self
     */
    public function setDraft($draft)
    {
        $this->draft = (int)$draft;

        return $this;
    }

    /**
     * Get the value of slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     *
     * @return  self
     */
    public function setSlug($slug)
    {
        $this->slug = htmlentities($slug);

        return $this;
    }


    public function titleToSlug(): ?string
    {
        return preg_replace('/ /', '-', strToLower($this->title));
    }
    /**
     * Get the value of created_at
     */
    public function getCreatedAt(): string
    {
        // return new \DateTime($this->created_at, new \DateTimeZone('Europe/Paris'));
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * Get the value of content
     */
    public function getContent()
    {
        return $this->br2nl($this->content);
    }
    private function br2nl($str)
    {
        return str_replace('<br />', "\n", $str);
    }
    /**
     * Set the value of content
     *
     * @return  self
     */
    public function setContent($content)
    {
        $this->content =  htmlentities(nl2br($content));
        return $this;
    }

    /**
     * Get the value of name
     */
    public function getTitle()
    {
        return htmlentities($this->title);
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setTitle($title)
    {
        $this->title =  htmlentities($title);

        return $this;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of user_id
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * Set the value of user_id
     *
     * @param int $user_id identifiant of user
     *
     * @return self
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getArrayFromObject(): ?array
    {
        return get_object_vars($this);
    }

    /**
     * Get the value of chapo
     */
    public function getChapo()
    {
        return $this->chapo;
    }

    /**
     * Set the value of chapo
     *
     * @return  self
     */
    public function setChapo($chapo)
    {
        $this->chapo =  htmlentities(nl2br($chapo));

        return $this;
    }

    /**
     * Get the value of comments
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set the value of comments
     *
     * @return  self
     */
    public function setComments(Comment $comment)
    {
        array_push($this->comments, $comment);
        return $this;
    }
}
