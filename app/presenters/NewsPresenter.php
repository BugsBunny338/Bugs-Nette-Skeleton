<?php

namespace App\Presenters;

use Nette,
	App\Model,
    \Authorizator,
    Nette\Application\UI\Form,
    Nette\Image;

define('NewsPresenter_UPLOAD_PATH', BugsBasePresenter::UPLOAD_FOLDER . '/news');

class NewsPresenter extends BugsBasePresenter
{
    const UPLOAD_PATH = NewsPresenter_UPLOAD_PATH;

    public function beforeRender()
    {
        $this->template->uploadPath = self::UPLOAD_PATH;
    }

	public function renderDefault()
	{
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::NEWS_RESOURCE, 'view'))
        {
            $this->flashMessage('K prohlížení novinek nemáte oprávnění!', 'warning');
            $this->redirectToLogin($this->getName());
        }

		$this->template->news = $this->db->table(Authorizator::NEWS_TABLE)->where(array(
            self::LANG_COLUMN => $this->lang,
			self::DELETED_COLUMN => FALSE
		));
	}

    protected function createComponentAddNewForm()
    {
        $form = new Form;
        $form->addProtection();

        $form->addHidden(self::LANG_COLUMN, $this->lang);
        $form->addHidden('inserted', NULL); // initializes to CURRENT_TIMESTAMP
        $form->addHidden('insertedBy', $this->user->id);
        $form->addHidden('updatedBy', $this->user->id);

        $form->addText('title', 'Nadpis:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka nadpisu je %d znaků.', 100)
            ->setRequired();
        $form->addTextarea('text', 'Text:', NULL, 8)
            ->setAttribute('class', 'editable')
            ->setRequired();
        $form->addUpload('photo', 'Fotografie:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, "Nahrát lze pouze obrázek.");

        $form->addSubmit('save', 'Přidat')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'addNewFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zrušit')
            ->setAttribute('class', 'alert small')
            ->setValidationScope(FALSE)
            ->onClick[] = callback($this, 'cancel');

        return $form;
    }

    public function addNewFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        $values->inserted = NULL; // initializes to CURRENT_TIMESTAMP

        if (!$this->acl->isAllowed($this->user->roles, Authorizator::NEWS_RESOURCE, 'add'))
        {
            $this->flashMessage("K přidání novinky nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $photo = $values->photo;
        unset($values['photo']);

        try
        {
            $new = $this->db->table(Authorizator::NEWS_TABLE)->insert($values);

            if ($photo->getError() == UPLOAD_ERR_NO_FILE)
            {
                // no image, keep the old one (if any)
            }
            else if ($photo->isOk() and $photo->isImage())
            {
                $extension = strtolower(pathinfo($photo->name, PATHINFO_EXTENSION));
                $photoName = $new->id . '.' . $extension;
                $path = self::UPLOAD_PATH . '/' . $photoName;
                $photo->move($path);

                $image = Image::fromFile($path);
                $image->resize(400, 400, Image::FILL);
                $image->save($path);

                $new->update(array('photo' => $photoName));
            }
            else
            {
                $this->flashMessage('Chyba při nahrávání obrázku UPLOAD_ERR: ' . $photo->error, 'error');
            }

            $this->flashMessage('Novinka přidána!', 'success');
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
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::NEWS_RESOURCE, 'editTheirOwn', $this->user->id, $id))
        {
            $this->flashMessage("K úpravě této novinky nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $this->template->new = $this->db->table(Authorizator::NEWS_TABLE)->get($id);
    }

    protected function createComponentEditNewForm()
    {
        $form = new Form;
        $form->addProtection();

        $new = $this->db->table(Authorizator::NEWS_TABLE)->get($this->getParam('id'));

        $form->addHidden('id', $new->id);
        $form->addHidden('updatedBy', $this->user->id);

        $form->addText('title', 'Nadpis:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka nadpisu je %d znaků.', 100)
            ->setRequired();
        $form->addTextarea('text', 'Text:', NULL, 8)
            ->setAttribute('class', 'editable')
            ->setRequired();
        $form->addUpload('photo', 'Fotografie:')
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, "Nahrát lze pouze obrázek.");

        $form->setDefaults($new);

        $form->addSubmit('save', 'Uložit změny')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'editNewFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zahodit změny')
            ->setValidationScope(FALSE)
            ->setAttribute('class', 'secondary small')
            ->onClick[] = callback($this, 'cancel');

        return $form;

    }

    public function editNewFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();

        if (!$this->acl->isAllowed($this->user->roles, Authorizator::NEWS_RESOURCE, 'editTheirOwn', $this->user->id, $values->id))
        {
            $this->flashMessage("K úpravě novinek nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $new = $this->db->table(Authorizator::NEWS_TABLE)->get($values->id);
        $photo = $values['photo'];
        unset($values['photo']);

        if ($photo->getError() == UPLOAD_ERR_NO_FILE)
        {
            // no image, keep the old one (if any)
        }
        else if ($photo->isOk() and $photo->isImage())
        {
            $extension = strtolower(pathinfo($photo->name, PATHINFO_EXTENSION));
            $photoName = $new->id . '.' . $extension;
            $path = self::UPLOAD_PATH . '/' . $photoName;
            $photo->move($path);

            $image = Image::fromFile($path);
            $image->resize(400, 400, Image::FILL);
            $image->save($path);

            $new->update(array('photo' => $photoName));
        }
        else
        {
            $this->flashMessage('Chyba při nahrávání obrázku UPLOAD_ERR: ' . $photo->error, 'error');
        }

        try
        {
            $new->update($values);
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
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::NEWS_RESOURCE, 'deleteTheirOwn', $this->user->id, $id))
        {
            $this->flashMessage("Ke smazání této novinky nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::NEWS_TABLE)->get($id)->update(array(self::DELETED_COLUMN => TRUE));
            $this->flashMessage('Novinka byla smazána!', 'success');
        }
        catch (\PDOException $e)
        {
            $this->flashMessage($e->getMessage(), 'error');
        }
        $this->redirectHere();
    }

}
