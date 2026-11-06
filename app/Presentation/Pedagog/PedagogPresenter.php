<?php declare(strict_types=1);

namespace App\Presentation\Pedagog;

use Nette;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;

final class PedagogPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Explorer $database,
    ) {
        parent::__construct();
    }

    /**
     * Kontrola přihlášení a role pedagoga.
     */
    protected function startup(): void
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:default');
        }

        if (!$this->getUser()->isInRole('pedagog')) {
            $this->error('Nemáš oprávnění vstoupit do této části systému.', 403);
        }
    }

    /**
     * Načtení údajů pro stránku pedagoga.
     */
    public function renderDefault(): void
    {
        $identity = $this->getUser()->getIdentity();
        $kodPedagoga = $identity->getId();

        $this->template->jmeno = $identity->name;
        $this->template->prijmeni = $identity->surname;
        $this->template->kod = $kodPedagoga;

        // Předměty přihlášeného pedagoga.
        $this->template->predmety = $this->database
            ->table('pedagogove_predmety')
            ->where('kod_pedagoga', $kodPedagoga)
            ->fetchAll();

        // Termíny přihlášeného pedagoga.
        $this->template->terminy = $this->database
            ->table('vypsane_terminy')
            ->where('kod_pedagoga', $kodPedagoga)
            ->order('datum_cas')
            ->fetchAll();

        // Informace o právě upravovaném termínu.
        $editId = $this->getParameter('id');

        $this->template->editMode = $editId !== null;
    }
    /**
 * Při úpravě termínu načteme stejné údaje jako na hlavní stránce.
 */
        public function renderEdit(int $id): void
    {
    $this->renderDefault();
    }

    /**
     * Otevření formuláře pro úpravu termínu.
     */
    public function actionEdit(int $id): void
    {
        $termin = $this->database->table('vypsane_terminy')
            ->where('id_terminu', $id)
            ->where('kod_pedagoga', $this->getUser()->getId())
            ->fetch();

        if (!$termin) {
            $this->error('Termín nebyl nalezen nebo k němu nemáš oprávnění.', 404);
        }

        if (\DateTimeImmutable::createFromInterface($termin->datum_cas) <= new \DateTimeImmutable()) {
            $this->flashMessage('Termín v minulosti již nelze upravovat.', 'warning');
            $this->redirect('default');
        }
    }

    /**
     * Formulář pro vytvoření a úpravu termínu.
     */
    protected function createComponentTermForm(): Form
    {
        $form = new Form;

        $kodPedagoga = $this->getUser()->getId();

        // Nabídka pouze předmětů přihlášeného pedagoga.
        $subjects = [];

        foreach ($this->database->table('pedagogove_predmety')
            ->where('kod_pedagoga', $kodPedagoga) as $assignment) {

            $subject = $this->database->table('predmety')
                ->get($assignment->zkratka_predmetu);

            if ($subject) {
                $subjects[$subject->zkratka_predmetu] = $subject->nazev;
            }
        }

        // Nabídka místností.
        $rooms = [];

        foreach ($this->database->table('mistnosti') as $room) {
            $rooms[$room->zkratka_mistnosti] =
                $room->popis . ' (kapacita ' . $room->kapacita . ')';
        }

        $form->addSelect('subject', 'Předmět:', $subjects)
            ->setRequired('Vyber předmět.');

        $form->addSelect('room', 'Místnost:', $rooms)
            ->setRequired('Vyber místnost.');

        $form->addText('dateTime', 'Datum a čas:')
            ->setHtmlType('datetime-local')
            ->setHtmlAttribute(
                'min',
                (new \DateTimeImmutable())->format('Y-m-d\TH:i')
            )
            ->setRequired('Zadej datum a čas.');

        $form->addText('capacity', 'Maximální počet studentů:')
            ->setHtmlType('number')
            ->setHtmlAttribute('min', 1)
            ->setHtmlAttribute('max', 32767)
            ->setRequired('Zadej maximální počet studentů.');

        $form->addText('note', 'Poznámka:');

        $editId = $this->getParameter('id');

        // Pokud upravujeme existující termín, načteme jeho údaje.
        if ($editId !== null) {
            $termin = $this->database->table('vypsane_terminy')
                ->where('id_terminu', (int) $editId)
                ->where('kod_pedagoga', $kodPedagoga)
                ->fetch();

            if (!$termin) {
                $this->error('Termín nebyl nalezen.', 404);
            }

            $form->setDefaults([
                'subject' => $termin->zkratka_predmetu,
                'room' => $termin->zkratka_mistnosti,
                'dateTime' => \DateTimeImmutable::createFromInterface($termin->datum_cas)
                ->format('Y-m-d\TH:i'),
                'capacity' => $termin->max_pocet_prihlasenych,
                'note' => $termin->poznamka,
            ]);

            $form->addSubmit('send', 'Uložit změny');
        } else {
            $form->addSubmit('send', 'Vypsat termín');
        }

        $form->onSuccess[] = [$this, 'termFormSucceeded'];

        return $form;
    }

    /**
     * Uložení nového nebo upraveného termínu.
     */
    public function termFormSucceeded(Form $form, \stdClass $values): void
    {
        $kodPedagoga = $this->getUser()->getId();
        $editId = $this->getParameter('id');

        // Ověření, že pedagog vyučuje vybraný předmět.
        $subject = $this->database->table('pedagogove_predmety')
            ->where('kod_pedagoga', $kodPedagoga)
            ->where('zkratka_predmetu', $values->subject)
            ->fetch();

        if (!$subject) {
            $form->addError('Tento předmět nemůžeš vypisovat.');
            return;
        }

        // Ověření data a času.
        $dateTime = \DateTimeImmutable::createFromFormat(
            '!Y-m-d\TH:i',
            $values->dateTime,
        );

        if (
            !$dateTime
            || $dateTime->format('Y-m-d\TH:i') !== $values->dateTime
            || $dateTime <= new \DateTimeImmutable()
        ) {
            $form->addError('Termín musí být nastaven na budoucí datum a čas.');
            return;
        }

        // Ověření kapacity.
        if (!ctype_digit((string) $values->capacity)) {
            $form->addError('Kapacita musí být celé kladné číslo.');
            return;
        }

        $capacity = (int) $values->capacity;

        if ($capacity < 1 || $capacity > 32767) {
            $form->addError('Kapacita musí být v rozsahu 1 až 32767.');
            return;
        }

        // Ověření místnosti a její kapacity.
        $room = $this->database->table('mistnosti')
            ->get($values->room);

        if (!$room || $capacity > $room->kapacita) {
            $form->addError('Počet studentů překračuje kapacitu místnosti.');
            return;
        }

        // Při úpravě ověříme vlastnictví termínu.
        if ($editId !== null) {
            $termin = $this->database->table('vypsane_terminy')
                ->where('id_terminu', (int) $editId)
                ->where('kod_pedagoga', $kodPedagoga)
                ->fetch();

            if (!$termin) {
                $form->addError('Termín nebyl nalezen nebo k němu nemáš oprávnění.');
                return;
            }

            // Nelze snížit kapacitu pod počet již přihlášených studentů.
            $pocetPrihlasenych = $this->database->table('zapsane_terminy')
                ->where('id_terminu', (int) $editId)
                ->count('*');

            if ($capacity < $pocetPrihlasenych) {
                $form->addError(
                    'Kapacita nemůže být nižší než počet již přihlášených studentů.'
                );
                return;
            }

            $termin->update([
                'zkratka_mistnosti' => $values->room,
                'zkratka_predmetu' => $values->subject,
                'datum_cas' => $dateTime->format('Y-m-d H:i:s'),
                'max_pocet_prihlasenych' => $capacity,
                'poznamka' => $values->note ?? '',
            ]);

            $this->flashMessage('Termín byl úspěšně upraven.', 'success');
        } else {
            // Vytvoření nového termínu.
            $this->database->table('vypsane_terminy')->insert([
                'zkratka_mistnosti' => $values->room,
                'kod_pedagoga' => $kodPedagoga,
                'zkratka_predmetu' => $values->subject,
                'datum_cas' => $dateTime->format('Y-m-d H:i:s'),
                'max_pocet_prihlasenych' => $capacity,
                'poznamka' => $values->note ?? '',
            ]);

            $this->flashMessage('Termín byl úspěšně vypsán.', 'success');
        }

        $this->redirect('Pedagog:default');
    }

    /**
     * Smazání termínu přihlášeného pedagoga.
     */
    public function handleDeleteTerm(int $id): void
    {
        $termin = $this->database->table('vypsane_terminy')
            ->where('id_terminu', $id)
            ->where('kod_pedagoga', $this->getUser()->getId())
            ->fetch();

        if (!$termin) {
            $this->flashMessage(
                'Termín nebyl nalezen nebo k němu nemáš oprávnění.',
                'danger'
            );
            $this->redirect('Pedagog:default');
        }

        $termin->delete();

        $this->flashMessage('Termín byl úspěšně smazán.', 'success');
        $this->redirect('Pedagog:default');
    }
    /**
 * Odhlášení pedagoga.
 */
    public function handleLogout(): void
    {
    $this->getUser()->logout(true);
    $this->flashMessage('Byl/a jsi úspěšně odhlášen/a.', 'success');
    $this->redirect('Sign:default');
    }
    /**
 * Kontrola, zda termín patří přihlášenému pedagogovi.
 */
