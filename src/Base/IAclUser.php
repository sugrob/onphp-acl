<?php

namespace sugrob\OnPHP\Acl\Base;

interface IAclUser {
	public function getIdentityField();
	public function getIdentity();

	public function getPassword();
	public function setPassword($password);

	/**
	 * @return bool
	 */
	public function isActivated()/*:bool*/;
}