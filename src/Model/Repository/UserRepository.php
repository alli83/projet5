<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Service\Database;
use App\Model\Entity\User;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;
use PDO;

final class UserRepository implements EntityRepositoryInterface
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

    public function find(int $id): ?User
    {
        return null;
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?User
    {
        $req = $this->pdo->prepare('select * from user where email=:email');
        $req->setFetchMode(\PDO::FETCH_CLASS, 'App\\Model\\Entity\\User', [$criteria]);
        foreach ($criteria as $key => $param) {
            $req->bindValue($key, $param);
        }
        $req->execute();
        $data = $req->fetch();

        $user = $data === false ? null : $data;

        return $user;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        return null;
    }

    public function findAll(): ?array
    {
        return null;
    }

    public function create(object $user): bool
    {
        return false;
    }

    public function update(object $user): bool
    {
        return false;
    }

    public function delete(object $user): bool
    {
        return false;
    }
}
