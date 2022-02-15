<?php

namespace sugrob\OnPHPACL\Flow;

use sugrob\OnPHPACL\Base\IAclContext;
use sugrob\OnPHPACL\Base\IAclRight;

class ActionAccessResolver
{
	private $actionAccessMap = array();

	/**
	* @return ActionAccessResolver
	*/
	public function addAction($actionName, IAclRight $right)
	{
		$this->actionAccessMap[$actionName] = $right;
		return $this;
	}

	/**
	* @return ActionAccessResolver
	*/
	public function dropAction($actionName)
	{
		if (isset($this->actionAccessMap[$actionName]))
			unset($this->actionAccessMap[$actionName]);

		return $this;
	}

	public function isAllowedAction($actionName, IAclContext $context)
	{
		if (isset($this->actionAccessMap[$actionName])) {
			return AclResolver::isAccessGranted(
				$this->actionAccessMap[$actionName],
				$context
			);
		}

		return false;
	}
}