<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAclContext {
	public function getName():string;
	public function getClass():string;
}
