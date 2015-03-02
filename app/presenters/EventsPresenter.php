<?php

namespace App\Presenters;

use Nette,
	App\Model,
    \Authorizator,
    Nette\Application\UI\Form;


/**
 * Example of presenter that simply displays records from db.
 */
class EventsPresenter extends BugsBasePresenter
{
	public function renderDefault()
	{
        if (!$this->user->isAllowed(Authorizator::EVENTS_RESOURCE, 'view'))
        {
            $this->flashMessage('K prohlížení událostí nemáte oprávnění!', 'warning');
            $this->redirectToLogin($this->getName());
        }

		$this->template->events = $this->db->table(Authorizator::EVENTS_TABLE)->where(array(
            self::LANG_COLUMN => $this->lang,
			self::DELETED_COLUMN => FALSE
		));
	}

    protected function createComponentAddEventForm()
    {
        $form = new Form;
        $form->addProtection();

        $form->addHidden(self::LANG_COLUMN, $this->lang);
        $form->addHidden('insertedBy', $this->user->id);
        $form->addHidden('updatedBy', $this->user->id);

        $form->addText('heading', 'Nadpis:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka nadpisu je %d znaků.', 30)
            ->setRequired();
        $form->addText('date', 'Datum:')
            ->setDefaultValue(date('Y-m-d'))
            ->setType('date')
            ->setRequired();
        $form->addTextarea('text', 'Text:', NULL, 8)
            ->setAttribute('class', 'editable')
            ->setRequired();

        $form->addSubmit('save', 'Přidat')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'addEventFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zrušit')
            ->setAttribute('class', 'alert small')
            ->setValidationScope(FALSE)
            ->onClick[] = callback($this, 'cancel');

        return $form;
    }

    public function addEventFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->user->isAllowed(Authorizator::EVENTS_RESOURCE, 'add'))
        {
            $this->flashMessage("K přidání události nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::EVENTS_TABLE)->insert($values);
            $this->flashMessage('Událost přidána!', 'success');
            $this->redirectHere();
        }
        catch (PDOException $e)
        {
            $form->addError($e->getMessage());
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    public function renderEdit($id)
    {
        if (!$this->user->isAllowed(Authorizator::EVENTS_RESOURCE, 'editTheirOwn', $this->user->id, $id))
        {
            $this->flashMessage("K úpravě této události nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $this->template->event = $this->db->table(Authorizator::EVENTS_TABLE)->get($id);
    }

    protected function createComponentEditEventForm()
    {
        $form = new Form;
        $form->addProtection();

        $event = $this->db->table(Authorizator::EVENTS_TABLE)->get($this->getParam('id'));

        $form->addHidden('id', $event->id);
        $form->addHidden('updatedBy', $this->user->id);

        $form->addText('heading', 'Nadpis:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka nadpisu je %d znaků.', 30)
            ->setRequired();
        $form->addText('date', 'Datum:')
            ->setDefaultValue(date('Y-m-d'))
            ->setType('date')
            ->setRequired();
        $form->addTextarea('text', 'Text:', NULL, 8)
            ->setAttribute('class', 'editable')
            ->setRequired();

        $form->setDefaults($event);
        $form->setDefaults(array('date' => $event->date->format('Y-m-d')));

        $form->addSubmit('save', 'Uložit změny')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'editEventFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zahodit změny')
            ->setValidationScope(FALSE)
            ->setAttribute('class', 'secondary small')
            ->onClick[] = callback($this, 'cancel');

        return $form;

    }

    public function editEventFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->user->isAllowed(Authorizator::EVENTS_RESOURCE, 'editTheirOwn', $this->user->id, $values->id))
        {
            $this->flashMessage("K úpravě událostí nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::EVENTS_TABLE)->get($values->id)->update($values);
            $this->flashMessage('Změny byly uloženy.', 'success');
        }
        catch(\PDOException $e)
        {
            $this->flashMessage($e->getMessage(), 'error');
        }
        $this->redirectHere();
    }

    public function actionDelete($id)
    {
        if (!$this->user->isAllowed(Authorizator::EVENTS_RESOURCE, 'deleteTheirOwn', $this->user->id, $id))
        {
            $this->flashMessage("Ke smazání této události nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::EVENTS_TABLE)->get($id)->update(array(self::DELETED_COLUMN => TRUE));
            $this->flashMessage('Událost byla smazána!', 'success');
        }
        catch (\PDOException $e)
        {
            $this->flashMessage($e->getMessage(), 'error');
        }
        $this->redirectHere();
    }

}
