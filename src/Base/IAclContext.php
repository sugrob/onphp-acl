<?php

namespace sugrob\OnPHPACL\Base;

interface IAclContext {
	public function getName():string;
	public function getClass():string;
}
