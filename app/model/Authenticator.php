<?php

use Nette\Security as NS;

/**
 * Users authenticator.
 * @deprecated use UserManager.php instead
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator
{
	/** @var Nette\Database\Connection */
	private $database;

	public function __construct(Nette\Database\Connection $database)
	{
		$this->database = $database;
	}

	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = $this->database->table('uzivatele')->where('email', $username)->fetch();

		if (!$row) {
			throw new NS\AuthenticationException("Email '$username' nenalazen.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->heslo !== $this->calculateHash($password)) {
			throw new NS\AuthenticationException("Neplatné heslo.", self::INVALID_CREDENTIAL);
		}

		unset($row->heslo);
		return new NS\Identity($row->id, $row->role, $row->toArray());
	}

	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
		return md5($password);
	}

}
