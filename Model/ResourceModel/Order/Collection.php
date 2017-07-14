<?php

namespace Dotdigitalgroup\Email\Model\ResourceModel\Order;

/**
 * Class Collection
 * @package Dotdigitalgroup\Email\Model\ResourceModel\Order
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'email_order_id'; //@codingStandardsIgnoreLine

    /**
     * Initialize resource collection.
     */
    public function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init(
            'Dotdigitalgroup\Email\Model\Order',
            'Dotdigitalgroup\Email\Model\ResourceModel\Order'
        );
    }
}
