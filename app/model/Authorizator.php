<?php

use Nette\Security as NS;


/**
 * Users authorizator.
 */
class Authorizator extends Nette\Object implements NS\IAuthorizator
{

    const ROLE_GUEST = 'guest';
    const ROLE_REGISTERED = 'registered';
    const ROLE_ADMIN = 'admin';

    const PAGES_TABLE = 'pages';
    const PAGES_RESOURCE = 'page';
    const USERS_TABLE = 'users';
    const USERS_RESOURCE = 'user';

    /* ***** EDIT HERE - BEGIN */
    const EVENTS_TABLE = 'events';
    const EVENTS_RESOURCE = 'event';
    const FILES_TABLE = 'files';
    const FILES_RESOURCE = 'file';
    const FORUM_TABLE = 'forum';
    const FORUM_RESOURCE = 'post';
    /* ***** EDIT HERE - END */

    private $db;
    private $acl;

    private $resourceToTable = array(
        self::PAGES_RESOURCE => self::PAGES_TABLE,
        self::USERS_RESOURCE => self::USERS_TABLE,

        /* ***** EDIT HERE - BEGIN */
        self::EVENTS_RESOURCE => self::EVENTS_TABLE,
        self::FILES_RESOURCE => self::FILES_TABLE,
        self::FORUM_RESOURCE => self::FORUM_TABLE
        /* ***** EDIT HERE - END */
    );

    public function __construct(\Nette\DI\Container $context = NULL)
    {
        $acl = new Nette\Security\Permission;
        
        $acl->addRole(self::ROLE_GUEST);
        $acl->addRole(self::ROLE_REGISTERED, self::ROLE_GUEST);
        $acl->addRole(self::ROLE_ADMIN, self::ROLE_REGISTERED);

        $acl->addResource(self::PAGES_RESOURCE);
        $acl->addResource(self::USERS_RESOURCE);
        
        /* ***** EDIT HERE - BEGIN */
        $acl->addResource(self::EVENTS_RESOURCE);
        $acl->addResource(self::FILES_RESOURCE);
        $acl->addResource(self::FORUM_RESOURCE);
        /* ***** EDIT HERE - END */

        // $acl->addResource('ownership'); // pri vkladani novyho Resource muze urcit majitele
        
        /* ***** EDIT HERE - BEGIN */
        $acl->allow(self::ROLE_GUEST, self::PAGES_RESOURCE, 'view');
        $acl->allow(self::ROLE_GUEST, self::FILES_RESOURCE, 'view');
        $acl->allow(self::ROLE_GUEST, self::EVENTS_RESOURCE, 'view');
        $acl->allow(self::ROLE_GUEST, self::FORUM_RESOURCE, 'view');
        $acl->allow(self::ROLE_REGISTERED, self::EVENTS_RESOURCE, array('view', 'add', 'edit', 'delete'));
        $acl->allow(self::ROLE_REGISTERED, self::FORUM_RESOURCE, array('view', 'add', 'edit', 'delete'));
        $acl->allow(self::ROLE_REGISTERED, self::USERS_RESOURCE, array('view', 'edit'));
        $acl->allow(self::ROLE_ADMIN, NS\Permission::ALL, array(
            'manage', /* also allows to switch role (e.g. registered --> admin) */
            'view',
            'viewTheirOwn',
            'add',
            'edit',
            'editTheirOwn',
            'delete',
            'deleteTheirOwn'
        ));
        /* ***** EDIT HERE - END */
        
        $this->acl = $acl;
        $this->db = $context->database->context;
    }

    public function isAllowed($role, $resource, $privilege, $userId = NULL, $resourceId = NULL)
    {
        // dump($userId);
        // dump($resourceId);

        if ($role == self::ROLE_REGISTERED)
        {
            switch ($privilege)
            {
                case 'viewTheirOwn':
                    $privilege = 'view';
                    break;
                case 'editTheirOwn':
                    $privilege = 'edit';
                    break;
                case 'deleteTheirOwn':
                    $privilege = 'delete';
                    break;
                default:
                    return $this->acl->isAllowed($role, $resource, $privilege);
            }

            return
                $this->acl->isAllowed($role, $resource, $privilege) &&
                $this->isOwner($userId, $this->resourceToTable[$resource], $resourceId);
        }
        else
        {
            // dump($role);
            // dump($privilege);
            return $this->acl->isAllowed($role, $resource, $privilege);
        }
    }
    
    private function isOwner($userId, $table, $resourceId)
    {
        // dump($userId);
        // dump($resourceId);

        if ($userId == NULL || $resourceId == NULL)
        {
            return false;
        }
        else
        {
            $column;
            
            switch ($table)
            {
                /* ***** EDIT HERE - BEGIN */
                case self::EVENTS_TABLE:
                    $column = 'insertedBy';
                    break;
                case self::FILES_TABLE:
                    $column = 'owner';
                    break;
                case self::FORUM_TABLE:
                    $column = 'insertedBy';
                    break;
                /* ***** EDIT HERE - END */
                default:
                    $column = 'id';
            }
            
            // dump($table);
            // dump($this->db->table('events'));

            return $this->db->table($table)->get($resourceId)->$column == $userId;
        }
    }

}
