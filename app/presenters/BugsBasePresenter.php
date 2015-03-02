<?php

namespace App\Presenters;

use Nette,
    App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BugsBasePresenter extends Nette\Application\UI\Presenter
{

    const LANG_COLUMN = 'lang';
    const UPLOAD_FOLDER = 'upload';
    const DELETED_COLUMN = 'deleted';

    /** @persistent */
    public $lang;
    protected $db;

    /* ***** EDIT HERE - BEGIN */
    private $languages = array('cs', 'en');
    private $locale = array('cs' => 'cs_CZ', 'en' => 'en_US');
    private $dateFormat = array('cs' => '%e. %B %Y', 'en' => '%B %e, %Y');
    /* ***** EDIT HERE - END */

    public function __construct(\Nette\DI\Container $context = NULL)
    {
        parent::__construct($context);
        $this->db = $context->database->context;
    }

	/* translator */
    protected function translate($msg)
    {
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
        $this->template->lang = $this->lang;
        $this->template->langs = $this->languages;
        $this->template->presenter = $this->getName();

        $this->template->setTranslator($this->context->translator->setLang($this->lang));
    }

    protected function redirectHere($action = '', $id = NULL)
    {
        if ($id == NULL)
        {
            $this->redirect($this->getName() . ':' . $action);
        }
        else
        {
            $this->redirect($this->getName() . ':' . $action, $id);
        }
    }

    protected function redirectHome()
    {
        $this->redirect('Homepage:');
    }

    protected function redirectToLogin($presenter = NULL)
    {
        $this->redirect('Sign:in?redirectTo=' . $presenter);
    }

    public function cancel(\Nette\Forms\Controls\SubmitButton $button)
    {
        $this->redirectHere();
    }

}
