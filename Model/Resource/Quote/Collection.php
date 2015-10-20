<?php

namespace Dotdigitalgroup\Email\Model\Resource\Quote;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	/**
	 * Initialize resource collection
	 *
	 * @return void
	 */
	public function _construct()
	{
		$this->_init('Dotdigitalgroup\Email\Model\Quote', 'Dotdigitalgroup\Email\Model\Resource\Quote');
	}


}