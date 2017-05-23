<?php
namespace CHash;

use CHash\Hasher\HasherInterface;
use CHash\Hasher\Crc32Hasher;

class CHash
{

	private $hasher;

	/**
	 * 虚拟节点数量
	 *
	 * @var int
	 */
	private $replicas = 64;

	/**
	 * 所有节点
	 *
	 * @var array
	 */
	private $nodeList = array();

	/**
	 * 所有物理节点(真实节点)
	 *
	 * @var array
	 */
	private $physicalNodeList = array();

	/**
	 * 是否已经排序
	 *
	 * @var bool
	 */
	private $isSorted = false;

	/**
	 * 总物理节点个数
	 *
	 * @var int
	 */
	private $physicalNodeCount = 0;


	/**
	 * CHashing constructor.
	 *
	 * @param HasherInterface|null $hasher
	 * @param null $replicas
	 */
	public function __construct(HasherInterface $hasher = null, $replicas = null)
	{
		$this->hasher = $hasher ? $hasher : new Crc32Hasher();

		$replicas = intval($replicas);
		if ($replicas > 0) {
			$this->replicas = $replicas;
		}
	}

	/**
	 * 添加节点
	 *
	 * @param string $node
	 * @param int $widget
	 * @return $this
	 * @throws Exception
	 */
	public function addNode($node, $widget = 1)
	{
		if (isset($this->physicalNodeList[$node])) {
			throw new Exception("Physical Node '$node' already exists.");
		}

		for ($i = 0; $i < round($this->replicas * $widget); $i++) {
			$position = $this->hasher->hash(sprintf("%s#%d", $node, $i));
			$this->nodeList[$position] = $node;
			$this->physicalNodeList[$node][] = $position;
		}

		++$this->physicalNodeCount;

		return $this;
	}


	/**
	 * 批量添加节点
	 *
	 * @param array $nodes
	 * @param int $widget
	 * @return $this
	 * @throws Exception
	 */
	public function addNodes($nodes, $widget = 1)
	{
		foreach ($nodes as $node) {
			$this->addNode($node, $widget);
		}

		return $this;
	}


	/**
	 * 移除节点
	 *
	 * @param string $node
	 * @return $this
	 * @throws Exception
	 */
	public function removeNode($node)
	{
		if (!isset($this->physicalNodeList[$node])) {
			throw new Exception("Physical Node '$node' not exists.");
		}

		foreach ($this->physicalNodeList[$node] as $position) {
			unset($this->nodeList[$position]);
		}

		unset($this->physicalNodeList[$node]);
		--$this->physicalNodeCount;

		return $this;
	}

	/**
	 * 获取所有节点
	 *
	 * @return array
	 */
	public function getAllNode()
	{
		return array_keys($this->nodeList);
	}


	/**
	 * 遍历某个资源对应的节点信息
	 *
	 * @param string $resource
	 * @return string
	 * @throws Exception
	 */
	public function lookup($resource)
	{
		$nodes = $this->lookupList($resource, 1);
		if (empty($nodes)) {
			throw new Exception('No node exist');
		}

		return $nodes[0];
	}


	/**
	 * 批量获取某个资源所对应的节点集合
	 *
	 * @param string $resource
	 * @param int $count
	 * @return array
	 * @throws Exception
	 */
	public function lookupList($resource, $count = 1)
	{
		if (empty($count)) {
			throw new Exception("Params fail.");
		}

		if (0 == count($this->physicalNodeList)) {
			return [];
		}

		if (1 == $this->physicalNodeCount) {
			return array_uinque(array_values($this->nodeList));
		}

		$position = $this->hasher->hash($resource);
		$result = [];
		$collect = false;

		$this->sortNodeList();

		foreach ($this->nodeList as $key => $value) {
			if (!$collect && $key > $position) {
				$collect = true;
			}

			if ($collect && !in_array($value, $result)) {
				$result[] = $value;
			}

			if (count($result) == $count || count($result) == $this->physicalNodeCount) {
				return $result;
			}
		}

		foreach ($this->nodeList as $key => $value) {
			if (!in_array($value, $result)) {
				$result[] = $value;
			}

			if (count($result) == $count || count($result) == $this->physicalNodeCount) {
				return $result;
			}
		}

		return $result;
	}

	public function __toString()
	{
		return sprintf(
			'%s{nodes:[%s]}',
			get_class($this),
			implode(',', $this->getAllNode())
		);
	}

	/**
	 * 所有节点排序
	 *
	 * @return $this
	 */
	private function sortNodeList()
	{
		if (!$this->isSort) {
			ksort($this->nodeList, SORT_REGULAR);
			$this->isSorted = true;
		}
		return $this;
	}
}