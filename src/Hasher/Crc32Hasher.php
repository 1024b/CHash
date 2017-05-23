<?php
namespace CHash\Hasher;

class Crc32Hasher implements HasherInterface 
{
	/**
	 * @param string $str
	 * @return int
	 */
	public function hash($str)
	{
		return crc32($str);
	}

}