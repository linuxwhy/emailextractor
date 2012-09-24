<?php

class HomepageTest extends SeleniumTestCase
{

	public function testFirstArticlePresent()
	{
		$this->url('/');

		$element = $this->byCssSelector('a');
		$this->assertEquals('Parsuj domeny', $element->text());
	}

	public function testClickToDetail()
	{
		$this->url('/');

		$element = $this->byCssSelector('a.zlatestranky');
		$element->click();

		$element = $this->byId('testdiv');
		$this->assertEquals('Clear', $element->text());
	}

}
