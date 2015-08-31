<?php

namespace Dotdigitalgroup\Email\Block\Adminhtml;

class Importer extends \Magento\Backend\Block\Widget\Container
{
	protected $_resourceModel;
	protected $_template = 'importer/view.phtml';


	public function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		\Dotdigitalgroup\Email\Model\Resource\importer $resourceModel,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_resourceModel = $resourceModel;
	}

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->addData(
			[
				\Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'adminhtml_importer',
				\Magento\Backend\Block\Widget\Grid\Container::PARAM_BLOCK_GROUP => 'Dotdigitalgroup_Email',
				\Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => __('Importer'),
			]
		);
		parent::_construct();
	}
	/**
	 * Prepare button and gridCreate Grid , edit/add grid row and installer in Magento2
	 *
	 * @return \Magento\Catalog\Block\Adminhtml\Product
	 */
	protected function _prepareLayout()
	{

		$this->setChild(
			'grid',
			$this->getLayout()->createBlock('Dotdigitalgroup\Email\Block\Adminhtml\Importer\Grid', 'importer.view.grid')
		);
		return parent::_prepareLayout();
	}

	/**
	 *
	 *
	 * @return array
	 */
	protected function _getAddButtonOptions()
	{

		$splitButtonOptions[] = [
			'label' => __('Add New'),
			'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
		];

		return $splitButtonOptions;
	}

	/**
	 *
	 *
	 * @param string $type
	 * @return string
	 */
	protected function _getCreateUrl()
	{
		return $this->getUrl(
			'*/*/new'
		);
	}

	/**
	 * Render grid
	 *
	 * @return string
	 */
	public function getGridHtml()
	{
		return $this->getChildHtml('grid');
	}
}

