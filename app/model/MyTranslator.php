<?php

class MyTranslator implements \Nette\Localization\ITranslator {

    private $lang;

    public function setLang($lang) {
        $this->lang = $lang;
        return $this;
    }

    public function translate($message, $count = NULL) {

        $array = array(
            // -------------------------------------------------------------
            // --------------------------- HOMEPAGE ------------------------
            // -------------------------------------------------------------
            'translated' =>
            array(
                'cs' => 'přeloženo',
                'en' => 'translated'
            )
        );

        return $array[$message][$this->lang];

    }

}
