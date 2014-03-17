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

namespace Magento\Sales\Block\Reorder;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Reorder\Sidebar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    protected function setUp()
    {
        $this->block = $this->getMock('Magento\Sales\Block\Reorder\Sidebar', array('getItems'), array(), '', false);
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = array('catalog_product_1');

        $product = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $product->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($productTags));

        $item = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Item',
            array('getProduct', '__wakeup'),
            array(),
            '',
            false
        );
        $item->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $this->block->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array($item)));

        $this->assertEquals(
            $productTags,
            $this->block->getIdentities()
        );
    }
}