private function findMyTerm(int $id): ?Nette\Database\Table\ActiveRow
{
    return $this->database->table('vypsane_terminy')
        ->where('id_terminu', $id)
        ->where('kod_pedagoga', $this->getUser()->getId())
        ->fetch();
}

/**
 * Zobrazení studentů přihlášených na termín.
 */
public function actionResults(int $id): void
{
    $termin = $this->findMyTerm($id);

    if (!$termin) {
        $this->flashMessage('Tento termín neexistuje nebo vám nepatří.', 'error');
        $this->redirect('default');
    }
}

/**
 * Načtení studentů a výsledků pro šablonu.
 */
public function renderResults(int $id): void
{
    $termin = $this->findMyTerm($id);

    if (!$termin) {
        $this->error('Termín nebyl nalezen.', 404);
    }

    $predmet = $this->database->table('predmety')
        ->get($termin->zkratka_predmetu);

    $studenti = [];

    $zapisy = $this->database->table('zapsane_terminy')
        ->where('id_terminu', $id)
        ->fetchAll();

    foreach ($zapisy as $zapis) {
        $student = $this->database->table('studenti')
            ->get($zapis->kod_studenta);

        if ($student) {
            $studenti[] = (object) [
                'kod' => $student->kod_studenta,
                'jmeno' => $student->jmeno,
                'prijmeni' => $student->prijmeni,
                'vysledek' => $zapis->id_vysledku,
            ];
        }
    }

    $this->template->termin = $termin;
    $this->template->predmet = $predmet;
    $this->template->studenti = $studenti;
}

