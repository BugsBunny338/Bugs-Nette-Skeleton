<?php

namespace App\Presenters;

use Nette,
	App\Model;


class FilesPresenter extends BugsBasePresenter
{
	const UPLOAD_PATH = 'upload/files';

	public function renderDefault()
	{
        if (!$this->user->isAllowed(\Authorizator::FILES_RESOURCE, 'view'))
        {
            $this->flashMessage("K prohlížení této sekce nemáš oprávnění!", 'warning');
            $this->redirectHome();
        }

		$guest = $this->db->table(\Authorizator::FILES_TABLE)->where(array(
            'group' => \Authorizator::ROLE_GUEST,
            self::DELETED_COLUMN => FALSE
        ))->fetchAll();
		usort($guest, array($this, 'sortFilesByName'));
		$this->template->public = $guest;

		$registered = $this->db->table(\Authorizator::FILES_TABLE)->where(array(
			'group' => \Authorizator::ROLE_REGISTERED,
            self::DELETED_COLUMN => FALSE
		));
		$this->template->registered = $registered;

		$private = $this->db->table(\Authorizator::FILES_TABLE)->where(array(
			'owner' => $this->user->id,
			'group' => '',
            self::DELETED_COLUMN => FALSE
		));
		$this->template->private = $private;

		$all = $this->db->table(\Authorizator::FILES_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
		$this->template->all = $all;

		$this->template->uploadPath = self::UPLOAD_PATH;
	}

	public function renderManage()
	{
		if (!$this->user->isAllowed(\Authorizator::FILES_RESOURCE, 'manage'))
		{
			$this->flashMessage('Ke správě souborů nemáš oprávnění!', 'warning');
			$this->redirectHome();
		}

		$this->template->files = $this->db->table(\Authorizator::FILES_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
		$this->template->users = $this->db->table(\Authorizator::USERS_TABLE);
	}

    protected function createComponentUploadFileForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->addProtection();

        $form->addHidden('uploadedBy', $this->user->id);

        $users = $this->db->table(\Authorizator::USERS_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
        $owners = array();

        foreach ($users as $user)
        {
        	$owners[$user->id] = $user->surname . ', ' . $user->name . ' (' . $user->username . ')';
        }

        $form->addText('name', 'Název:')
            ->addRule(Nette\Application\UI\Form::MAX_LENGTH, 'Maximální délka názvu je %d znaků.', 30)
            ->setRequired();
        $form->addTextarea('description', 'Popis:', NULL, 3);
        $form->addUpload('file', 'Soubor:')
        	->setRequired();
        $form->addSelect('owner', 'Vlastník:', $owners)
        	->setDefaultValue($this->user->id)
        	->setRequired();
    	$form->addSelect('group', 'Skupina:', array(
    		NULL => '-',
    		\Authorizator::ROLE_GUEST => 'host',
    		\Authorizator::ROLE_REGISTERED => 'registrovaný'
		));

        $form->addSubmit('submit', 'Nahrát')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'uploadFileFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zrušit')
            ->setAttribute('class', 'secondary small')
            ->setValidationScope(FALSE)
            ->onClick[] = callback($this, 'cancel');

        return $form;
    }

    public function uploadFileFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->user->isAllowed(\Authorizator::FILES_RESOURCE, 'manage'))
        {
            $this->flashMessage("Ke správě souborů nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $file = $values->file;
        unset($values['file']);
        $values['extension'] = pathinfo($file->name, PATHINFO_EXTENSION);

        if ($file->error == 0)
        {
	        try
	        {
	        	// upload and add extension to $values
	            $row = $this->db->table(\Authorizator::FILES_TABLE)->insert($values);
	        	$file->move(self::UPLOAD_PATH . '/' . $row->id . '.' . $values->extension);
	            $this->flashMessage('Soubor nahrán!', 'success');
	            $this->redirectHere('manage');
	        }
	        catch (PDOException $e)
	        {
	            $form->addError($e->getMessage());
	            $this->flashMessage($e->getMessage(), 'error');
	        }
        }
        else
        {
        	$this->flashMessage('Upload se nezdařil. Zkuste to znovu nebo kontaktujte administrátora.', 'error');
        	$this->redirectHere('upload');
        }
    }

	public function renderEdit($id)
	{
		if (!$this->user->isAllowed(\Authorizator::FILES_RESOURCE, 'editTheirOwn', $this->user->id, $id))
		{
			$this->flashMessage('K editaci tohoto souboru nemáte oprávnění!', 'warning');
			$this->redirectHere('manage');
		}

		$this->template->file = $this->db->table(\Authorizator::FILES_TABLE)->get($id);
	}

    protected function createComponentEditFileForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->addProtection();

        $id = $this->getParam('id');
        $file = $this->db->table(\Authorizator::FILES_TABLE)->get($id);
        $users = $this->db->table(\Authorizator::USERS_TABLE)->fetchAll();
        $owners = array();

        foreach ($users as $user)
        {
        	$owners[$user->id] = $user->surname . ', ' . $user->name . ' (' . $user->username . ')';
        }

        $form->addHidden('id', $id);
        $form->addText('name', 'Název:')
            ->addRule(Nette\Application\UI\Form::MAX_LENGTH, 'Maximální délka názvu je %d znaků.', 30)
            ->setRequired();
        $form->addText('extension', 'Přípona:')
            ->setDisabled();
        $form->addTextarea('description', 'Popis:', NULL, 3);
        $form->addSelect('owner', 'Vlastník:', $owners)
        	->setRequired();
    	$form->addSelect('group', 'Skupina:', array(
    		NULL => '-',
    		\Authorizator::ROLE_GUEST => 'host',
    		\Authorizator::ROLE_REGISTERED => 'registrovaný'
		));

        $form->setDefaults($file);

        $form->addSubmit('save', 'Uložit')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'editFileFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zrušit')
            ->setAttribute('class', 'alert small')
            ->setValidationScope(FALSE)
            ->onClick[] = function() use ($presenter) { $presenter->redirectHere('manage'); };

