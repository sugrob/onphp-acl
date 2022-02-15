<?php

namespace sugrob\OnPHPACL\Base;

interface IOwner {
	/**
	 * @param IOwnerAssociated $object
	 * @return bool
	 */
	public function isOwnerOf(IOwnerAssociated $object):bool;
}