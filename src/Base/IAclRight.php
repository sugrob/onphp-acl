<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAclRight {
	public function getId():int;
	public function getName():string;
}