/**
 * Formulář pro zadání výsledků studentům.
 */
protected function createComponentResultForm(): Form
{
    $form = new Form;

    $id = (int) $this->getParameter('id');
    $termin = $this->findMyTerm($id);

    if (!$termin) {
        $this->error('Termín nebyl nalezen.', 404);
    }

    if ($termin->datum_cas->format('Y-m-d H:i:s') > date('Y-m-d H:i:s')) {
    return $form;
}

    $predmet = $this->database->table('predmety')
        ->get($termin->zkratka_predmetu);

    $moznosti = [];

    foreach ($this->database->table('vysledky')
        ->where('typ', $predmet->ukonceni) as $vysledek) {
        $moznosti[$vysledek->id_vysledku] = $vysledek->popis;
    }

    $zapisy = $this->database->table('zapsane_terminy')
        ->where('id_terminu', $id)
        ->fetchAll();

    foreach ($zapisy as $zapis) {
        $student = $this->database->table('studenti')
            ->get($zapis->kod_studenta);

        if ($student) {
            $nazevPole = 'student_' . $student->kod_studenta;

            $form->addSelect(
                $nazevPole,
                $student->jmeno . ' ' . $student->prijmeni,
                $moznosti
            )
                ->setPrompt('Bez výsledku')
                ->setDefaultValue($zapis->id_vysledku);
        }
    }

    $form->addSubmit('save', 'Uložit výsledky');

    $form->onSuccess[] = [$this, 'resultFormSucceeded'];

    return $form;
}

/**
 * Uložení výsledků do databáze.
 */
public function resultFormSucceeded(Form $form, \stdClass $values): void
{
    $id = (int) $this->getParameter('id');
    $termin = $this->findMyTerm($id);

    if (!$termin) {
        $this->error('Termín nebyl nalezen.', 404);
    }

    if ($termin->datum_cas->format('Y-m-d H:i:s') > date('Y-m-d H:i:s')) {
        $form->addError('Výsledky lze zadat až po uskutečnění termínu.');
        return;
    }

    $zapisy = $this->database->table('zapsane_terminy')
        ->where('id_terminu', $id)
        ->fetchAll();

    foreach ($zapisy as $zapis) {
        $nazevPole = 'student_' . $zapis->kod_studenta;
        $idVysledku = $values->$nazevPole ?? null;

        $zapis->update([
            'id_vysledku' => $idVysledku ?: null,
        ]);
    }

    $this->flashMessage('Výsledky byly úspěšně uloženy.', 'success');
    $this->redirect('results', $id);
}

    /**
     * Cesta k šabloně stránky.
     */
    public function formatTemplateFiles(): array
{
    $view = $this->getView();

    if ($view === 'results') {
        return [
            __DIR__ . '/templates/results.latte',
        ];
    }

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