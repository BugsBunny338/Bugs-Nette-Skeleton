<?php

class Translator implements \Nette\Localization\ITranslator {

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
            'translated' => array(
                'cs' => 'přeloženo',
                'en' => 'translated'
            ),
            'curLang' => array(
                'cs' => 'čeština',
                'en' => 'English'
            )
        );

        return $array[$message][$this->lang];

    }

}
