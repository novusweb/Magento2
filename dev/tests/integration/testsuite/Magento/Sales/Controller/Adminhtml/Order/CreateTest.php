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
 * @category    Magento
 * @package     Magento_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * @magentoAppArea adminhtml
 */
class CreateTest extends \Magento\Backend\Utility\Controller
{
    public function testLoadBlockAction()
    {
        $this->getRequest()->setParam('block', ',');
        $this->getRequest()->setParam('json', 1);
        $this->dispatch('backend/sales/order_create/loadBlock');
        $this->assertEquals('{"message":""}', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testLoadBlockActionData()
    {
        $this->_objectManager->get('Magento\Sales\Model\AdminOrder\Create')
            ->addProducts(array(1 => array('qty' => 1)));
        $this->getRequest()->setParam('block', 'data');
        $this->getRequest()->setParam('json', 1);
        $this->dispatch('backend/sales/order_create/loadBlock');
        $html = $this->getResponse()->getBody();
        $this->assertContains('<div id=\"sales_order_create_search_grid\">', $html);
        $this->assertContains('<div id=\"order-billing_method_form\">', $html);
        $this->assertContains('id=\"shipping-method-overlay\"', $html);
        $this->assertContains('id=\"coupons:code\"', $html);
    }

    /**
     * @dataProvider loadBlockActionsDataProvider
     */
    public function testLoadBlockActions($block, $expected)
    {
        $this->getRequest()->setParam('block', $block);
        $this->getRequest()->setParam('json', 1);
        $this->dispatch('backend/sales/order_create/loadBlock');
        $html = $this->getResponse()->getBody();
        $this->assertContains($expected, $html);
    }

    public function loadBlockActionsDataProvider()
    {
        return array(
            'shipping_method' => array('shipping_method', 'id=\"shipping-method-overlay\"'),
            'billing_method' => array('billing_method', '<div id=\"order-billing_method_form\">'),
            'newsletter' => array('newsletter', 'name=\"newsletter:subscribe\"'),
            'search' => array('search', '<div id=\"sales_order_create_search_grid\">'),
            'search_grid' => array('search', '<div id=\"sales_order_create_search_grid\">'),
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testLoadBlockActionItems()
    {
        $this->_objectManager->get('Magento\Sales\Model\AdminOrder\Create')
            ->addProducts(array(1 => array('qty' => 1)));
        $this->getRequest()->setParam('block', 'items');
        $this->getRequest()->setParam('json', 1);
        $this->dispatch('backend/sales/order_create/loadBlock');
        $html = $this->getResponse()->getBody();
        $this->assertContains('id=\"coupons:code\"', $html);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea adminhtml
     */
    public function testIndexAction()
    {
        /** @var $order \Magento\Sales\Model\AdminOrder\Create */
        $order = $this->_objectManager->get('Magento\Sales\Model\AdminOrder\Create');
        $order->addProducts(array(1 => array('qty' => 1)));
        $this->dispatch('backend/sales/order_create/index');
        $html = $this->getResponse()->getBody();
        $this->assertContains('<div id="order-customer-selector"', $html);
        $this->assertContains('<div id="sales_order_create_customer_grid">', $html);
        $this->assertContains('<div id="order-billing_method_form">', $html);
        $this->assertContains('id="shipping-method-overlay"', $html);
        $this->assertContains('<div id="sales_order_create_search_grid">', $html);
        $this->assertContains('id="coupons:code"', $html);
    }

    /**
     * @param string $actionName
     * @param boolean $reordered
     * @param string $expectedResult
     *
     * @dataProvider getAclResourceDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetAclResource($actionName, $reordered, $expectedResult)
    {
        $this->_objectManager->get('Magento\Backend\Model\Session\Quote')->setReordered($reordered);
        $orderController = $this->_objectManager->get('\Magento\Sales\Controller\Adminhtml\Order\Create');

        $this->getRequest()->setActionName($actionName);

        $method = new \ReflectionMethod('\Magento\Sales\Controller\Adminhtml\Order\Create', '_getAclResource');
        $method->setAccessible(true);
        $result = $method->invoke($orderController);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function getAclResourceDataProvider()
    {
        return array(
            array('index', false, 'Magento_Sales::create'),
            array('index', true, 'Magento_Sales::reorder'),
            array('save', false, 'Magento_Sales::create'),
            array('save', true, 'Magento_Sales::reorder'),
            array('reorder', false, 'Magento_Sales::reorder'),
            array('reorder', true, 'Magento_Sales::reorder'),
            array('cancel', false, 'Magento_Sales::cancel'),
            array('cancel', true, 'Magento_Sales::reorder'),
            array('', false, 'Magento_Sales::actions'),
            array('', true, 'Magento_Sales::actions'),
        );
    }
}
