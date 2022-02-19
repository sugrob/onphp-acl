<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAclGroup {
	/**
	 * @return int
	 */
	public function getId()/*:int*/;

	/**
	 * @return string
	 */
	public function getName()/*:string*/;

	/**
	 * @return array
	 */
	public function getAclList()/*:array*/;

	/**
	 * @return bool
	 */
	public function isSu()/*:bool*/;
}
