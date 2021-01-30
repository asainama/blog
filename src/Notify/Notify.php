<?php

namespace App\Notify;

use PDO;
use SplSubject;
use SplObserver;
use App\Helpers\Mailer;
use App\Helpers\QueryBuilder;

class Notify implements SplObserver
{
    /**
     * @inheritdoc
     */
    public function update(SplSubject $subject)
    {
        // dd("La classe Notify a été alerté. L'article '" . $subject->getComment()->getContent() . "' a été crée.\n");
        $this->sendMessageComment($subject);
        // Ici dans un cas réel, on envoie un e-mail à tous les utilisateurs avec un lien vers le nouvel article
    }

    public function sendMessageComment($comment)
    {
        $comment = $comment->getComment();
        $user = $comment->getUserId();
        $qb = new QueryBuilder();
        $query = $qb
            ->select()
            ->from('User')
            ->where('id = :id')
            ->params(
                [
                    'id' => $comment->getUserId()
                ]
            )
            ->execute();
        $query->setFetchMode(PDO::FETCH_CLASS, User::class);
        if ($query !== false) {
            $user = $query->fetch()['username'];
        }
        $message = (new \Swift_Message("Commentaire: Demande de validation du commentaire id " . $comment->getId()))
        ->setFrom([$_ENV['MAILER_USER'] => 'Blog commentaire en attente'])
        ->setTo([$_ENV['MAILER_USER']])
        ->setBody(
            "Le contenu du commentaire : " . $comment->getContent() . "\n"
            . "posté par l'utilisateur : " . $user . "\n"
            . "pour l'article : " .  $comment->getPostId() . "\n"
        );
        $mailer = new Mailer();
        return $mailer->send($message);
    }
}
