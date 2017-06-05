<?php

namespace Dotdigitalgroup\Email\Observer\Customer;

/**
 * Register new wishlist automation.
 */
class RegisterWishlist implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Dotdigitalgroup\Email\Model\WishlistFactory
     */
    private $wishlistFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Dotdigitalgroup\Email\Model\AutomationFactory
     */
    private $automationFactory;
    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    private $wishlist;

    /**
     * RegisterWishlist constructor.
     *
     * @param \Magento\Wishlist\Model\WishlistFactory        $wishlist
     * @param \Dotdigitalgroup\Email\Model\AutomationFactory $automationFactory
     * @param \Magento\Customer\Model\CustomerFactory        $customerFactory
     * @param \Dotdigitalgroup\Email\Model\WishlistFactory   $wishlistFactory
     * @param \Dotdigitalgroup\Email\Helper\Data             $data
     * @param \Magento\Store\Model\StoreManagerInterface     $storeManagerInterface
     */
    public function __construct(
        \Magento\Wishlist\Model\WishlistFactory $wishlist,
        \Dotdigitalgroup\Email\Model\AutomationFactory $automationFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Dotdigitalgroup\Email\Model\WishlistFactory $wishlistFactory,
        \Dotdigitalgroup\Email\Helper\Data $data,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->wishlist          = $wishlist;
        $this->automationFactory = $automationFactory;
        $this->customerFactory   = $customerFactory;
        $this->wishlistFactory   = $wishlistFactory;
        $this->helper            = $data;
        $this->storeManager      = $storeManagerInterface;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //wishlist
        $wishlist = $observer->getEvent()->getObject()->getData();
        //required data for checking the new instance of wishlist with items in it.
        if (is_array($wishlist) && isset($wishlist['customer_id'])
            && isset($wishlist['wishlist_id'])
        ) {
            $wishlistModel = $this->wishlist->create();
            $wishlistModel = $wishlistModel->getResource()->load($wishlistModel, $wishlist['wishlist_id']);
            $itemsCount = $wishlistModel->getItemsCount();
            //wishlist items found
            if ($itemsCount) {
                //save wishlist info in the table
                $this->registerWishlist($wishlist);
            }
        }
    }

    /**
     * Automation new wishlist program.
     *
     * @param array $wishlist
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function registerWishlist($wishlist)
    {
        try {
            $emailWishlist = $this->wishlistFactory->create();
            $customer = $this->customerFactory->create();

            //if wishlist exist not to save again
            if (!$emailWishlist->getWishlist($wishlist['wishlist_id'])) {
                $customer = $customer->getResource()->load($customer, $wishlist['customer_id']);
                $email = $customer->getEmail();
                $wishlistId = $wishlist['wishlist_id'];
                $websiteId = $customer->getWebsiteId();
                $emailWishlist->setWishlistId($wishlistId)
                    ->setCustomerId($wishlist['customer_id'])
                    ->setStoreId($customer->getStoreId());
                $emailWishlist->getResource()->save($emailWishlist);

                $store
                           = $this->storeManager->getStore($customer->getStoreId());
                $storeName = $store->getName();

                //if api is not enabled
                if (!$this->helper->isEnabled($websiteId)) {
                    return $this;
                }
                $programId
                    = $this->helper->getWebsiteConfig(
                        'connector_automation/visitor_automation/wishlist_automation',
                        $websiteId
                    );
                //wishlist program mapped
                if ($programId) {
                    $automation = $this->automationFactory->create();
                    //save automation type
                    $automation->setEmail($email)
                        ->setAutomationType(\Dotdigitalgroup\Email\Model\Sync\Automation::AUTOMATION_TYPE_NEW_WISHLIST)
                        ->setEnrolmentStatus(\Dotdigitalgroup\Email\Model\Sync\Automation::AUTOMATION_STATUS_PENDING)
                        ->setTypeId($wishlistId)
                        ->setWebsiteId($websiteId)
                        ->setStoreName($storeName)
                        ->setProgramId($programId);
                    $automation->getResource()->save($automation);
                }
            }
        } catch (\Exception $e) {
            $this->helper->error((string)$e, []);
        }

        return $this;
    }
}
