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
 * @category    Tools
 * @package     unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Tools\Migration\Acl\Db\Logger;

require_once realpath(__DIR__ . '/../../../../../../../../../../')
    . '/tools/Magento/Tools/Migration/Acl/Db/AbstractLogger.php';
require_once realpath(__DIR__ . '/../../../../../../../../../../')
    . '/tools/Magento/Tools/Migration/Acl/Db/Logger/Factory.php';
require_once realpath(__DIR__ . '/../../../../../../../../../../')
    . '/tools/Magento/Tools/Migration/Acl/Db/Logger/Console.php';
require_once realpath(__DIR__ . '/../../../../../../../../../../')
    . '/tools/Magento/Tools/Migration/Acl/Db/Logger/File.php';

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\Acl\Db\Logger\Factory
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Tools\Migration\Acl\Db\Logger\Factory();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @return array
     */
    public function getLoggerDataProvider()
    {
        return array(
            array('console', null),
            array('file', realpath(__DIR__ . '/../../../../../') . '/tmp') ,
        );
    }

    /**
     * @param string $loggerType
     * @param string $file
     * @dataProvider getLoggerDataProvider
     */
    public function testGetLogger($loggerType, $file)
    {
        $this->assertInstanceOf(
            'Magento\Tools\Migration\Acl\Db\AbstractLogger', $this->_model->getLogger($loggerType, $file));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetLoggerWithInvalidType()
    {
        $this->_model->getLogger('invalid type');
    }
}

