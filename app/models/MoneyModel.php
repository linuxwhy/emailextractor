<?php

class MoneyModel {
	private $m;
	
	
	function __construct( $money ) {
		$this->m = $money;
		
	}
	
	
	function getM(){
		return $this->m;
	}
	
	function negate() {
		return -1*$this->m;
	}
}