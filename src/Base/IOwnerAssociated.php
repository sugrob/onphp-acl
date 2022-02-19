<?php

namespace sugrob\OnPHP\Acl\Base;

interface IOwnerAssociated {
	/**
	 * @return IOwner
	 */
	public function getOwner()/*:IOwner*/;
	public function setOwner(IOwner $owner);
}
