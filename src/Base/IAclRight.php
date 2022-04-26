<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAclRight {
	/**
	 * @return int
	 */
	public function getId()/*:int*/;

	/**
	 * @return string
	 */
	public function getName()/*:string*/;
}
