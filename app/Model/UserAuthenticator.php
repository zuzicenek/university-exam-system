<?php declare(strict_types=1);

namespace App\Model;

use Nette\Security\Authenticator;
use Nette\Security\SimpleIdentity;
use Nette\Security\AuthenticationException;

final class UserAuthenticator implements Authenticator
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        // Vyhledáme uživatele podle přihlašovacího kódu.
        $user = $this->userRepository->findByCode($username);

        // Kontrola, zda uživatel existuje.
        if ($user === null) {
            throw new AuthenticationException('Nesprávný přihlašovací kód nebo heslo.');
        }

        // Ověření hesla proti uloženému hashi.
        if (!password_verify($password, $user['passwordHash'])) {
            throw new AuthenticationException('Nesprávný přihlašovací kód nebo heslo.');
        }

        // Vytvoření identity přihlášeného uživatele.
        return new SimpleIdentity(
            $user['code'],
            $user['role'],
            [
                'name' => $user['name'],
                'surname' => $user['surname'],
            ],
        );
    }
}