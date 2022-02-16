<?php

namespace sugrob\OnPHP\Acl\Base;

interface IOwner {
	/**
	 * @param IOwnerAssociated $object
	 * @return bool
	 */
	public function isOwnerOf(IOwnerAssociated $object):bool;
}