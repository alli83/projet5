<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Model\Entity\User;
use App\Model\Repository\UserRepository;
use App\Service\Http\Session\Session;

class Auth
{
    private array $datas;
    private UserRepository $userRepo;
    private Session $session;

    public function __construct(array $datas, UserRepository $userRepo, Session $session)
    {
        $this->datas = $datas;
        $this->userRepo = $userRepo;
        $this->session = $session;
    }

    public function getDatas(): array
    {
        return $this->datas;
    }

    public function isValidLoginForm(): bool
    {
        $infoUser = $this->getDatas();
        if ($infoUser == [] || $infoUser["email"] === "" || $infoUser["password"] === "") {
            return false;
        }

        $email = htmlspecialchars($infoUser['email']);
        $password = htmlspecialchars($infoUser['password']);

        $user = $this->userRepo->findOneBy(['email' => $email]);
        if ($user !== null) {
            if (password_verify($password, $user->getPassword())) {
                $this->session->set('pseudo', $user->getPseudo());
                $this->session->set('role', $user->getRole());
                $this->session->set('email', $user->getEmail());
                return true;
            }
        }
        return false;
    }

    public function register(): bool
    {
        $userDatas = $this->getDatas();
        $password = "";

        if (!empty($userDatas['email']) && !empty($userDatas['pseudo'])) {
            $params = ['email', 'password', 'pseudo'];

            if (filter_var($userDatas['email'], FILTER_VALIDATE_EMAIL)) {
                $email = filter_var($userDatas['email'], FILTER_SANITIZE_EMAIL);

                if ($this->userRepo->findOneBy(["email" => $email])) {
                    $this->session->addFlashes(
                        "warning",
                        "un compte a déjà été crée avec cette adresse email"
                    );
                    return false;
                }
                if (!preg_match('/^\w{3,15}$/', $userDatas['pseudo'])) {
                    $this->session->addFlashes(
                        "warning",
                        "Votre pseudo doit comporter entre 3 et 15 caractères (chiffres ou lettres"
                    );
                    return false;
                }
                if (!preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@%^&*-]).{8,}$/', $userDatas['password'])) {
                    $this->session->addFlashes(
                        "warning",
                        "Votre mot de passe doit comporter 8 caractères, au moins un chiffre et un caractère spécial #?!@%^&*-"
                    );
                    return false;
                }
                $pseudo = $userDatas["pseudo"];

                if (array_key_exists("password", $userDatas) && $userDatas["password"] !== "") {
                    $password = password_hash($userDatas['password'], PASSWORD_DEFAULT);

                    $values = [$email, $password, $pseudo];
                    $param = array_combine($params, $values);
                    $object = new User($param);

                    return $this->userRepo->create($object);
                }
            }
            $this->session->addFlashes("warning", "Veuillez saisir un email valide");
        }
        return false;
    }
}
