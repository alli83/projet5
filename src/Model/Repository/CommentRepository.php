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
        $req = $this->pdo->prepare('select * from comment where idPost=:idPost AND status = "validated"');
        $req->setFetchMode(\PDO::FETCH_CLASS, 'App\\Model\\Entity\\Comment', [$criteria]);
        foreach ($criteria as $key => $param) {
            if (!is_int($param)) {
                // TO DO ERROR
            }
            $req->bindValue($key, $param);
        }
        // TO DO ERROR
        $req->execute();
        $datas = $req->FetchAll();
        return  $datas === false ? null : $datas;
    }

    public function findAll(int $limit = null, int $offset = null, array $orderBy = null): ?array
    {
        return null;
    }

    public function create(object $comment): bool
    {
        return false;
    }

    public function update(object $comment): bool
    {
        return false;
    }

    public function delete(object $comment): bool
    {
        return false;
    }
}
