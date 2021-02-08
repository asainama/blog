<?php

namespace App\Manager;

use PDO;
use SplSubject;
use SplObserver;
use SplObjectStorage;
use App\Helpers\QueryBuilder;

class AdminManager implements SplSubject
{
    private $comments;
    private $errors = [];
    private $delete = [];
    private $update = [];
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

    public function updateOrDeleteComment(array $comments)
    {
        $this->comments = $comments;
        foreach ($comments as $key => $value) {
            if ($value === "1") {
                $this->updateComment($key);
            } elseif ($value === "2") {
                $this->deleteComment($key);
            }
        }
        $this->notify();
        return $this->errors;
    }

    private function updateComment($id)
    {
        $comment = $this->getComment($id);
        $qb = new QueryBuilder();
        $query = $qb
                ->update('Comment')
                ->set("validate = 1")
                ->where('id = :id')
                ->params(
                    [
                        'id' => $id,
                    ]
                )
                ->execute();
        if ($query === false) {
            $this->errors[$id] = array("Le commentaire $id n'a pas pu être mis à jour");
        }
        if ($comment !== false) {
            array_push($this->update, $comment);
        }
    }

    private function getComment($id)
    {
        $query = (new QueryBuilder())
                ->select()
                ->from('Comment')
                ->where('id = :id')
                ->params(
                    [
                        'id' => $id
                    ]
                )
                ->execute();
        $query->setFetchMode(PDO::FETCH_CLASS, Comment::class);
        if ($query !== false) {
            return $query->fetch();
        }
        return false;
    }

    private function deleteComment($id)
    {
        $comment = $this->getComment($id);
        $qb = new QueryBuilder();
        $query = $qb
                ->delete()
                ->from('Comment')
                ->where('id = :id')
                ->params(
                    [
                        'id' => $id,
                    ]
                )
                ->execute();
        if ($query === false) {
            $this->errors[$id] = array("Le commentaire $id n'a pas pu être supprimé");
        }
        if ($comment !== false) {
            array_push($this->delete, $comment);
        }
    }

    public function getDelete()
    {
        return $this->delete;
    }

    public function getUpdate()
    {
        return $this->update;
    }

    public function getComments()
    {
        return $this->comments;
    }
}
