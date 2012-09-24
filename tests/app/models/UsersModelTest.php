<?php
class UsersModelTest extends UnitTestCase{
	function testCalculateHash(){
		$a = new \UsersModel;
		$this->assertEquals( $a->calculateHash( 'test'),'cf4fb6e34745a8d75da5dbb7b2924382');
	}
}