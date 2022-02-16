<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAclGroup {
	public function getId():int;
	public function getName():string;
	public function getAclList():array;
	public function isSu():bool;
}
