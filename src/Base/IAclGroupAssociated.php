<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAclGroupAssociated {
	/**
	 * @return IAclGroup
	 */
	public function getAclGroup(): IAclGroup;
}