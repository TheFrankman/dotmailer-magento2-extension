<?php

namespace Dotdigitalgroup\Email\Model\ResourceModel\Cron;

/**
 * Class Collection
 * @package Dotdigitalgroup\Email\Model\ResourceModel\Cron
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'schedule_id'; //@codingStandardsIgnoreLine

    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct() //@codingStandardsIgnoreLine
    {
        $this->_init('Magento\Cron\Model\Schedule', 'Magento\Cron\Model\ResourceModel\Schedule');
    }
}
