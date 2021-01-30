<?php

namespace App\Notify;

use PDO;
use SplSubject;
use SplObserver;
use App\Helpers\Mailer;
use App\Helpers\QueryBuilder;

class AdminNotify implements SplObserver
{
    /**
     * @inheritdoc
     */
    public function update(SplSubject $subject)
    {
        /** @var AdminManager */
        $subject = $subject;
        if (!empty($subject->getDelete())) {
            $this->sendMessageDelete($subject->getDelete());
        }
        if (!empty($subject->getUpdate())) {
            $this->sendMessageUpdate($subject->getUpdate());
        }
    }

    public function sendMessageDelete(array $delete)
    {
        foreach ($delete as $k => $d) {
            $query = (new QueryBuilder())
                ->select()
                ->from('Comment')
                ->where('id = :id')
                ->params(
                    [
                        'id' => $d
                    ]
                )
                ->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, Comment::class);
            if ($query !== false) {
                $comment = $query->fetch();
                $this->sendMessageComment($comment, 2);
            }
        }
    }

    public function sendMessageUpdate(array $update)
    {
        foreach ($update as $k => $u) {
            $query = (new QueryBuilder())
                ->select()
                ->from('Comment')
                ->where('id = :id')
                ->params(
                    [
                        'id' => $u
                    ]
                )
                ->execute();
            $query->setFetchMode(PDO::FETCH_CLASS, Comment::class);
            if ($query !== false) {
                $comment = $query->fetch();
                $this->sendMessageComment($comment, 1);
            }
        }
    }

    public function sendMessageComment($comment, int $type)
    {
        $user = $comment['user_id'];
        $qb = new QueryBuilder();
        $query = $qb
            ->select()
            ->from('User')
            ->where('id = :id')
            ->params(
                [
                    'id' => $comment['user_id']
                ]
            )
            ->execute();
        $query->setFetchMode(PDO::FETCH_CLASS, User::class);
        if ($query !== false) {
            $user = $query->fetch();
        }
        $message = null;
        if ($type === 2) {
            $message = (new \Swift_Message("Commentaire refusé"))
            ->setFrom([$_ENV['MAILER_USER']])
            ->setTo($user['email'])
            ->setBody(
                "Le contenu du commentaire : " . $comment['content'] . "\n"
                . "posté par l'utilisateur : " . $user['username'] . "\n"
                . "Votre commentaire a été refusé car il ne respectait pas les règles du site."
            );
        } elseif ($type === 1) {
            $message = (new \Swift_Message("Commentaire accepté"))
            ->setFrom([$_ENV['MAILER_USER']])
            ->setTo($user['email'])
            ->setBody(
                "Le contenu du commentaire : " . $comment['content'] . "\n"
                . "posté par l'utilisateur : " . $user['username'] . "\n"
                . "Fécilitations! Votre commentaire a été accepté."
            );
        }
        $mailer = new Mailer();
        return $mailer->send($message);
    }
}
