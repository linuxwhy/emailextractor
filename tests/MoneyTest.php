<?php
/**
 * @covers Money
 */
class MoneyTest extends PHPUnit_Framework_TestCase
{
	/**
     * @covers Money::negate
     */
	public function testCanBeNegated()
    {
		require_once  APP_DIR.'/models/MoneyModel.php';

		$a = new MoneyModel(1);
		
		$b = $a->negate();
		
		$this->assertEquals(-1, $b->getAmount());
	}
}