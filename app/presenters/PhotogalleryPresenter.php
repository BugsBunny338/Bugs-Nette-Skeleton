<?php

namespace App\Presenters;

use Nette,
	App\Model,
    \Authorizator,
    Nette\Application\UI\Form,
    Nette\Image;

define('PhotogalleryPresenter_UPLOAD_PATH', BugsBasePresenter::UPLOAD_FOLDER . '/photos');

class PhotogalleryPresenter extends BugsBasePresenter
{
	const UPLOAD_PATH = PhotogalleryPresenter_UPLOAD_PATH;
    const THUMB_SUFFIX = '_thumb';

    public function renderDefault()
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::ALBUMS_RESOURCE, 'view'))
        {
            $this->flashMessage("K prohlížení této sekce nemáš oprávnění!", 'warning');
            $this->redirectHome();
        }

        $albums = $this->db->table(Authorizator::ALBUMS_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
        $this->template->albums = $albums;

        $photos = array();
        foreach($albums as $album)
        {
            if ($album->photo !== NULL)
            {
                $photos[$album->id] = $this->db->table(Authorizator::PHOTOS_TABLE)->get($album->photo);
            }
        }
        $this->template->photos = $photos;
    }

    public function renderManageAlbums()
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::ALBUMS_RESOURCE, 'manage'))
        {
            $this->flashMessage("Ke správě alb nemáš oprávnění!", 'warning');
            $this->redirectHome();
        }

        $this->template->albums = $this->db->table(Authorizator::ALBUMS_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
        $this->template->photos = $this->db->table(Authorizator::PHOTOS_TABLE)->where(array(self::DELETED_COLUMN => FALSE));
        $this->template->users = $this->db->table(Authorizator::USERS_TABLE);
    }

    protected function createComponentAddAlbumForm()
    {
        $form = new Form;
        $form->addProtection();

        $form->addHidden('addedBy', $this->user->id);
        $form->addHidden('updatedBy', $this->user->id);
        $form->addHidden('added', NULL);

        $form->addText('title', 'Název alba:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka názvu je %d znaků.', 100)
            ->setRequired();

        $form->addSubmit('submit', 'Přidat')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'addAlbumFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zrušit')
            ->setAttribute('class', 'secondary small')
            ->setValidationScope(FALSE)
            ->onClick[] = callback($this, 'cancelToManageAlbums');

        return $form;
    }

    public function addAlbumFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        $values->added = NULL; // initializes to CURRENT_TIMESTAMP
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::ALBUMS_RESOURCE, 'add'))
        {
            $this->flashMessage("K přidání alba nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $row = $this->db->table(Authorizator::ALBUMS_TABLE)->insert($values);
        $this->flashMessage('Album přidáno!', 'success');
        $this->redirectHere('manageAlbums');
    }

    public function renderEditAlbum($id)
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::ALBUMS_RESOURCE, 'edit'))
        {
            $this->flashMessage('K úpravě tohoto alba nemáš oprávnění!', 'warning');
            $this->redirectHere('manageAlbums');
        }

        $this->template->album = $this->db->table(Authorizator::ALBUMS_TABLE)->get($id);
    }

    protected function createComponentEditAlbumForm()
    {
        $form = new Form;
        $form->addProtection();

        $id = $this->getParam('id');
        $album = $this->db->table(Authorizator::ALBUMS_TABLE)->get($id);

        $form->addHidden('id', $album->id);
        $form->addHidden('updatedBy', $this->user->id);

        $form->addText('title', 'Název alba:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka názvu je %d znaků.', 100)
            ->setRequired();

        $form->setDefaults($album);

        $form->addSubmit('submit', 'Uložit')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'editAlbumFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zrušit')
            ->setAttribute('class', 'secondary small')
            ->setValidationScope(FALSE)
            ->onClick[] = callback($this, 'cancelToManageAlbums');

        return $form;
    }

    public function editAlbumFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::ALBUMS_RESOURCE, 'edit'))
        {
            $this->flashMessage("K úpravě alba nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $this->db->table(Authorizator::ALBUMS_TABLE)->get($values->id)->update($values);
        $this->flashMessage('Album upravno!', 'success');
        $this->redirectHere('manageAlbums');
    }

    public function actionDeleteAlbum($id)
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::ALBUMS_RESOURCE, 'delete'))
        {
            $this->flashMessage('Ke smazání tohoto alba nemáš oprávnění!', 'warning');
            $this->redirectHere('manageAlbums');
        }

        $album = $this->db->table(Authorizator::ALBUMS_TABLE)->get($id);
        $album->update(array(self::DELETED_COLUMN => TRUE));

        $this->flashMessage('Album smazáno!', 'success');
        $this->redirectHere('manageAlbums');
    }

    /**************************************************************************/

    public function renderManagePhotos($id) // $id = $albumId
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::PHOTOS_RESOURCE, 'add'))
        {
            $this->flashMessage('K přidání fotografií nemáš oprávnění!', 'warning');
            $this->redirectHere('manageAlbums');
        }

        $album = $this->db->table(Authorizator::ALBUMS_TABLE)->get($id);
        $this->template->album = $album;
        $this->template->photos = $this->db->table(Authorizator::PHOTOS_TABLE)->where(array(
            'album' => $album->id,
            self::DELETED_COLUMN => FALSE
        ));
        $this->template->users = $this->db->table(Authorizator::USERS_TABLE);
    }

    public function renderUploadPhotos($id) // $id = $albumId
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::PHOTOS_RESOURCE, 'add'))
        {
            $this->flashMessage('K nahrávání fotografií nemáš oprávnění!', 'warning');
            $this->redirectHere();
        }

        $this->template->album = $this->db->table(Authorizator::ALBUMS_TABLE)->get($id);
    }

    protected function createComponentUploadPhotosForm()
    {
        $form = new Form;
        $form->addProtection();

        $id = $this->getParam('id');
        $album = $this->db->table(Authorizator::ALBUMS_TABLE)->get($id);

        $form->addHidden('uploadedBy', $this->user->id);
        $form->addHidden('album', $album->id);

        $form->addText('title', 'Společný titulek:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka titulku je %d znaků.', 100);
        $form->addUpload('photos', 'Fotografie:', TRUE)
            ->setRequired()
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, "Některý ze souborů nemá formát obrázku (jpg, png, ...). Nahrát lze pouze obrázky.");

        $form->addSubmit('submit', 'Nahrát')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'uploadPhotosFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zrušit')
            ->setAttribute('class', 'secondary small')
            ->setValidationScope(FALSE)
            ->onClick[] = array($this, 'cancelToManagePhotos');

        return $form;
    }

    public function uploadPhotosFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::PHOTOS_RESOURCE, 'add'))
        {
            $this->flashMessage("K nahrávání fotografií nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        $photos = $values->photos;

        foreach ($photos as $photo)
        {
            if ($photo->isOk() and $photo->isImage())
            {
                try
                {
                    $extension = strtolower(pathinfo($photo->name, PATHINFO_EXTENSION));
                    $row = $this->db->table(Authorizator::PHOTOS_TABLE)->insert(array(
                        'title' => $values->title,
                        'album' => $values->album,
                        'extension' => $extension,
                        'uploadedBy' => $values->uploadedBy
                    ));
                    $path = self::UPLOAD_PATH . '/' . $row->id . '.' . $extension;
                    $pathThumb = self::UPLOAD_PATH . '/' . $row->id . self::THUMB_SUFFIX . '.' . $extension;
                    $photo->move($path);

                    $image = Image::fromFile($path);
                    $image->resize(1024, 1024, Image::FIT | Image::SHRINK_ONLY);
                    $image->save($path);
                    $image->resize(500, 500, Image::FIT | Image::SHRINK_ONLY);
                    $image->save($pathThumb);

                    $this->flashMessage("Fotografie '$photo->name' nahrána!", 'success');
                }
                catch (PDOException $e)
                {
                    $this->flashMessage($e->getMessage(), 'error');
                }
            }
            else
            {
                $this->flashMessage("Nahrání fotografie '$photo->name' se nezdařilo. Zkuste to znovu nebo kontaktujte administrátora.", 'error');
            }
        }
        $this->redirectHere('managePhotos', $values->album);
    }

    public function renderEditPhoto($id)
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::PHOTOS_RESOURCE, 'edit'))
        {
            $this->flashMessage('K editaci této fotografie nemáte oprávnění!', 'warning');
            $this->redirectHere('manage');
        }

        $this->template->photo = $this->db->table(Authorizator::PHOTOS_TABLE)->get($id);
    }

    protected function createComponentEditPhotoForm()
    {
        $form = new Form;
        $form->addProtection();

        $id = $this->getParam('id');
        $photo = $this->db->table(Authorizator::PHOTOS_TABLE)->get($id);

        $form->addHidden('id', $photo->id);
        $form->addHidden('album', $photo->album); // good for cancel callback and redirect

        $form->addText('title', 'Titulek:')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka názvu je %d znaků.', 100);
        $form->addText('extension', 'Přípona:')
            ->setDisabled();

        $form->setDefaults($photo);

        $form->addSubmit('save', 'Uložit')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'editPhotoFormSubmitted');
        $presenter = $this;
        $form->addSubmit('cancel', 'Zrušit')
            ->setAttribute('class', 'alert small')
            ->setValidationScope(FALSE)
            ->onClick[] = callback($this, 'cancelToManagePhotos');

        return $form;
    }

    public function editPhotoFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();

        if (!$this->acl->isAllowed($this->user->roles, Authorizator::PHOTOS_RESOURCE, 'edit'))
        {
            $this->flashMessage("K úpravě této fotografie nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::PHOTOS_TABLE)->get($values->id)->update($values);
            $this->flashMessage('Fotografie upravena!', 'success');
            $this->redirectHere('managePhotos', $values->album);
        }
        catch (PDOException $e)
        {
            $form->addError($e->getMessage());
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    public function actionDeletePhoto($id)
    {
        $photo = $this->db->table(Authorizator::PHOTOS_TABLE)->get($id);

        if (!$this->acl->isAllowed($this->user->roles, Authorizator::PHOTOS_RESOURCE, 'delete'))
        {
            $this->flashMessage('Ke smazání této fotografie nemáš oprávnění!', 'warning');
            $this->redirectHere('managePhotos', $photo);
        }

        $photo->update(array(self::DELETED_COLUMN => TRUE));

        $this->flashMessage('Fotografie smazána!', 'success');
        $this->redirectHere('managePhotos', $photo->album);
    }

    /**************************************************************************/

    public function actionSetCoverPhoto($id)
    {
        $photo = $this->db->table(Authorizator::PHOTOS_TABLE)->get($id);

        $this->db->table(Authorizator::ALBUMS_TABLE)->get($photo->album)->update(array(
            'photo' => $photo->id
        ));

        $this->flashMessage('Fotografie byla nastavena jako titulní ke svému albu.', 'success');
        $this->redirectHere('managePhotos', $photo->album);
    }

    public function actionUnsetCoverPhoto($id)
    {
        $photo = $this->db->table(Authorizator::PHOTOS_TABLE)->get($id);

        $this->db->table(Authorizator::ALBUMS_TABLE)->get($photo->album)->update(array(
            'photo' => NULL
        ));

        $this->flashMessage('Fotografie již není titulní ke svému albu.', 'success');
        $this->redirectHere('managePhotos', $photo->album);
    }

    /**************************************************************************/

    public function cancelToManageAlbums(\Nette\Forms\Controls\SubmitButton $button)
    {
        $this->redirectHere('manageAlbums');
    }

    public function cancelToManagePhotos(\Nette\Forms\Controls\SubmitButton $button)
    {
        $this->redirectHere('managePhotos', $button->getForm()->getValues()->album);
    }

}
