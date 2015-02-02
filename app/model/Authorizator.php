<?php

use Nette\Security as NS;


/**
 * Users authorizator.
 */
class Authorizator extends Nette\Object implements NS\IAuthorizator
{
    
    private $db;
	private $acl;
    private $resourceToTable = array(
        'akce' => 'akce',
        'zprava' => 'forum',
        'fotka' => 'fotky',
        'poplatek' => 'poplatky',
        'album' => 'alba',
        'nastaveni' => 'nastaveni',
        'ucet' => 'uzivatele',
        'uzivatel' => 'uzivatele',
        'typickaVlastnost' => 'uzivatele',
        'aktivnost' => 'uzivatele'
    );

	public function __construct(Nette\Database\Connection $database)
	{
        $acl = new Nette\Security\Permission;
        
        $acl->addRole('guest');
        $acl->addRole('registered', 'guest');
        $acl->addRole('administrator', 'registered');

        $acl->addResource('akce');
        $acl->addResource('zprava');
        $acl->addResource('fotka');
        $acl->addResource('poplatek');
        $acl->addResource('album');
        $acl->addResource('nastaveni');
        $acl->addResource('ucet');
        $acl->addResource('uzivatel');
        $acl->addResource('typickaVlastnost');
        $acl->addResource('aktivnost');
        
        $acl->addResource('vlastnictvi'); // pri vkladani novyho Resource muze urcit majitele
        
        $acl->allow('guest', 'akce', 'view');
        $acl->allow('registered', array('typickaVlastnost', 'aktivnost'), array('view', 'edit'));
        $acl->allow('registered', 'uzivatel', 'view');
        $acl->allow('registered', array('akce', 'zprava', 'fotka', 'album'), array('view', 'edit', 'add'));
        $acl->allow('registered', 'ucet', array('view', 'edit'));
        $acl->allow('administrator', NS\Permission::ALL, array('view', 'edit', 'add', 'send', 'transfer'));
        
        $this->acl = $acl;
        $this->db = $database;
	}

    public function isAllowed($role, $resource, $privilege, $userId, $resourceId)
    {
        if ($role == 'registered' && ($privilege == 'edit' || $privilege == 'send')
                && $resource != 'typickaVlastnost')
        {   // check ownership too
            return $this->acl->isAllowed($role, $resource, $privilege) &&
                $this->isOwner($userId, $this->resourceToTable[$resource], $resourceId);
        }
        else
        {
            return $this->acl->isAllowed($role, $resource, $privilege);
        }
    }
    
    private function isOwner($userId, $table, $resourceId)
    {
        if ($userId == NULL || $resourceId == NULL)
        {
            return false;
        }
        else
        {
            $column = '';
            
            switch ($table)
            {
                case 'alba':
                case 'fotky':
                case 'akce':
                case 'forum':
                    $column = 'vlozil';
                    break;
                case 'uzivatele':
                    $column = 'id';
                    break;
            }
            
            return $this->db->table($table)->get($resourceId)->$column == $userId;
        }
    }

}
