<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

  public function renderDefault()
  {
    $this->template->anyVariable = 'any value';
  }

  protected function createComponentContactForm()
  {
    $form = new Nette\Application\UI\Form;

    $form->addText('name')
      ->setRequired();
    $form->addText('email')
      ->setRequired()
      ->addRule(Nette\Application\UI\Form::EMAIL);
    $form->addTextArea('message')
      ->setRequired();

    $form->addSubmit('submit', 'Send');
    $form->onSuccess[] = array($this, 'contactFormSubmitted');

    return $form;
  }

  public function contactFormSubmitted(Nette\Application\UI\Form $form)
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
