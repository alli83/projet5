<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Model\Entity\User;
use App\Model\Repository\UserRepository;
use App\Service\Http\Session\Session;

class Auth
{
    private ?array $datas;
    private UserRepository $userRepo;
    private Session $session;

    public function __construct(?array $datas, UserRepository $userRepo, Session $session)
    {
        $this->datas = $datas;
        $this->userRepo = $userRepo;
        $this->session = $session;
    }

    public function getDatas(): ?array
    {
        return $this->datas;
    }

    public function isValidLoginForm(): bool
    {
        $infoUser = $this->getDatas();
        if ($infoUser == null) {
            return false;
        }

        if ($infoUser["email"] === "" || $infoUser["password"] === "") {
            return false;
        }
        $email = htmlspecialchars($infoUser['email']);
        $password = htmlspecialchars($infoUser['password']);

        $user = $this->userRepo->findOneBy(['email' => $email]);
        if ($user !== null) {
            password_verify($password, $user->getPassword());
            if (!password_verify($password, $user->getPassword())) {
                return false;
            }
            $this->session->set('user', $user); // remove password
            return true;
        } else {
            return false;
        }
    }

    public function register(): bool
    {
        $userDatas = $this->getDatas();
        $password = "";
        if (array_key_exists("password", $userDatas)) {
            $password = password_hash($userDatas['password'], PASSWORD_DEFAULT); // to change if more security is needed 12
        } else {
            return false;
        }
        if (!empty($userDatas['email']) && !empty($userDatas['pseudo'])) {
            $params = ['email', 'password', 'pseudo'];
            $values = [htmlspecialchars($userDatas['email']), $password, htmlspecialchars($userDatas['pseudo'])];
            $param = array_combine($params, $values);

            $object = new User($param);

            return $this->userRepo->create($object);
        } else {
            return false;
        }
    }
}
