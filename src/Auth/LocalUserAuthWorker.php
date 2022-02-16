<?php

namespace sugrob\OnPHP\Acl\Auth;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\ObjectNotFoundException;
use OnPHP\Core\Form\Form;
use OnPHP\Core\Form\Primitive;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\OSQL\DBField;
use OnPHP\Main\DAO\DAOConnected;
use sugrob\OnPHP\Acl\Base\IAclUser;
use sugrob\OnPHP\Acl\Exception\BadLoginException;
use sugrob\OnPHP\Acl\Exception\BadPasswordException;
use sugrob\OnPHP\Acl\Exception\InvalidPasswordException;
use sugrob\OnPHP\Acl\Exception\UserNotActivatedException;
use sugrob\OnPHP\Acl\Exception\UserNotFoundException;
use sugrob\OnPHP\Acl\Exception\UserRemovedException;
use sugrob\OnPHP\Toolkit\Base\Removable;
use sugrob\OnPHP\Toolkit\Utils\IHasher;

class LocalUserAuthWorker implements IAuthWorker
{
	const PASSWORD_PATTERN = "/^[a-zA-Z0-6_\-\.\!@#%]{6,32}$/u";
	const LOGIN_PATTERN = "/^[a-z0-9_\-\.@]+$/iu";

	/**
	 * @var Form
	 */
	private $form;

	/**
	 * @var IHasher
	 */
	private $hasher;

	public function __construct(IHasher $hasher, $loginPattern = null, $passwordPattenr = null)
	{
		$this->hasher = $hasher;

		$this->form = Form::create()->
			add(
				Primitive::string("login")->
					setAllowedPattern($loginPattern ? $loginPattern : self::LOGIN_PATTERN)
			)->
			add(
				Primitive::string("password")->
					setAllowedPattern($passwordPattenr ? $passwordPattenr : self::PASSWORD_PATTERN)
			);
	}

	/**
	 * @param IAclUser $subject
	 * @param $identity
	 * @param $password
	 * @return IAclUser
	 * @throws BadLoginException
	 * @throws BadPasswordException
	 * @throws InvalidPasswordException
	 * @throws UserNotActivatedException
	 * @throws UserNotFoundException
	 * @throws \OnPHP\Core\Exception\WrongArgumentException
	 */
	public function auth(IAclUser $subject, $identity, $password): IAclUser
	{
		Assert::isInstance($subject, DAOConnected::class, 'DAOConnected user class expected');

		$this->form->importOne('password', $password);

		if ($this->form->hasError('password'))
			throw new BadPasswordException();

		$user = $this->fetchUserByIdentity($subject, $identity);

		if ($user->getPassword() !== $this->hasher->hash($password)) {
			throw new InvalidPasswordException();
		}

		return $user;
	}

	/**
	 * @param IAclUser $subject
	 * @param $identity
	 * @param bool $checkIsActivated
	 * @return IAclUser
	 * @throws BadLoginException
	 * @throws UserNotActivatedException
	 * @throws UserNotFoundException
	 */
	public function fetchUserByIdentity(IAclUser $subject, $identity, bool $checkIsActivated = true) {
		$this->form->clean()->dropAllErrors();
		$this->form->importOne("login", $identity);

		if ($this->form->hasError('login'))
			throw new BadLoginException();

		try {
			$user = $subject->
				dao()->
					getByLogic(
						Expression::eq(
							new DBField(
								$subject->getIdentityField()
							),
							$this->form->getValue("login")
						)
					);
		} catch (ObjectNotFoundException $e){
			throw new UserNotFoundException();
		}

		if ($user->isActivated()) {
			throw new UserNotActivatedException();
		}

		if ($user instanceof Removable
			&& $user->isRemoved()
		) {
			throw new UserRemovedException();
		}

		return $user;
	}
}