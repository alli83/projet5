<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Model\Entity\User;
use App\Model\Repository\UserRepository;
use App\Service\Http\Session\Session;

class AuthService
{

    public function isValidLoginForm(Session $session, array $datas, UserRepository $userRepo): bool
    {

        if ($datas == [] || $datas["emailLogin"] === "" || $datas["passwordLogin"] === "") {
            return false;
        }

        $validToken = (new TokenService())->validateToken($datas, $session);

        if ($validToken) {
            $email = htmlspecialchars($datas['emailLogin']);
            $password = htmlspecialchars($datas['passwordLogin']);

            $user = $userRepo->findOneBy(['email' => $email]);
            if ($user !== null) {
                if (password_verify($password, $user->getPassword())) {
                    $session->set('pseudo', $user->getPseudo());
                    $session->set('role', $user->getRole());
                    $session->set('email', $user->getEmail());
                    return true;
                }
            }
            return false;
        }
        $session->addFlashes("danger", "une erreur est survenue");
        return false;
    }

    public function register(Session $session, array $datas, UserRepository $userRepo): bool
    {
        $password = "";

        if (!empty($datas['emailSignup']) && !empty($datas['pseudoSignup'])) {
            $params = ['email', 'password', 'pseudo'];
            $email = $datas['emailSignup'];

            if ($userRepo->findOneBy(["email" => $email])) {
                $session->addFlashes(
                    "warning",
                    "un compte a déjà été crée avec cette adresse email"
                );
                return false;
            }
            if (!preg_match('/^\w{1,20}$/', $datas['pseudoSignup'])) {
                $session->addFlashes(
                    "warning",
                    "Votre pseudo doit comporter entre 1 et 20 caractères (chiffres ou lettres"
                );
                return false;
            }
            if (!preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@%^&*-]).{8,}$/', $datas['password'])) {
                $session->addFlashes(
                    "warning",
                    "Votre mot de passe doit comporter 8 caractères, au moins un chiffre et un caractère spécial #?!@%^&*-"
                );
                return false;
            }
            if ($datas['password'] !== $datas['passwordConfirm']) {
                $session->addFlashes(
                    "warning",
                    "Vos mots de passe ne correspondent pas"
                );
                return false;
            }
            $pseudo = $datas["pseudoSignup"];

            $password = password_hash($datas['password'], PASSWORD_DEFAULT);

            $values = [$email, $password, $pseudo];
            $param = array_combine($params, $values);
            $object = new User($param);

            return $userRepo->create($object);
        }
        return false;
    }
}
