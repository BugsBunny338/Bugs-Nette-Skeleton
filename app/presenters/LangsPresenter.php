<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Example of presenter that searches for templates in corresponding language folders.
 */
class LangsPresenter extends BugsLanguagesPresenter
{

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
