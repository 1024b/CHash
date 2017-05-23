<?php
namespace CHash\Hasher;

interface HasherInterface 
{
	/**
	 * @param string $str
	 * @return mixed
	 */
	public function hash($str);
}
