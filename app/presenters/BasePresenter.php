<?php

namespace App\Presenters;

use Nette,
    App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @persistent */
    public $lang;
    protected $languages = array('cs', 'en');
    protected $locale = array('cs' => 'cs_CZ', 'en' => 'en_US');
    protected $dateFormat = array('cs' => '%e. %B %Y', 'en' => '%B %e, %Y');
    protected $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

	/* translator */
    protected function translate($msg) {
        return $this->translator->translate($msg);
    }

    protected function startup()
    {
        parent::startup();
        
        if (!isset($this->lang))
        {
            $this->lang = $this->getHttpRequest()->detectLanguage($this->languages);
        }

        setlocale(LC_ALL, ($this->locale[$this->lang] . '.UTF-8'));
        $this->template->dateFormat = $this->dateFormat[$this->lang];

        $this->template->setTranslator($this->context->translator->setLang($this->lang));
    }

}
