<?php declare(strict_types=1);

namespace App\Presentation\Sign;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Security\AuthenticationException;

final class SignPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private User $user,
    ) {
        parent::__construct();
    }

    protected function createComponentLoginForm(): Form
    {
        $form = new Form;

        $form->addText('code', 'Přihlašovací kód:')
            ->setRequired('Zadej svůj přihlašovací kód.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Zadej své heslo.');

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        return $form;
    }

    public function loginFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->user->login($values->code, $values->password);
$this->flashMessage('Úspěšně ses přihlásil/a.', 'success');

if ($this->user->isInRole('pedagog')) {
    $this->redirect('Pedagog:default');
} else {
    $this->redirect('Student:default');
}

        } catch (AuthenticationException) {
            $form->addError('Nesprávný přihlašovací kód nebo heslo.');
        }
    }

    public function formatTemplateFiles(): array
    {
        return [
            __DIR__ . '/templates/default.latte',
        ];
    }
    protected function beforeRender(): void
{
    parent::beforeRender();

    $this->setLayout(__DIR__ . '/../@layout.latte');
}
}