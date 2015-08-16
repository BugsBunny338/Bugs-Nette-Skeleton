<?php

namespace App\Presenters;

use Nette,
    App\Model,
    \Authorizator,
    Nette\Application\UI\Form;

define('FilesPresenter_UPLOAD_PATH', BugsBasePresenter::UPLOAD_FOLDER . '/files');

class FilesPresenter extends BugsBasePresenter
{
    const UPLOAD_PATH = FilesPresenter_UPLOAD_PATH;

    public function beforeRender()
    {
		// register helpers / filters
        $this->template->addFilter('brightOrDark', $this->brightOrDark);
		$this->template->addFilter('createLabel', $this->createLabel);
		$this->template->addFilter('createLabels', $this->createLabels);
		$this->template->addFilter('createFilesLabels', $this->createFilesLabels);
		$this->template->addFilter('linkFile', $this->linkFile);
    }

    public function renderDefault()
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'view'))
        {
            $this->flashMessage("K prohlížení této sekce nemáš oprávnění!", 'warning');
            $this->redirectHome();
        }

        $guest = $this->db->table(Authorizator::FILES_TABLE)->where(array(
            'group' => Authorizator::ROLE_GUEST,
            self::DELETED_COLUMN => FALSE
        ))->fetchAll();
        usort($guest, array($this, 'sortFilesByName'));
        $this->template->public = $guest;

        $registered = $this->db->table(Authorizator::FILES_TABLE)->where(array(
            'group' => Authorizator::ROLE_REGISTERED,
            self::DELETED_COLUMN => FALSE
        ));
        $this->template->registered = $registered;

        $private = $this->db->table(Authorizator::FILES_TABLE)->where(array(
            'owner' => $this->user->id,
            'group' => '',
            self::DELETED_COLUMN => FALSE
        ));
        $this->template->private = $private;

        $this->template->labels = $this->getLabels();
		$this->template->filesLabels = $this->getFilesLabels();

        $all = $this->db->table(Authorizator::FILES_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
        $this->template->all = $all;
    }

    public function renderManage()
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'manage'))
        {
            $this->flashMessage('Ke správě souborů nemáš oprávnění!', 'warning');
            $this->redirectHere();
        }

        $this->template->files = $this->db->table(Authorizator::FILES_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
		$this->template->filesLabels = $this->getFilesLabels();
        $this->template->users = $this->db->table(Authorizator::USERS_TABLE);
    }

    public function renderUpload()
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'manage'))
        {
            $this->flashMessage('K nahrávání souborů nemáš oprávnění!', 'warning');
            $this->redirectHere();
        }
    }

    protected function createComponentUploadFileForm()
    {
        $form = new Form;
        $form->addProtection();

        $form->addHidden('uploadedBy', $this->user->id);

        $users = $this->db->table(Authorizator::USERS_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
        $owners = array();

        foreach ($users as $user)
        {
            $owners[$user->id] = $user->surname . ', ' . $user->name . ' (' . $user->username . ')';
        }

        $form->addText('name', 'Název:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka názvu je %d znaků.', 30)
            ->setRequired();
        $form->addMultiSelect('labels', 'Štítky', array_map(function($record) {
            return $record->label;
        }, $this->getLabels()->fetchAll()));
        $form->addTextarea('description', 'Popis:', NULL, 3);
        $form->addUpload('file', 'Soubor:')
            ->setRequired();
        $form->addSelect('owner', 'Vlastník:', $owners)
            ->setDefaultValue($this->user->id)
            ->setRequired();
        $form->addSelect('group', 'Skupina:', array(
            NULL => '-',
            Authorizator::ROLE_GUEST => 'host',
            Authorizator::ROLE_REGISTERED => 'registrovaný'
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
        $labels = $values->labels;
        unset($values->labels);

        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'manage'))
        {
            $this->flashMessage("Ke správě souborů nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $file = $values->file;
        unset($values['file']);
        $values['extension'] = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));

        if ($file->error == UPLOAD_ERR_OK)
        {
            try
            {
                // upload and add file extension to $values
                $row = $this->db->table(Authorizator::FILES_TABLE)->insert($values);
                foreach ($labels as $labelId) {
                    $this->db->table(Authorizator::FILESLABELS_TABLE)->insert(array(
                        'file_id' => $row->id,
                        'label_id' => $labelId
                    ));
                }
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
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'editTheirOwn', $this->user->id, $id))
        {
            $this->flashMessage('K editaci tohoto souboru nemáte oprávnění!', 'warning');
            $this->redirectHere('manage');
        }

        $this->template->file = $this->db->table(Authorizator::FILES_TABLE)->get($id);
    }

    protected function createComponentEditFileForm()
    {
        $form = new Form;
        $form->addProtection();

        $id = $this->getParam('id');
        $file = $this->db->table(Authorizator::FILES_TABLE)->get($id);
        $users = $this->db->table(Authorizator::USERS_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
        $owners = array();

        foreach ($users as $user)
        {
            $owners[$user->id] = $user->surname . ', ' . $user->name . ' (' . $user->username . ')';
        }

        $form->addHidden('id', $file->id);

        $form->addText('name', 'Název:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka názvu je %d znaků.', 30)
            ->setRequired();
		$form->addMultiSelect('labels', 'Štítky', array_map(function($record) {
            return $record->label;
        }, $this->getLabels()->fetchAll()));
        $form->addText('extension', 'Přípona:')
            ->setDisabled();
        $form->addTextarea('description', 'Popis:', NULL, 3);
        $form->addSelect('owner', 'Vlastník:', $owners)
            ->setRequired();
        $form->addSelect('group', 'Skupina:', array(
            NULL => '-',
            Authorizator::ROLE_GUEST => 'host',
            Authorizator::ROLE_REGISTERED => 'registrovaný'
        ));

        $form->setDefaults($file);
        $filesLabels = $this->getFilesLabels();
        if (isset($filesLabels[$file->id])) {
            $form->setDefaults(array('labels' => $filesLabels[$file->id]));
        }

        $form->addSubmit('save', 'Uložit')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'editFileFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zrušit')
            ->setAttribute('class', 'alert small')
            ->setValidationScope(FALSE)
            // ->onClick[] = function() use ($presenter) { $presenter->redirectHere('manage'); };
            ->onClick[] = callback($this, 'cancel');

        return $form;
    }

    public function editFileFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        $labels = $values->labels;
        unset($values->labels);

        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'edit'))
        {
            $this->flashMessage("K úpravě souborů nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::FILES_TABLE)->get($values->id)->update($values);
            $this->db->table(Authorizator::FILESLABELS_TABLE)->where(array('file_id' => $values->id))->delete();
            foreach ($labels as $labelId) {
                $this->db->table(Authorizator::FILESLABELS_TABLE)->insert(array(
                    'file_id' => $values->id,
                    'label_id' => $labelId
                ));
            }
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
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'deleteTheirOwn', $this->user->id, $id))
        {
            $this->flashMessage('Ke smazání tohoto souboru nemáš oprávnění!', 'warning');
            $this->redirectHere('manage');
        }

        $file = $this->db->table(Authorizator::FILES_TABLE)->get($id);
        //@unlink(self::UPLOAD_PATH . '/' . $file->id . '.' . $file->extension); // instead of deleting, just set DELETED_COLUMN => TRUE
        $file->update(array(self::DELETED_COLUMN => TRUE));

        $this->redirectHere('manage');
    }

    public function actionGet($id)
    {
        $file = $this->db->table(Authorizator::FILES_TABLE)->get($id);
        $allowed = TRUE;

        // dump($this->user->authorizator); exit;
        // dump($this->user->roles); exit;

        if ($file->group !== Authorizator::ROLE_GUEST)
        {
            if ($file->group !== Authorizator::ROLE_REGISTERED)
            {
                if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'viewTheirOwn', $this->user->id, $file->id))
                {
                    $allowed = FALSE;
                }
            }
            else
            {
                if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'view'))
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
        else
        {
            $this->sendResponse(
                new Nette\Application\Responses\FileResponse(
                    self::UPLOAD_PATH . '/' . $file->id . '.' . $file->extension,
                    $file->name . '.' . $file->extension
                )
            );
        }
    }

    public function renderLabels()
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::LABELS_RESOURCE, 'manage'))
        {
            $this->flashMessage('Ke správě štítků nemáš oprávnění!', 'warning');
            $this->redirectHere();
        }

        $this->template->labels = $this->db->table(Authorizator::LABELS_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
        $labelUsage = array();
        foreach ($this->db->table(Authorizator::FILESLABELS_TABLE)->select('`label_id`, count(*)')->group('file_id') as $row) {
            $labelUsage[$row->label_id] = $row['count(*)'];
        }
        $this->template->labelUsage = $labelUsage;
    }

    protected function createComponentAddLabelForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->getElementPrototype()->id = 'addLabelForm';

        $form->addHidden('label', ''); // javascript in layout.latte will get it
        $form->addHidden('color', ''); // javascript in layout.latte will get it

        $form->addSubmit('save', 'Přidat')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'addLabelFormSubmitted');

        return $form;
    }

    public function addLabelFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'manage'))
        {
            $this->flashMessage("Ke správě štítků nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $this->db->table(Authorizator::LABELS_TABLE)->insert($values);
        $this->flashMessage('Štítek přidán!', 'success');
        $this->redirectHere('labels');
    }

    protected function createComponentUpdateLabelForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->getElementPrototype()->id = 'updateLabelForm';

        $form->addHidden('id', ''); // javascript in layout.latte will get it
        $form->addHidden('label', ''); // javascript in layout.latte will get it
        $form->addHidden('color', ''); // javascript in layout.latte will get it

        $form->addSubmit('save', 'Přidat')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'updateLabelFormSubmitted');

        return $form;
    }

    public function updateLabelFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FILES_RESOURCE, 'manage'))
        {
            $this->flashMessage("Ke správě štítků nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $this->db->table(Authorizator::LABELS_TABLE)->get($values->id)->update($values);
        $this->flashMessage('Štítek upraven!', 'success');
        $this->redirectHere('labels');
    }

    public function actionDeleteLabel($id)
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::LABELS_RESOURCE, 'delete'))
        {
            $this->flashMessage('Ke smazání labelu nemáš oprávnění!', 'warning');
            $this->redirectHere('labels');
        }

        $label = $this->db->table(Authorizator::LABELS_TABLE)->get($id);
        $label->update(array(self::DELETED_COLUMN => TRUE));

        $this->redirectHere('labels');
    }

    private function sortFilesByName($a, $b)
    {
        return strcmp($a->name, $b->name);
    }

    private function sortFilesByUploadedDate($a, $b)
    {
        return $a->uploaded > $b->uploaded;
    }

	private function getLabels() {
		return $this->db->table(Authorizator::LABELS_TABLE)->where(array(
            self::DELETED_COLUMN => FALSE
        ));
	}

	private function getFilesLabels() {
		$filesLabels = array();
		foreach ($this->db->table(Authorizator::FILESLABELS_TABLE) as $key => $value) {
            $explodedKey = explode('|', $key);
			$fileId = $explodedKey[0];
			$labelId = $explodedKey[1];
			if (!isset($filesLabels[$fileId])) {
				$filesLabels[$fileId] = array();
			}
			array_push($filesLabels[$fileId], $labelId);
		}
		return $filesLabels;
	}

    public function linkFile($config) {
        // TODO: use $this->link instead of files/get/<id>
        $filesLabels = $config[0];
        $file = $config[1];
        $labels = $this->createFilesLabels(array($filesLabels, $file));
        $link = "<a href=\"files/get/{$file->id}\">{$file->name}</a>";
        $desc = "<span>({$file->extension}, popis: {$file->description}, nahráno: {$file->uploaded->format('j. n. Y')})</span>";
        return implode(" ", array($labels, $link, $desc));
    }

	public function brightOrDark($color) {
		// if label color too dark, change font color from black (screen.css) to white
		$labelHexColor = substr($color, 1);
		$labelBrightness = 0.213 * hexdec(substr($labelHexColor, 0, 2)) +
			0.715 * hexdec(substr($labelHexColor, 2, 2)) +
			0.072 * hexdec(substr($labelHexColor, 4, 2));
		if ($labelBrightness < 0.5 * 255) {
			return '#ffffff';
		} else {
			return '#000000';
		}
	}

	public function createFilesLabels($config) {
		$filesLabels = $config[0];
		$file = $config[1];
        if (isset($filesLabels[$file->id])) {
    		return $this->createLabels($this->db->table(Authorizator::LABELS_TABLE)->where(array(
    			self::DELETED_COLUMN => FALSE
    		))->where("`id` IN (" . implode(", ", $filesLabels[$file->id]) . ")"));
        } else {
            return NULL;
        }
	}
	public function createLabels($labels) {
		return "<div class=\"labels\">" . implode(NULL, array_map($this->createLabel, $labels->fetchAll())) . "</div>";
	}

	public function createLabel($label) {
		return "<a href=\"#\" style=\"background-color: {$label->color}; color: " . $this->brightOrDark($label->color) . "\" data-label-id=\"{$label->id}\">{$label->label}</a>";
	}
}
