<?php

namespace Dotdigitalgroup\Email\Controller\Adminhtml\Addressbook;

class Save extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    private $helperData;

    /**
     * Save constructor.
     *
     * @param \Dotdigitalgroup\Email\Helper\Data $data
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Dotdigitalgroup\Email\Helper\Data $data,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->helperData     = $data;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($context);
    }

    /**
     * Execute method.
     */
    public function execute()
    {
        $addressBookName = $this->getRequest()->getParam('name');
        $visibility = $this->getRequest()->getParam('visibility');
        $website = (int) $this->getRequest()->getParam('website', 0);

        if ($this->helperData->isEnabled($website)) {
            $client = $this->helperData->getWebsiteApiClient($website);
            if (! empty($addressBookName)) {
                $response = $client->postAddressBooks($addressBookName, $visibility);
                if (isset($response->message)) {
                    $this->messageManager->addErrorMessage($response->message);
                } else {
                    $this->messageManager->addSuccessMessage('Address book successfully created.');
                }
            }
        }
    }


    /**
     * Check the permission to run it.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dotdigitalgroup_Email::config');
    }
}
