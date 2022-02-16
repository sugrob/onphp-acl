<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAcl {
	public function getGroup():IAclGroup;
	public function getContext():IAclContext;
	public function getRight():IAclRight;
	public function getGroupId():int;
	public function getContextId():int;
	public function getRightId():int;
}
