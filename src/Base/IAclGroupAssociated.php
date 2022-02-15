<?php

namespace sugrob\OnPHPACL\Base;

interface IAclGroupAssociated {
	/**
	 * @return IAclGroup
	 */
	public function getAclGroup(): IAclGroup;
}