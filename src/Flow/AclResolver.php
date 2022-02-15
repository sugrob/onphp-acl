<?

namespace sugrob\OnPHPACL\Flow;

use OnPHP\Core\Base\Identifiable;
use OnPHP\Core\Base\Instantiatable;
use OnPHP\Core\Base\Singleton;
use sugrob\OnPHPACL\Auth\AuthManager;
use sugrob\OnPHPACL\Base\IAclContext;
use sugrob\OnPHPACL\Base\IAclGroup;
use sugrob\OnPHPACL\Base\IAclGroupAssociated;
use sugrob\OnPHPACL\Base\IAclRight;
use sugrob\OnPHPACL\Base\IOwner;
use sugrob\OnPHPACL\Base\IOwnerAssociated;

class AclResolver extends Singleton implements Instantiatable
{
	/**
	 * @return AclResolver
	 */
	public static function me() {
		return Singleton::getInstance(__CLASS__);
	}

	/**
	 * @param IOwnerAssociated $object
	 * @param IAclRight $right
	 * @param IAclContext $context
	 * @return bool
	 */
	public function isDocumentAccessGranted(IOwnerAssociated $object, IAclRight $right, IAclContext $context): bool
	{
		if (AuthManager::me()->isAuth()) {
			$user = AuthManager::me()->getUser();

			if ($user instanceof IOwner && $user->isOwnerOf($object)) {
				return true;
			}

			return self::me()->isAccessGranted($right, $context);
		}

		return false;
	}

	/**
	 * @param IAclRight $right
	 * @param IAclContext $context
	 * @return bool
	 */
	public function isAccessGranted(IAclRight $right, IAclContext $context): bool
	{
		if (AuthManager::me()->isAuth()) {
			$user = AuthManager::me()->getUser();

			if ($user instanceof IAclGroupAssociated) {
				if ($group = $user->getAclGroup()) {
					return self::me()->isGroupAccessGranted($group, $right, $context);
				}
			}
		}

		return false;
	}

	protected function isGroupAccessGranted(IAclGroup $group, IAclRight $right, IAclContext $context) {
		if ($group->isSu())
			return true;

		foreach ($group->getAclList() as $aclRule){
			if ($aclRule->getRightId() == $right->getId()
				&& (
					(
						$context instanceof Identifiable
						&& $aclRule->getContextId() == $context->getId()
					) || (
						!$context instanceof Instantiatable
						&& $aclRule->getContext()->getClass() == $context->getClass()
					)
				)
			) {
				return true;
			}
		}

		return false;
	}
}