<?php

namespace sugrob\OnPHP\Acl\Base;

interface IOwnerAssociated {
	/**
	 * @return IOwner
	 */
	public function getOwner();
	public function setOwner(IOwner $owner);
}
