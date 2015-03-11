<?php

namespace App\Presenters;

use Nette,
	App\Model,
    \Authorizator,
    Nette\Application\UI\Form;


class ForumPresenter extends BugsBasePresenter
{

	public function renderDefault()
	{
		$posts = $this->db->table(Authorizator::FORUM_TABLE)->where(array(
            'parent' => NULL,
            self::DELETED_COLUMN => FALSE
        ))->order('inserted DESC');

		$comments = array();
		foreach ($posts as $post)
		{
			$comments[$post->id] = $this->db->table(Authorizator::FORUM_TABLE)->where(array(
                'parent' => $post->id,
                self::DELETED_COLUMN => FALSE
            ));
		}

		$this->template->posts = $posts;
		$this->template->comments = $comments;
		$this->template->users = $this->db->table(Authorizator::USERS_TABLE)->fetchAll();
	}

    protected function createComponentAddPostForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->getElementPrototype()->id = 'addPostForm';

        $form->addHidden('inserted', NULL);
        $form->addHidden('insertedBy', $this->user->id);
        $form->addHidden('updatedBy', $this->user->id);
        $form->addHidden('text', ''); // javascript in layout.latte will get it

        $form->addSubmit('save', 'Přidat')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'addPostFormSubmitted');

        return $form;
    }

    public function addPostFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        $values->inserted = NULL; // initializes to CURRENT_TIMESTAMP
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FORUM_RESOURCE, 'add'))
        {
            $this->flashMessage("K přidání příspěvku nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::FORUM_TABLE)->insert($values);
            $this->flashMessage('Příspěvek přidán!', 'success');
            $this->redirectHere();
        }
        catch (PDOException $e)
        {
            $form->addError($e->getMessage());
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    protected function createComponentEditPostForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->getElementPrototype()->id = 'editPostForm';

        $post = $this->db->table(Authorizator::FORUM_TABLE)->get($this->getParam('id'));

        $form->addHidden('id', ''); // javascript in layout.latte will get it
        $form->addHidden('updatedBy', $this->user->id);
        $form->addHidden('text', ''); // javascript in layout.latte will get it

        $form->addSubmit('save', 'Uložit')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'editPostFormSubmitted');

        return $form;
    }

    public function editPostFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FORUM_RESOURCE, 'editTheirOwn', $this->user->id, $values->id))
        {
            $this->flashMessage("K úpravě tohoto příspěvku nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::FORUM_TABLE)->get($values->id)->update($values);
            $this->flashMessage('Příspěvek upraven!', 'success');
            $this->redirectHere();
        }
        catch (PDOException $e)
        {
            $form->addError($e->getMessage());
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    protected function createComponentAddCommentForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->getElementPrototype()->id = 'addCommentForm';

        $form->addHidden('insertedBy', $this->user->id);
        $form->addHidden('updatedBy', $this->user->id);
        $form->addHidden('text', ''); // javascript in layout.latte will get it
        $form->addHidden('parent', ''); // javascript in layout.latte will get it

        $form->addSubmit('save', 'Přidat')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'addCommentFormSubmitted');

        return $form;
    }

    public function addCommentFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FORUM_RESOURCE, 'add'))
        {
            $this->flashMessage("K přidání komentáře nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::FORUM_TABLE)->insert($values);
            $this->flashMessage('Komentář přidán!', 'success');
            $this->redirectHere();
        }
        catch (PDOException $e)
        {
            $form->addError($e->getMessage());
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    protected function createComponentEditCommentForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->getElementPrototype()->id = 'editCommentForm';

        $post = $this->db->table(Authorizator::FORUM_TABLE)->get($this->getParam('id'));

        $form->addHidden('id', ''); // javascript in layout.latte will get it
        $form->addHidden('updatedBy', $this->user->id);
        $form->addHidden('text', ''); // javascript in layout.latte will get it

        $form->addSubmit('save', 'Uložit')
            ->setAttribute('class', 'success small')
            ->onClick[] = callback($this, 'editCommentFormSubmitted');

        return $form;
    }

    public function editCommentFormSubmitted($submitButton)
    {
        $values = $submitButton->getForm()->getValues();
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FORUM_RESOURCE, 'editTheirOwn', $this->user->id, $values->id))
        {
            $this->flashMessage("K úpravě tohoto komentáře nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::FORUM_TABLE)->get($values->id)->update($values);
            $this->flashMessage('Komentář upraven!', 'success');
            $this->redirectHere();
        }
        catch (PDOException $e)
        {
            $form->addError($e->getMessage());
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    public function actionDelete($id)
    {
        if (!$this->acl->isAllowed($this->user->roles, Authorizator::FORUM_RESOURCE, 'deleteTheirOwn', $this->user->id, $id))
        {
            $this->flashMessage("Ke smazání tohoto příspěvku nemáš oprávnění!", 'warning');
            $this->redirectHere();
        }

        try
        {
            $this->db->table(Authorizator::FORUM_TABLE)->get($id)->update(array(self::DELETED_COLUMN => TRUE));
            $this->flashMessage('Příspěvek byl smazán!', 'success');
        }
        catch (\PDOException $e)
        {
            if ($e->errorInfo[1] == 1451)
            {
                $this->flashMessage('Příspěvek nejde smazat, protože jsou na něj vázány komentáře.', 'error');
            }
            else
            {
                $this->flashMessage($e->getMessage(), 'error');
            }
        }
        $this->redirectHere();
    }

}
