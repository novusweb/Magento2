<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service\V1;

/**
 * Class CustomerCurrentService
 */
class CustomerCurrentService implements \Magento\Customer\Service\V1\CustomerCurrentServiceInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Customer\Service\V1\Dto\CustomerBuilder
     */
    protected $customerBuilder;

    /**
     * @var \Magento\Customer\Service\V1\CustomerService
     */
    protected $customerService;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\View\LayoutInterface $layout
     * @param Dto\CustomerBuilder $customerBuilder
     * @param CustomerServiceInterface $customerService
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Module\Manager $moduleManager
     * @param \Magento\App\ViewInterface $view
     */
    public function __construct(
        \Magento\Customer\Model\Session                         $customerSession,
        \Magento\View\LayoutInterface                           $layout,
        \Magento\Customer\Service\V1\Dto\CustomerBuilder        $customerBuilder,
        \Magento\Customer\Service\V1\CustomerServiceInterface   $customerService,
        \Magento\App\RequestInterface                           $request,
        \Magento\Module\Manager                                 $moduleManager,
        \Magento\App\ViewInterface                              $view
    ) {
        $this->customerSession  = $customerSession;
        $this->layout           = $layout;
        $this->customerBuilder  = $customerBuilder;
        $this->customerService  = $customerService;
        $this->request          = $request;
        $this->moduleManager    = $moduleManager;
        $this->view             = $view;
    }

    /**
     * Returns customer Dto with customer group only
     *
     * @return Dto\Customer
     */
    protected function getDepersonalizedCustomer()
    {
        return $this->customerBuilder->setGroupId($this->customerSession->getCustomerGroupId())->create();
    }

    /**
     * Returns customer Dto from service
     *
     * @return Dto\Customer
     */
    protected function getCustomerFromService()
    {
        return $this->customerService->getCustomer($this->customerSession->getId());
    }

    /**
     * Returns current customer according to session and context
     *
     * @return Dto\Customer
     */
    public function getCustomer()
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && !$this->request->isAjax()
            && $this->view->isLayoutLoaded()
            && $this->layout->isCacheable()
        ) {
            return $this->getDepersonalizedCustomer();
        } else {
            return $this->getCustomerFromService();
        }
    }

    /**
     * Returns customer id from session
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->customerSession->getId();
    }
}
