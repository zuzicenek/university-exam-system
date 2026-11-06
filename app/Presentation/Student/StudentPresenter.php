<?php declare(strict_types=1);

namespace App\Presentation\Student;

use Nette;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;

final class StudentPresenter extends Presenter
{
    public function __construct(
        private Explorer $database,
    ) {
        parent::__construct();
    }

    /**
     * Kontrola přihlášení a role studenta.
     */
    protected function startup(): void
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:default');
        }

        if (!$this->getUser()->isInRole('student')) {
            $this->error('Nemáš oprávnění vstoupit do této části systému.', 403);
        }
    }

    /**
     * Načtení předmětů a dostupných termínů studenta.
     */
    public function renderDefault(): void
    {
        $kodStudenta = $this->getUser()->getId();

        // Předměty zapsané studentem.
        $this->template->predmety = $this->database
            ->table('studenti_predmety')
            ->where('kod_studenta', $kodStudenta)
            ->fetchAll();

        $zkratky = [];

        foreach ($this->template->predmety as $predmet) {
            $zkratky[] = $predmet->zkratka_predmetu;
        }

        $terminy = [];

        if ($zkratky !== []) {
            $radky = $this->database->table('vypsane_terminy')
                ->where('zkratka_predmetu', $zkratky)
                ->where('datum_cas > ?', new \DateTimeImmutable())
                ->order('datum_cas')
                ->fetchAll();

            foreach ($radky as $termin) {
                $pocetPrihlasenych = $this->database
                    ->table('zapsane_terminy')
                    ->where('id_terminu', $termin->id_terminu)
                    ->count('*');

                $mojePrihlaska = $this->database
                    ->table('zapsane_terminy')
                    ->where('id_terminu', $termin->id_terminu)
                    ->where('kod_studenta', $kodStudenta)
                    ->fetch();

                $terminy[] = (object) [
                    'id' => $termin->id_terminu,
                    'predmet' => $termin->zkratka_predmetu,
                    'datumCas' => $termin->datum_cas,
                    'mistnost' => $termin->zkratka_mistnosti,
                    'kapacita' => $termin->max_pocet_prihlasenych,
                    'poznamka' => $termin->poznamka,
                    'pocetPrihlasenych' => $pocetPrihlasenych,
                    'prihlasen' => $mojePrihlaska !== null,
                    'plno' => $pocetPrihlasenych >= $termin->max_pocet_prihlasenych,
                ];
            }
        }

        $this->template->terminy = $terminy;

        // Jméno přihlášeného studenta.
        $identity = $this->getUser()->getIdentity();

        $this->template->jmeno = $identity->name;
        $this->template->prijmeni = $identity->surname;
        $this->template->kod = $kodStudenta;
    }

    /**
     * Přihlášení studenta na termín.
     */
    public function handleSignUp(int $id): void
    {
        $kodStudenta = $this->getUser()->getId();

        $termin = $this->database->table('vypsane_terminy')
            ->where('id_terminu', $id)
            ->fetch();

        if (!$termin) {
            $this->flashMessage('Termín neexistuje.', 'danger');
            $this->redirect('default');
        }

        // Kontrola, zda má student daný předmět zapsaný.
        $predmet = $this->database->table('studenti_predmety')
            ->where('kod_studenta', $kodStudenta)
            ->where('zkratka_predmetu', $termin->zkratka_predmetu)
            ->fetch();

        if (!$predmet) {
            $this->flashMessage('Na tento předmět nemáš nárok.', 'danger');
            $this->redirect('default');
        }

        // Kontrola, zda termín není v minulosti.
        if ($termin->datum_cas <= new \DateTimeImmutable()) {
            $this->flashMessage('Na termín v minulosti se nelze přihlásit.', 'danger');
            $this->redirect('default');
        }

        // Kontrola, zda již student není přihlášen na stejný termín.
        $existujici = $this->database->table('zapsane_terminy')
            ->where('id_terminu', $id)
            ->where('kod_studenta', $kodStudenta)
            ->fetch();

        if ($existujici) {
            $this->flashMessage('Na tento termín už jsi přihlášen/a.', 'warning');
            $this->redirect('default');
        }

        // Kontrola, zda student nemá jiný termín stejného předmětu.
        $jinyTermin = $this->database->table('zapsane_terminy')
            ->where('kod_studenta', $kodStudenta)
            ->fetchAll();

        foreach ($jinyTermin as $zapis) {
            $jiny = $this->database->table('vypsane_terminy')
                ->get($zapis->id_terminu);

            if ($jiny && $jiny->zkratka_predmetu === $termin->zkratka_predmetu) {
                $this->flashMessage(
                    'Na jiný termín tohoto předmětu už jsi přihlášen/a.',
                    'warning'
                );
                $this->redirect('default');
            }
        }

        // Kontrola kapacity termínu.
        $pocet = $this->database->table('zapsane_terminy')
            ->where('id_terminu', $id)
            ->count('*');

        if ($pocet >= $termin->max_pocet_prihlasenych) {
            $this->flashMessage('Termín je již plně obsazen.', 'danger');
            $this->redirect('default');
        }

        // Zápis studenta na termín.
        $this->database->table('zapsane_terminy')->insert([
            'id_terminu' => $id,
            'kod_studenta' => $kodStudenta,
            'id_vysledku' => null,
        ]);

        $this->flashMessage('Úspěšně ses přihlásil/a na termín.', 'success');
        $this->redirect('default');
    }

    /**
     * Odhlášení studenta z termínu.
     */
    public function handleCancel(int $id): void
    {
        $kodStudenta = $this->getUser()->getId();

        $zapis = $this->database->table('zapsane_terminy')
            ->where('id_terminu', $id)
            ->where('kod_studenta', $kodStudenta)
            ->fetch();

        if (!$zapis) {
            $this->flashMessage('Přihlášku se nepodařilo najít.', 'danger');
            $this->redirect('default');
        }

        $termin = $this->database->table('vypsane_terminy')
            ->get($id);

        if (!$termin || $termin->datum_cas <= new \DateTimeImmutable())  {
            $this->flashMessage('Z termínu v minulosti se již nelze odhlásit.', 'warning');
            $this->redirect('default');
        }

        $zapis->delete();

        $this->flashMessage('Z termínu ses úspěšně odhlásil/a.', 'success');
        $this->redirect('default');
    }
    /**
 * Odhlášení studenta.
 */
    public function handleLogout(): void
    {
    $this->getUser()->logout(true);
    $this->flashMessage('Byl/a jsi úspěšně odhlášen/a.', 'success');
    $this->redirect('Sign:default');
    }

    /**
     * Cesta k šabloně.
     */
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