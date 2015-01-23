<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		$router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		// $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
        $router[] = new Route('[<lang cs|en>/]<presenter>/<action>[/<id>]', array(
                'lang' => 'cs',
                'presenter' => 'Homepage',
                'action' => 'default',
        ));
		return $router;
	}

}
