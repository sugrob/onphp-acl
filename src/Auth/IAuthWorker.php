<?php

namespace sugrob\OnPHPACL\Auth;

use sugrob\OnPHPACL\Base\IAclUser;

interface IAuthWorker
{
	/**
	 * @thows @InvalidPasswordException | @UserNotFoundException | @UserNotActivatedException
	 * @param $subject
	 * @param $identity
	 * @param $password
	 * @return IAclUser
	 */
	public function auth(IAclUser $subject, $identity, $password) : IAclUser;

	/**
	 * @param IAclUser $subject
	 * @param $identity
	 * @param bool $checkIsActivated
	 * @return IAclUser
	 */
	public function fetchUserByIdentity(IAclUser $subject, $identity, bool $checkIsActivated = true);
}