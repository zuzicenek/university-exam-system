<?php declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class UserRepository
{
    public function __construct(
        private Explorer $database,
    ) {
    }

    /**
     * Vyhledá uživatele podle přihlašovacího kódu.
     */
    public function findByCode(string $code): ?array
    {
        // Nejprve hledáme pedagoga.
        $user = $this->database->table('pedagogove')
            ->where('kod_pedagoga', $code)
            ->fetch();

        if ($user) {
            return [
                'code' => $user->kod_pedagoga,
                'name' => $user->jmeno,
                'surname' => $user->prijmeni,
                'passwordHash' => $user->heslo,
                'role' => 'pedagog',
            ];
        }

        // Pokud pedagog neexistuje, hledáme studenta.
        $user = $this->database->table('studenti')
            ->where('kod_studenta', $code)
            ->fetch();

        if ($user) {
            return [
                'code' => $user->kod_studenta,
                'name' => $user->jmeno,
                'surname' => $user->prijmeni,
                'passwordHash' => $user->heslo,
                'role' => 'student',
            ];
        }

        // Uživatel nebyl nalezen.
        return null;
    }
}