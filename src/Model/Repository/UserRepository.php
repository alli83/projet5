<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\User;
use App\Service\Database;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;
use App\Service\ErrorsHandlers\Errors;
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
        $query = 'SELECT * FROM user WHERE 1 ';

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
        $req = $this->pdo->prepare($query);
        $req->setFetchMode(\PDO::FETCH_CLASS, User::class, [$criteria]);
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
        id, pseudo, role,  Created_date, last_update, pseudo
        from user WHERE role= "user" OR role= "admin"';
        $query = $query . " LIMIT 3";
        if ($offset !== null) {
            $query = $query . " OFFSET $offset";
        }
        $req = $this->pdo->prepare($query);
        $req->execute();
        $datas = $req->fetchAll(\PDO::FETCH_CLASS, User::class);
        return  $datas === false ? null : $datas;
    }

    public function create(object $user): bool
    {
        $req = $this->pdo->prepare('INSERT INTO user (pseudo, email, password) VALUES(:pseudo, :email, :password)');

        $req->bindValue("email", $user->getEmail());
        $req->bindValue("pseudo", $user->getPseudo());
        $req->bindValue("password", $user->getPassword());
        try {
            return $req->execute();
        } catch (\Exception $e) {
            $error = new Errors((int)$e->getCode());
            $error->handleErrors();
            return false;
        }
    }

    public function update(object $user): bool
    {
        $id = $user->getId();
        $role = $user->getRole();
        $valuesToBind = [":role" => $role];

        $req = $this->pdo->prepare("UPDATE user SET role = :role,
         last_update = now() WHERE id = ${id}");
        foreach ($valuesToBind as $key => $val) {
            $req->bindValue($key, $val);
        }
        return $req->execute();
    }

    public function delete(object $user): bool
    {
        $id = $user->getId();
        $req = $this->pdo->prepare("DELETE FROM user WHERE id = ${id}");
        if ($req->execute()) {
            return true;
        }
        return false;
    }
}
