<?php

namespace Dotdigitalgroup\Email\Observer\Customer;

/**
 * Creates and updates the contact for customer. Monitor the email change for customer.
 */
class CreateUpdateContact implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Dotdigitalgroup\Email\Model\ContactFactory
     */
    private $contactFactory;
    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    private $wishlist;
    /**
     * @var \Dotdigitalgroup\Email\Model\ImporterFactory
     */
    private $importerFactory;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * CreateUpdateContact constructor.
     *
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlist
     * @param \Dotdigitalgroup\Email\Model\ContactFactory $contactFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Dotdigitalgroup\Email\Helper\Data $data
     * @param \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \Magento\Wishlist\Model\WishlistFactory $wishlist,
        \Dotdigitalgroup\Email\Model\ContactFactory $contactFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Registry $registry,
        \Dotdigitalgroup\Email\Helper\Data $data,
        \Dotdigitalgroup\Email\Model\ImporterFactory $importerFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->wishlist        = $wishlist;
        $this->contactFactory  = $contactFactory;
        $this->customerFactory = $customerFactory;
        $this->helper          = $data;
        $this->registry        = $registry;
        $this->importerFactory = $importerFactory;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * If it's configured to capture on shipment - do this.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $websiteId  = $customer->getWebsiteId();

        //check if enabled
        if (!$this->helper->isEnabled($websiteId)) {
            return $this;
        }

        $email      = $customer->getEmail();
        $customerId = $customer->getEntityId();
        $subscriber = $this->subscriberFactory->create()
            ->loadByCustomerId($customerId);
        $isSubscribed = $subscriber->isSubscribed();

        $emailBefore = '';

        try {
            // fix for a multiple hit of the observer
            $emailReg = $this->registry->registry($email . '_customer_save');
            if ($emailReg) {
                return $this;
            }
            $this->registry->register($email . '_customer_save', $email);

            $isContactExist = $this->contactFactory->create()
                ->loadByCustomerId($customerId);

            if ($isContactExist->getId()) {
                $emailBefore = $isContactExist->getEmail();
            }

            $emailAddress = empty($emailBefore) ? $email : $emailBefore;

            $contactModel = $this->contactFactory->create()
                ->loadByCustomerEmail($emailAddress, $websiteId);

            //email change detection
            if ($emailBefore && $email != $emailBefore) {
                //Reload contact model up update email
                $contactModel = $this->contactFactory->create()
                    ->loadByCustomerEmail($emailAddress, $websiteId);
                $contactModel->setEmail($email);

                $data = [
                    'emailBefore' => $emailBefore,
                    'email' => $email,
                    'isSubscribed' => $isSubscribed,
                ];
                $this->importerFactory->create()->registerQueue(
                    \Dotdigitalgroup\Email\Model\Importer::IMPORT_TYPE_CONTACT_UPDATE,
                    $data,
                    \Dotdigitalgroup\Email\Model\Importer::MODE_CONTACT_EMAIL_UPDATE,
                    $websiteId
                );
            } elseif (!$emailBefore) {
                //for new contacts update email
                $contactModel->setEmail($email);
            }
            $contactModel->setEmailImported(\Dotdigitalgroup\Email\Model\Contact::EMAIL_CONTACT_NOT_IMPORTED)
                ->setCustomerId($customerId);
            $contactModel->getResource()->save($contactModel);
        } catch (\Exception $e) {
            $this->helper->debug((string)$e, []);
        }

        return $this;
    }
}
