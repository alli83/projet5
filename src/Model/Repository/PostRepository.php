<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\Post;
use App\Service\Database;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;
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
        $query = 'SELECT post.id, post.title, post.stand_first, post.text, post.creation_date, post.last_update, post.file_attached, user.pseudo 
        FROM post INNER JOIN user ON post.userId = user.id 
        WHERE 1 ';

        $valuesToBind = [];
        foreach ($criteria as $key => $val) {
            if ((int)$val === $val) {
                $valuesToBind[] = ['key' => ':' . $key, 'value' => $val, 'type' => \PDO::PARAM_INT];
                $query .= "AND post.$key = :$key ";
            } else {
                $valuesToBind[] = ['key' => ':' . $key, 'value' => $val, 'type' => \PDO::PARAM_STR];
                $query .= "AND post.$key = :$key ";
            }
        }
        $req = $this->pdo->prepare($query);
        $req->setFetchMode(\PDO::FETCH_CLASS, Post::class, [$criteria]);
        foreach ($valuesToBind as $item) {
            $req->bindValue($item['key'], $item['value'], $item['type']);
        }

        $req->execute();
        $data = $req->fetch();

        return $data === false ? null : $data;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        return null;
    }

    public function findAll(int $limit = null, int $offset = null, array $orderBy = null): ?array
    {

        $query = 'select 
        post.id, post.title, post.stand_first, post.text, post.creation_date, post.last_update, post.file_attached, user.pseudo,
        post.userId
        from post INNER JOIN user ON post.userId = user.id';

        if ($orderBy !== null && array_keys($orderBy)[0] === "order") {
            $query = $query . " ORDER BY post.creation_date " . $orderBy['order'];
        }

        $query = $query . " LIMIT ${limit}";
        if ($offset !== null) {
            $query = $query . " OFFSET $offset";
        }
        $req = $this->pdo->prepare($query);

        $req->execute();
        $datas = $req->fetchAll(\PDO::FETCH_CLASS, Post::class);

        return  $datas === false ? null : $datas;
    }

    /**
     * @param Post $post
     */

    public function create(object $post): bool
    {
        $title = $post->getTitle();
        $standFirst = $post->getstand_first();
        $text = $post->getText();
        $userId = $post->getUserId();
        $file = $post->getFile_attached();

        $insert = "";
        $valueToInsert = "";

        $valuesToBind = [
            ":title" => $title,
            ":stand_first" => $standFirst,
            ":text" => $text,
            ":userId" => $userId
        ];

        if ($file !== null) {
            $valuesToBind["file_attached"] = $file;
            $insert = ", :file_attached";
            $valueToInsert = ", file_attached";
        }

        $req = $this->pdo->prepare("INSERT INTO post (title, stand_first, text, userId, last_update${valueToInsert}) 
        VALUES ( :title, :stand_first, :text, :userId, now()${insert})");
        foreach ($valuesToBind as $key => $val) {
            $req->bindValue($key, $val);
        }
        return $req->execute();
    }

    /**
     * @param Post $post
     */

    public function update(object $post): bool
    {
        $id = $post->getId();
        $title = $post->getTitle();
        $standFirst = $post->getStand_first();
        $text = $post->getText();
        $userId = $post->getUserId();
        $file = $post->getFile_attached();

        $insert = "";

        $valuesToBind = [
            ":title" => $title,
            ":stand_first" => $standFirst,
            ":text" => $text,
            ":userId" => $userId
        ];
        if ($file !== null) {
            $valuesToBind["file_attached"] = $file;
            $insert = ", file_attached = :file_attached";
        }

        $req = $this->pdo->prepare("UPDATE post SET title = :title, stand_first = :stand_first,
        text = :text, userId = :userId, last_update = now()${insert} WHERE id = ${id}");

        foreach ($valuesToBind as $key => $val) {
            $req->bindValue($key, $val);
        }
        return $req->execute();
    }

    /**
     * @param Post $post
     */

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
