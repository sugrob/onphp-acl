<?

namespace sugrob\OnPHPACL\Auth;

use OnPHP\Core\Base\Instantiatable;
use OnPHP\Core\Base\Singleton;
use OnPHP\Core\Exception\WrongArgumentException;
use sugrob\ OnPHPACL\Base\IAclUser;
use sugrob\ OnPHPACL\Exception\BadLoginException;
use sugrob\ OnPHPACL\Exception\BadPasswordException;
use sugrob\ OnPHPACL\Exception\InvalidPasswordException;
use sugrob\ OnPHPACL\Exception\UserNotActivatedException;
use sugrob\ OnPHPACL\Exception\UserNotFoundException;
use sugrob\OnPHPToolkit\PassHasher;
use sugrob\OnPHPToolkit\Utils\IHasher;

class AuthManager extends Singleton implements Instantiatable
{
	/**
	 * Name of user class
	 */
	protected $nullUser = null;

	/**
	 * @var IAclUser
	 */
	protected $user = null;

	/**
	 * @var IAuthWorker
	 */
	protected $worker;

	/**
	 * @var boolean
	 */
	protected $remember = false;

	/**
	 * @var IHasher
	 */
	protected $hasher;

	/**
	 * @return AuthManager
	 */
	public static function me()
	{
		return Singleton::getInstance(__CLASS__);
	}

	/**
	 * @param IAuthWorker $worker
	 * @return AuthManager
	 */
	public function setAuthWorker(IAuthWorker $worker) {
		$this->worker = $worker;
		return $this;
	}

	/**
	 * @return AuthManager
	 */
	public function clearWorker() {
		$this->worker = null;
		return $this;
	}

	/**
	 * @return AuthManager
	 */
	public function setCookiesSalt($cSalt) {
		$this->hasher = new PassHasher($cSalt);
		return $this;
	}

	/**
	 * @return IAclUser|null
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return IAclGroup | null
	 */
	public function getUserGroup()
	{
		if ($this->nullUser instanceof IAclGroupAssociated) {
			return $this->user
				? $this->user->getAclGroup()
				: null;
		}
	}

	/**
	 * @return IAclUser
	 */
	public function getNullUser()
	{
		return $this->nullUser;
	}

	/**
	 * @param IAclUser | class name
	 * @return Authorization
	 */
	public function setUserClass($class)
	{
		if (!is_object($class)) {
			$object = CoreUtils::guessObject($class);
		} else
			$object = $class;

		if ($object instanceof IAclUser) {
			$this->nullUser = $object;

			if (!$this->user instanceof IAclUser
				|| get_class($this->user) != get_class($this->nullUser)
			) {
				//In case of user type has been changed, let's try to auth him by sessions or cookies
				$this->user == null;
				$this->setUserFromSession();
			}

			return self::me();
		} else
			throw new WrongArgumentException('Argument must be valid class name or object instance of IAclUser');

	}

	/**
	 * @return UserAuth
	 */
	public function setRemember($value)
	{
		$this->remember = $value === true;

		return $this;
	}

	/**
	 * @param IAclUser $user
	 * @return AuthManager
	 */
	public function setUser(IAclUser $user)
	{
		Assert::brothers($this->nullUser, $user, 'First argument must be instance of '.get_class($this->nullUser));

		$this->user = $user;
		Session::assign($this->getSessionVarName(), serialize($user));

		return $this;
	}

	/**
	 * @throws BadLoginException
	 * @throws BadPasswordException
	 * @throws InvalidPasswordException
	 * @throws UserNotActivatedException
	 * @throws UserNotFoundException
	 * @throws WrongArgumentException
	 * @param $identity
	 * @param $password
	 * @return bool
	 */
	public function auth($identity, $password)
	{
		$this->checkIsUserTypeSet();

		if ($this->isAuth()) {
			return true;
		}

		$user = $this->worker->auth(self::me()->nullUser, $identity, $password);

		Session::assign(
			$this->getSessionVarName(),
			serialize($user)
		);

		$this->user = $user;

		if ($this->remember) {
			$this->setRememberMeCookie();
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function isAuth()
	{
		if (
			(
				$this->user instanceof IAclUser
				&& $this->user->getId()
			)
			|| $this->setUserFromSession()
		) {
			return true;
		}

		return false;
	}

	public function logout()
	{
		$_SESSION[$this->getSessionVarName()] = null;
		// execution order is important
		$this->setRememberMeCookie(null);
		$this->user = null;

		// session_regenerate_id();
		Session::destroy();
		Session::start();
	}

	public function tryToRemember()
	{
		if (!self::isAuth()) {
			return $this->setUserFromSession()
				|| $this->setUserFromCookie();
		}

	}

	private function getControlCode(IAclUser $user)
	{
		return $this->hasher->hash($user->getPassword());
	}

	/**
	 * @param int $lifetime (60*60*24*30)
	 * @return bool
	 */
	private function setRememberMeCookie($lifetime = 2592000, $force = false)
	{
		if (self::isAuth() || $force) {
			if ($lifetime) {
				$value = base64_encode($this->getUser()->getIdentity()) . ":". self::getControlCode($this->user);
				$lifetime += time();
			} else {
				$value = null;
				$lifetime = -1;
			}

			return setcookie(
				$this->getRememberMeCookieName(),
				$value,
				$lifetime,
				'/'
			);
		}

		return false;
	}

	/**
	 * @param HttpRequest $request
	 * @return string
	 */
	private function getRememberMeCookie()
	{
		$rememberMeCookieName = $this->getRememberMeCookieName();

		if (isset($_COOKIE[$rememberMeCookieName]))
			return $_COOKIE[$rememberMeCookieName];

		return null;
	}

	/**
	 * @param HttpRequest $request
	 * @return bool
	 */
	private function setUserFromCookie()
	{
		if (($cookie = $this->getRememberMeCookie())) {
			list($identity, $checkCode) = explode(':', $cookie);

			if (strlen(trim($identity))
				&& $identity = base64_decode(trim($identity))
			) {
				try {
					$user = $this->worker->fetchUserByIdentity($this->nullUser, $identity);
					/**
					 * User has been found! Let's try to do auth
					 */
					if ($checkCode == $this->getControlCode($user)) {
						$this->user = $user;

						Session::assign(
							$this->getSessionVarName(),
							serialize($user)
						);

						return true;
					}
				} catch (BadLoginException $e) {
				} catch (UserNotFoundException $e) {}
			}

			/**
			 * User has been removed or not found or other reasons,
			 * so we need drop cookie
			 */
			self::setRememberMeCookie(null, true);
		}

		return false;
	}

	/**
	 * Trying to get User from session if he already has been auth
	 * @param IAclUser $userObject
	 * @return boolean
	 */
	private function setUserFromSession()
	{
		$sessionVar = $this->getSessionVarName();

		if ($ses = Session::get($sessionVar)) {
			$user = unserialize($ses);

			if (get_class($this->nullUser) == get_class($user)) {
				$this->user = $user;
				return true;
			}
		}

		return false;
	}

	private function checkIsUserTypeSet()
	{
		Assert::isNotNull(
			$this->nullUser,
			'You must set userClass first.'
		);

		return $this;
	}

	private function getSessionVarName()
	{
		return str_rot13(strtolower(__CLASS__.get_class($this->nullUser)));
	}

	private function getRememberMeCookieName()
	{
		return 'RMC_'.$this->getSessionVarName();
	}
}