        return $form;
    }

    public function editFileFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->user->isAllowed(\Authorizator::FILES_RESOURCE, 'edit'))
        {
            $this->flashMessage("K úpravě souborů nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(\Authorizator::FILES_TABLE)->get($values->id)->update($values);
            $this->flashMessage('Soubor upraven!', 'success');
            $this->redirectHere('manage');
        }
        catch (PDOException $e)
        {
            $form->addError($e->getMessage());
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    public function actionDelete($id)
    {
    	if (!$this->user->isAllowed(\Authorizator::FILES_RESOURCE, 'deleteTheirOwn', $this->user->id, $id))
    	{
    		$this->flashMessage('Ke smazání tohoto souboru nemáš oprávnění!', 'warning');
    		$this->redirectHere('manage');
    	}

    	$file = $this->db->table(\Authorizator::FILES_TABLE)->get($id);
    	//@unlink(self::UPLOAD_PATH . '/' . $file->id . '.' . $file->extension); // instead of deleting, just set DELETED_COLUMN => TRUE
    	$file->update(array(self::DELETED_COLUMN => TRUE));

    	$this->redirectHere('manage');
    }

	public function actionGet($id)
	{
        $file = $this->db->table(\Authorizator::FILES_TABLE)->get($id);
        $allowed = TRUE;

        if ($file->group !== \Authorizator::ROLE_GUEST)
        {
            if ($file->group !== \Authorizator::ROLE_REGISTERED)
            {
        		if (!$this->user->isAllowed(\Authorizator::FILES_RESOURCE, 'viewTheirOwn', $this->user->id, $file->id))
        		{
                    $allowed = FALSE;
        		}
            }
            else
            {
                if (!$this->user->isAllowed(\Authorizator::FILES_RESOURCE, 'view'))
                {
                    $allowed = FALSE;
                }
            }
        }

        if (!$allowed)
        {
            $this->flashMessage('Ke čtení tohoto souboru nemáte oprávnění!', 'warning');
            $this->redirectHere();
        }

		$this->sendResponse(new Nette\Application\Responses\FileResponse(self::UPLOAD_PATH . '/' . $file->id . '.' . $file->extension, $file->name . '.' . $file->extension));
	}

	private function sortFilesByName($a, $b)
	{
	    return strcmp($a->name, $b->name);
	}

	private function sortFilesByUploadedDate($a, $b)
	{
	    return $a->uploaded > $b->uploaded;
	}
}
