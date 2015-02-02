<?php

class Database extends Nette\Object
{

    /** @var Nette\Database\Context */
    public $context;

    /**
     * @param Nette\Database\Context $db
     */
    public function __construct(Nette\Database\Context $db)
    {
        $this->context = $db;
    }

}
