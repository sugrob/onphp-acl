<?php

namespace sugrob\OnPHPACL\Base;

interface IAclUser {
	public function getIdentityField();
	public function getIdentity();

	public function getPassword();
	public function setPassword($password);

	public function isActivated(): bool;
}