<?php
namespace CHash\Tests;
/**
 *
 * User: ddinnnng@gmail.com
 * Date: 2017-05-23
 */

use CHash\CHash;
use PHPUnit_Framework_TestCase;

class CHashingTest extends PHPUnit_Framework_TestCase
{

	public function testT()
	{
		$c = new CHash();
		$c->addNodes(['192.168.0.1', '192.168.0.2']);


		var_dump($c->getAllNode());
	}

}