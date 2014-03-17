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

namespace Magento\Paypal\Block\PayflowExpress;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paypalConfig;

    /**
     * @var Form
     */
    protected $_model;

    protected function setUp()
    {
        $this->_paypalConfig = $this->getMock('Magento\Paypal\Model\Config', [], [], '', false);
        $this->_paypalConfig->expects($this->once())
            ->method('setMethod')
            ->will($this->returnSelf());
        $paypalConfigFactory = $this->getMock('Magento\Paypal\Model\ConfigFactory', ['create'], [], '', false);
        $paypalConfigFactory->expects($this->once())->method('create')->will($this->returnValue($this->_paypalConfig));
        $mark = $this->getMock('Magento\View\Element\Template', [], [], '', false);
        $mark->expects($this->once())->method('setTemplate')->will($this->returnSelf());
        $mark->expects($this->any())->method('__call')->will($this->returnSelf());
        $layout = $this->getMockForAbstractClass('Magento\View\LayoutInterface');
        $layout->expects($this->once())
            ->method('createBlock')
            ->with('Magento\View\Element\Template')
            ->will($this->returnValue($mark));
        $localeResolver = $this->getMock('Magento\Locale\ResolverInterface', array(), array(), '', false, false);
        $appMock = $this->getMock('\Magento\Core\Model\App', array('getLocaleResolver'), array(), '', false);
        $appMock->expects($this->any())
            ->method('getLocaleResolver')
            ->will($this->returnValue($localeResolver));
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Paypal\Block\PayflowExpress\Form',
            [
                'paypalConfigFactory' => $paypalConfigFactory,
                'layout' => $layout,
                'app' => $appMock,
            ]
        );
    }

    public function testGetBillingAgreementCode()
    {
        $this->assertFalse($this->_model->getBillingAgreementCode());
    }
}
