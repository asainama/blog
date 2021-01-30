<?php

namespace App\Manager;

use SplSubject;
use SplObserver;
use SplObjectStorage;
use App\Model\Comment;
use App\Helpers\QueryBuilder;

class CommentManager implements SplSubject
{
    private $comment;
    /**
     * SplObjectStorage pour s'assurer de l'unicité des observateurs.
     *
     * @var SplObjectStorage
     */
    protected $observers;

    public function __construct()
    {
        $this->observers = new SplObjectStorage();
    }

    /**
     * @inheritdoc
     */
    public function attach(SplObserver $observer)
    {
        $this->observers->attach($observer);
    }

    /**
     * @inheritdoc
     */
    public function detach(SplObserver $observer)
    {
        $this->observers->detach($observer);
    }

    /**
     * @inheritdoc
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function insertComment(Comment $comment)
    {
        $keys =  array_keys($comment->getArrayFromObject());
        array_shift($keys);
        $keys = implode(",", $keys);
        $values =  array_values($comment->getArrayFromObject());
        array_shift($values);
        $values = implode("§", $values);
        $qb = new QueryBuilder();
        $query = $qb
        ->insert('Comment')
        ->columns($keys)
        ->values($values)
        ->execute();
        if ($query) {
            $id = $qb->getLastInsertId();
            $comment->setId($id);
            $this->comment = $comment;
            $this->notify();
        }
        return $query;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
