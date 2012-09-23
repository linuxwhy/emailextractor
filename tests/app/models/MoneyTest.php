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
		$a = new Money(1);
		
		$b = $a->negate();
		
		$this->assertEquals(-2, $b->getAmount());
	}
}