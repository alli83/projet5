<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Service\Database;
use App\Model\Entity\Comment;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;
use PDO;

final class CommentRepository implements EntityRepositoryInterface
{
    private Database $database;
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->pdo = $this->getDatabase()->connectToDb();
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function find(int $id): ?Comment
    {
        return null;
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Comment
    {
        return null;
    }


    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $query = 'select comment.text, comment.created_date, user.pseudo from comment 
        INNER JOIN user ON comment.idUser = user.id
        where 1 ';
        $query2 = 'AND status = "validated"';


        if ($orderBy !== null && array_keys($orderBy)[0] === "order") {
            $query2 = $query2 . " ORDER BY Created_date DESC";
        }

        $valuesToBind = [];
        foreach ($criteria as $key => $val) {
            if ((int)$val === $val) {
                $valuesToBind[] = ['key' => ':' . $key, 'value' => $val, 'type' => \PDO::PARAM_INT];
                $query .= "AND $key = :$key ";
            } else {
                $valuesToBind[] = ['key' => ':' . $key, 'value' => $val, 'type' => \PDO::PARAM_STR];
                $query .= "AND $key = :$key ";
            }
        }
        $query = $query . $query2;
        $req = $this->pdo->prepare($query);
        $req->setFetchMode(\PDO::FETCH_ASSOC);
        foreach ($valuesToBind as $item) {
            $req->bindValue($item['key'], $item['value'], $item['type']);
        }

        $req->execute();
        $data = $req->fetchAll();

        return $data === false ? null : $data;
    }

    public function findAll(int $limit = null, int $offset = null, array $orderBy = null): ?array
    {
        $query = 'select 
        comment.id, comment.status, comment.text, comment.created_date, comment.last_update, user.pseudo, post.title
        from comment INNER JOIN user ON comment.idUser = user.id 
        INNER JOIN post ON comment.idPost = post.id WHERE comment.status = "created" OR comment.status = "validated"';

        $query = $query . " LIMIT 3";
        if ($offset !== null) {
            $query = $query . " OFFSET $offset";
        }
        $req = $this->pdo->prepare($query);
        $req->execute();
        $datas = $req->fetchAll(\PDO::FETCH_CLASS, Comment::class);
        return  $datas === false ? null : $datas;
    }

    public function create(object $comment): bool
    {
        $req = $this->pdo->prepare('INSERT INTO comment (pseudo, text, idPost, idUser) VALUES(:pseudo, :text, :idPost, :idUser)');

        $req->bindValue("pseudo", $comment->getPseudo());
        $req->bindValue("text", $comment->getText());
        $req->bindValue("idPost", $comment->getIdPost());
        $req->bindValue("idUser", $comment->getIdUser());

        return $req->execute();
    }

    public function validate(object $comment): bool
    {
        $id = $comment->getId();
        $req = $this->pdo->prepare("UPDATE comment SET status = 'validated', validation_date = now(), last_update = now() WHERE id = ${id}");
        if ($req->execute()) {
            return true;
        }
        return false;
    }

    public function update(object $comment): bool
    {
        return false;
    }

    public function delete(object $comment): bool
    {
        $id = $comment->getId();
        $req = $this->pdo->prepare("UPDATE comment SET status = 'cancelled', suppression_date = now(), last_update = now() WHERE id = ${id}");
        if ($req->execute()) {
            return true;
        }
        return false;
    }
}
