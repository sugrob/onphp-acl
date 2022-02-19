<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAcl {
	/**
	 * @return IAclGroup
	 */
	public function getGroup()/*:IAclGroup*/;

	/**
	 * @return IAclContext
	 */
	public function getContext()/*:IAclContext*/;

	/**
	 * @return IAclRight
	 */
	public function getRight()/*:IAclRight*/;

	/**
	 * @return int
	 */
	public function getGroupId()/*:int*/;

	/**
	 * @return int
	 */
	public function getContextId()/*:int*/;

	/**
	 * @return int
	 */
	public function getRightId()/*:int*/;
}
