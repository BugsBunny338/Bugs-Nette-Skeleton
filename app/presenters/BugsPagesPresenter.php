<?php

namespace App\Presenters;

use Nette,
	App\Model;

/**
 * Pages presenter for pages loaded entirely from database.
 */
abstract class BugsPagesPresenter extends BugsBasePresenter
{
	const PAGE_CONTENTS_COLUMN = 'contents';
	const PAGE_PRESENTER_COLUMN = 'presenter';

    protected function beforeRender() {
        parent::beforeRender();

        // TODO: if default.latte not found, use general default.latte
        // $this->setView($this->lang . "/" . $this->getView());
    }

	public function renderDefault()
	{
        if (!$this->user->isAllowed(\Authorizator::PAGES_RESOURCE, 'view'))
        {
            $this->flashMessage("K prohlížení této sekce nemáš oprávnění!", 'warning');
            $this->redirectHome();
        }

		$pages = $this->db->table(\Authorizator::PAGES_TABLE)->where(array(
				self::PAGE_PRESENTER_COLUMN => $this->getName(),
				self::LANG_COLUMN => $this->lang
			));

		if ($pages->count())
		{
			$this->template->page = $pages->order('date DESC')->fetch()->contents;
		}
		else
		{
			$this->template->page = '<p>Stránka není v tomto jazyce dostupná.</p>';
		}
	}

	protected function createComponentEditPageForm()
	{
		$form = new Nette\Application\UI\Form;
        $form->addProtection();
		$form->getElementPrototype()->id = 'editPageForm';

		$form->addHidden(self::PAGE_CONTENTS_COLUMN, ''); // javascript in layout.latte will get it
		$form->addHidden(self::PAGE_PRESENTER_COLUMN, $this->getName());
		$form->addHidden(self::LANG_COLUMN, $this->lang);

        $form->addSubmit('save', 'Uložit změny')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'editPageFormSubmittedSave');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zahodit změny')
            ->setValidationScope(FALSE)
            ->setAttribute('class', 'secondary small')
            ->onClick[] = callback($this, 'cancel');
        $form->addSubmit('restore', 'Vrátit zpět verzi stránky z: ')
            ->setValidationScope(FALSE)
            ->setAttribute('class', 'small')
            ->onClick[] = callback($this, 'editPageFormSubmittedRestore');

		$datesArray = array();
        foreach ($this->db->table(\Authorizator::PAGES_TABLE)->where(array(
				self::PAGE_PRESENTER_COLUMN => $this->getName(),
				self::LANG_COLUMN => $this->lang
			))->order('date DESC') as $row)
        {
            $datesArray[$row->id] = (string) $row->date . ' (' . (\MyString::truncate_html($row->contents, 40)) . ')';
        }
        $form->addSelect('date', '', $datesArray);
		return $form;

	}

	public function editPageFormSubmittedSave($submitButton)
	{
        if (!$this->user->isAllowed(\Authorizator::PAGES_RESOURCE, 'edit'))
        {
            $this->flashMessage("K úpravě stránek nemáš oprávnění!", 'warning');
            $this->redirectHome();
        }

        $values = $submitButton->getForm()->getValues();
        unset($values['date']);
        try
        {
            $this->db->table(\Authorizator::PAGES_TABLE)->insert($values);
            $this->flashMessage('Změny byly uloženy.', 'success');
        }
        catch(\PDOException $e)
        {
            $this->flashMessage($e->getMessage(), 'error');
        }
        $this->redirectHere();
	}

	public function editPageFormSubmittedRestore($submitButton)
	{
        if (!$this->user->isAllowed(\Authorizator::PAGES_RESOURCE, 'edit'))
        {
            $this->flashMessage("K úpravě stránek nemáš oprávnění!", 'warning');
            $this->redirectHome();
        }

        $id = $submitButton->getForm()->getValues()->date;
        $record = $this->db->table(\Authorizator::PAGES_TABLE)->get($id)->toArray();
        $date = $record['date'];
        unset($record['id']);
        unset($record['date']);
        try
        {
            $this->db->table(\Authorizator::PAGES_TABLE)->insert($record);
            $this->flashMessage('Vrácena verze z ' . $date . '.', 'success');
        }
        catch(\PDOException $e)
        {
            $this->flashMessage($e->getMessage(), 'error');
        }
        $this->redirectHere();
	}
}
