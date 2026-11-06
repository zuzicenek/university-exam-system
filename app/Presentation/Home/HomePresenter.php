<?php declare(strict_types=1);

namespace App\Presentation\Home;

use Nette\Application\UI\Presenter;

final class HomePresenter extends Presenter
{
    protected function startup(): void
    {
        parent::startup();

        // Nepřihlášený uživatel jde na přihlášení.
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:default');
        }

        // Pedagog jde do své části systému.
        if ($this->getUser()->isInRole('pedagog')) {
            $this->redirect('Pedagog:default');
        }

        // Student jde do své části systému.
        if ($this->getUser()->isInRole('student')) {
            $this->redirect('Student:default');
        }

        // Neznámá role – odhlášení a návrat na přihlášení.
        $this->getUser()->logout(true);
        $this->redirect('Sign:default');
    }
}