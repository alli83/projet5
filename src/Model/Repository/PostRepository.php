<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\Post;
use App\Service\Database;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;
use App\Service\ErrorsHandlers\Errors;
use DateTime;
use PDO;

final class PostRepository implements EntityRepositoryInterface
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

    public function find(int $id): ?Post
    {
        return null;
    }


    public function findOneBy(array $criteria, array $orderBy = null): ?Post
    {
        $query = 'SELECT post.id, post.title, post.stand_first, post.text, post.creation_date, post.last_update, user.pseudo 
        FROM post INNER JOIN user ON post.userId = user.id 
        WHERE 1 ';

        $valuesToBind = [];
        foreach ($criteria as $key => $val) {
            if (intval($val) === $val) {
                $valuesToBind[] = ['key' => ':' . $key, 'value' => $val];
                $query .= "AND post.$key = :$key ";
            } else {
                $valuesToBind[] = ['key' => ':' . $key, 'value' => $val];
                $query .= "AND post.$key = :$key ";
            }
        }
        $req = $this->pdo->prepare($query);
        $req->setFetchMode(\PDO::FETCH_CLASS, Post::class, [$criteria]);
        foreach ($valuesToBind as $item) {
            $req->bindValue($item['key'], $item['value']);
        }

        $req->execute();
        $data = $req->fetch();

        return $data === false ? null : $data;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        return null;
    }

    public function findAllIds(): ?array
    {
        $req = $this->pdo->prepare('select id from post');

        $req->execute();
        $datas = $req->fetchAll(\PDO::FETCH_COLUMN);
        return  $datas === false ? null : $datas;
    }

    public function findAll(int $limit = null, int $offset = null, array $orderBy = null): ?array
    {
        $query = 'select 
        post.id, post.title, post.stand_first, post.text, post.creation_date, post.last_update, pseudo
        from post INNER JOIN user ON post.userId = user.id';
        $query = $query . " LIMIT 3";
        if ($offset !== null) {
            $query = $query . " OFFSET $offset";
        }
        $req = $this->pdo->prepare($query);
        $req->execute();
        $datas = $req->fetchAll(\PDO::FETCH_CLASS, Post::class);
        return  $datas === false ? null : $datas;
    }

    public function create(object $post): bool
    {
        $title = $post->getTitle();
        $standFirst = $post->getstandFirst();
        $text = $post->getText();
        $userId = $post->getUserId();

        $valuesToBind = [
            ":title" => $title,
            ":stand_first" => $standFirst,
            ":text" => $text,
            ":userId" => $userId
        ];

        $req = $this->pdo->prepare("INSERT INTO post (title,stand_first,text,userId,last_update) 
        VALUES ( :title, :stand_first, :text, :userId, now())");
        foreach ($valuesToBind as $key => $val) {
            $req->bindValue($key, $val);
        }
        return $req->execute();
    }

    public function update(object $post): bool
    {
        $id = $post->getId();
        $title = $post->getTitle();
        $standFirst = $post->getstandFirst();
        $text = $post->getText();
        $valuesToBind = [
            ":title" => $title,
            ":stand_first" => $standFirst,
            ":text" => $text
        ];

        $req = $this->pdo->prepare("UPDATE post SET title = :title, stand_first = :stand_first,
        text = :text, last_update = now() WHERE id = ${id}");

        foreach ($valuesToBind as $key => $val) {
            $req->bindValue($key, $val);
        }
        return $req->execute();
    }

    public function delete(object $post): bool
    {

        $id = $post->getId();
        $req = $this->pdo->prepare("DELETE FROM post WHERE id = ${id}");
        if ($req->execute()) {
            return true;
        }
        return false;
    }
}
