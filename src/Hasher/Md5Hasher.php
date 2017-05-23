<?php
namespace CHash\Hasher;

class Md5Hasher implements HasherInterface
{
	/**
	 * @param string $str
	 * @return int
	 */
	public function hash($str)
	{
		return hexdec(substr(md5($str), 0, 8));
	}
}