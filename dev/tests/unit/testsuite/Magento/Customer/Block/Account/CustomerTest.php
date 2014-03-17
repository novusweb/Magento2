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

namespace Magento\Customer\Block\Account;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCustomerName()
    {
        $customerName = 'John Doe';

        $sessionMock = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $customer = $this->getMockBuilder('Magento\Customer\Service\V1\Dto\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $customerServiceMock = $this->getMockBuilder('\Magento\Customer\Service\V1\CustomerServiceInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $customerServiceMock->expects($this->any())->method('getCustomer')->will($this->returnValue($customer));

        $viewHelperMock = $this->getMockBuilder('Magento\Customer\Helper\View')
            ->disableOriginalConstructor()
            ->getMock();
        $viewHelperMock->expects($this->any())->method('getCustomerName')->will($this->returnValue($customerName));

        $escaperMock = $this->getMockBuilder('Magento\Escaper')
            ->disableOriginalConstructor()
            ->getMock();
        $escaperMock->expects($this->any())->method('escapeHtml')->with($customerName)
            ->will($this->returnValue($customerName));

        $contextMock = $this->getMockBuilder('Magento\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getEscaper')->will($this->returnValue($escaperMock));

        $block = new \Magento\Customer\Block\Account\Customer($contextMock, $sessionMock, $customerServiceMock,
            $viewHelperMock);

        $this->assertSame($customerName, $block->getCustomerName());
    }
}
