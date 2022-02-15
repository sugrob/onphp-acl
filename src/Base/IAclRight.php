<?php

namespace sugrob\OnPHPACL\Base;

interface IAclRight {
	public function getId():int;
	public function getName():string;
}
