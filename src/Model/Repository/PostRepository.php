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
        $req = $this->pdo->prepare('select * from post where id=:id');
        $req->setFetchMode(\PDO::FETCH_CLASS, 'App\\Model\\Entity\\Post', [$criteria]);
        foreach ($criteria as $key => $param) {
            $req->bindValue($key, $param);
        }
        $req->execute();
        $datas = $req->fetch();

        return $datas == false ? null : $datas;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        return null;
    }

    public function findAll(): ?array
    {
        $req = $this->pdo->prepare('select * from post');

        $req->execute();
        $datas = $req->fetchAll(\PDO::FETCH_CLASS, 'App\\Model\\Entity\\Post');

        if ($datas === []) {
            return null;
        }
        return $datas;
    }

    public function create(object $post): bool
    {
        return false;
    }

    public function update(object $post): bool
    {
        return false;
    }

    public function delete(object $post): bool
    {
        return false;
    }
}
