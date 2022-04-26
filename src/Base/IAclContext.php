<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAclContext {
	/**
	 * @return string
	 */
	public function getName()/*:string*/;

	/**
	 * @return string
	 */
	public function getClass()/*:string*/;
}
