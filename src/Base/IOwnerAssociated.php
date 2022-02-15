<?php

namespace sugrob\OnPHPACL\Base;

interface IOwnerAssociated {
	/**
	 * @return IOwner
	 */
	public function getOwner();
	public function setOwner(IOwner $owner);
}
