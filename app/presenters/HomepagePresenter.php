<?php

namespace App\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form;


class HomepagePresenter extends BugsBasePresenter
{
	protected function createComponentContactForm()
	{
		$form = new Form;

		$form->addText('name')
		  ->setRequired();
		$form->addText('email')
		  ->setRequired()
		  ->addRule(Form::EMAIL);
		$form->addTextArea('message')
		  ->setRequired();

		$form->addSubmit('submit', 'Send');
		$form->onSuccess[] = array($this, 'contactFormSubmitted');

		return $form;
	}

	public function contactFormSubmitted(Form $form)
	{
		$values = $form->getValues();

		$mail = new Nette\Mail\Message;
		$mail->setFrom($values['email'])
			->addTo('for@bar.cz')
		  	->addBcc('foo@bar.cz')
		  	->setSubject('Mail from website abc.de')
			->setBody($values['name'] . ' (' . $values['email'] . '): ' . $values['message']);

		$mailer = new Nette\Mail\SendmailMailer;
		$mailer->send($mail);

		$this->flashMessage('Thanks for your message!');
		$this->redirect('Homepage:');
	}
}
