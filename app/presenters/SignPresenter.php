<?php

namespace App\Presenters;

use Nette,
	App\Model,
	Nette\Environment;


/**
 * Presenter to handle authorization.
 */
class SignPresenter extends BugsBasePresenter
{

    protected function startup()
    {
        parent::startup();

        if ($this->getUser()->isLoggedIn() && $this->getAction() != 'out')
        {
        	$this->redirect('Homepage:');
        }
    }

    public function actionIn($redirectTo)
    {
    	// just carry the redirectTo parameter
    }

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Nette\Application\UI\Form;
		// $form->addProtection(); // do NOT dare to uncomment this !!! sign:in won't work !!!
		
		$form->addText('username', 'Přihlašovací jméno:')
			->addRule(Nette\Application\UI\Form::EMAIL)
			->setRequired('Prosím vyplňte uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosím vyplňte heslo.');

		// $form->addHidden('url', @$this->request->parameters['url']);

		$form->addCheckbox('remember', ' Zůstat přihlášen');

		$form->addSubmit('send', 'Přihlásit se');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}

	public function signInFormSucceeded($form)
	{
		try
		{
			$values = $form->getValues();
			if ($values->remember) {
				$this->getUser()->setExpiration('14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('20 minutes', TRUE);
			}
			$this->getUser()->login($values->username, $values->password);
            $this->flashMessage('Přihlášení bylo úspěšné.');

			if (strlen($this->getParam('redirectTo')) > 0)
			{
				$this->redirect($this->getParam('redirectTo') . ':');
			}
			else
			{
				$this->redirect('Homepage:');
			}

		}
		catch (Nette\Security\AuthenticationException $e)
		{
			$form->addError($e->getMessage());
			$this->flashMessage($e->getMessage(), 'error');
		}
	}

	public function renderOut($redirectTo = 'Homepage')
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl(a) jste odhlášen(a).');
		$this->redirect($redirectTo . ':');
	}

}
