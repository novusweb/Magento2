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
 * @package     Magento_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\DesignEditor\Controller\Varien\Router;

class StandardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test area code
     */
    const AREA_CODE = 'frontend';

    /**
     * Test VDE front name prefix
     */
    const VDE_FRONT_NAME = 'test_front_name/test_mode_type/test_theme_id';

    /**
     * Test VDE configuration data
     */
    const VDE_CONFIGURATION_DATA = 'vde_config_data';

    /**
     * Test path and host
     */
    const TEST_PATH = '/customer/account';
    const TEST_HOST = 'http://test.domain';

    /**
     * @var \Magento\DesignEditor\Controller\Varien\Router\Standard
     */
    protected $_model;

    /**
     * @param \Magento\App\RequestInterface $request
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @param array $routers
     * @param string|null $matchedValue
     *
     * @dataProvider matchDataProvider
     */
    public function testMatch(
        \Magento\App\RequestInterface $request,
        $isVde,
        $isLoggedIn,
        array $routers = array(),
        $matchedValue = null
    ) {
        $this->_model = $this->_prepareMocksForTestMatch($request, $isVde, $isLoggedIn, $routers);

        $this->assertEquals($matchedValue, $this->_model->match($request));
        if ($isVde && $isLoggedIn) {
            $this->assertEquals(self::TEST_PATH, $request->getPathInfo());
        }
    }

    /**
     * Data provider for testMatch
     *
     * @return array
     */
    public function matchDataProvider()
    {
        $vdeUrl    = self::TEST_HOST . '/' . self::VDE_FRONT_NAME . self::TEST_PATH;
        $notVdeUrl = self::TEST_HOST . self::TEST_PATH;

        $excludedRouters = array(
            'admin' => 'admin router',
            'vde'   => 'vde router',
        );

        $routerListMock = $this->getMock('\Magento\App\Route\ConfigInterface');

        $infoProcessorMock = $this->getMock('Magento\App\Request\PathInfoProcessorInterface');
        $infoProcessorMock->expects($this->any())->method('process')->will($this->returnArgument(1));

        // test data to verify routers match logic
        $matchedRequest = $this->getMock('Magento\App\Request\Http',
            array('_isFrontArea'),
            array($routerListMock, $infoProcessorMock, $vdeUrl)
        );

        $matchedController = $this->getMockForAbstractClass(
            'Magento\App\Action\AbstractAction', array(), '', false);

        // method "match" will be invoked for this router because it's first in the list
        $matchedRouter = $this->getMock(
            'Magento\Core\App\Router\Base', array(), array(), '', false
        );
        $matchedRouter->expects($this->once())
            ->method('match')
            ->with($matchedRequest)
            ->will($this->returnValue($matchedController));

        // method "match" will not be invoked for this router because controller will be found by first router
        $notMatchedRouter = $this->getMock(
            'Magento\Core\App\Router\Base', array(), array(), '', false
        );
        $notMatchedRouter->expects($this->never())
            ->method('match');

        $matchedRouters = array_merge($excludedRouters,
            array('matched' => $matchedRouter, 'not_matched' => $notMatchedRouter)
        );

        $infoProcessorMock = $this->getMock('Magento\App\Request\PathInfoProcessorInterface');
        $infoProcessorMock->expects($this->any())->method('process')->will($this->returnArgument(1));

        return array(
            'not vde request' => array(
                '$request' => $this->getMock(
                    'Magento\App\Request\Http', array('_isFrontArea'), array(
                        $routerListMock, $infoProcessorMock, $notVdeUrl
                    )
                ),
                '$isVde'           => false,
                '$isLoggedIn'      => true,
            ),
            'not logged as admin' => array(
                '$request' => $this->getMock(
                        'Magento\App\Request\Http',
                        array('_isFrontArea'),
                        array($routerListMock, $infoProcessorMock, $vdeUrl)
                    ),
                '$isVde'           => true,
                '$isLoggedIn'      => false,
            ),
            'no matched routers' => array(
                '$request' => $this->getMock(
                        'Magento\App\Request\Http',
                        array('_isFrontArea'),
                        array($routerListMock, $infoProcessorMock, $vdeUrl)
                    ),
                '$isVde'           => true,
                '$isLoggedIn'      => true,
                '$routers'         => $excludedRouters
            ),
            'matched routers' => array(
                '$request'         => $matchedRequest,
                '$isVde'           => true,
                '$isLoggedIn'      => true,
                '$routers'         => $matchedRouters,
                '$matchedValue'    => $matchedController,
            ),
        );
    }

    /**
     * @param \Magento\App\RequestInterface $request
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @param array $routers
     * @return \Magento\DesignEditor\Controller\Varien\Router\Standard
     */
    protected function _prepareMocksForTestMatch(
        \Magento\App\RequestInterface $request,
        $isVde,
        $isLoggedIn,
        array $routers
    ) {
        // default mocks - not affected on method functionality
        $objectManager      = $this->getMock('Magento\ObjectManager');
        $helperMock         = $this->_getHelperMock($isVde);
        $backendSessionMock = $this->_getBackendSessionMock($isVde, $isLoggedIn);
        $stateMock          = $this->_getStateModelMock($routers);

        $callback = function ($name) use ($helperMock, $backendSessionMock, $stateMock) {
            switch ($name) {
                case 'Magento\DesignEditor\Helper\Data':
                    return $helperMock;
                case 'Magento\Backend\Model\Auth\Session':
                    return $backendSessionMock;
                case 'Magento\DesignEditor\Model\State':
                    return $stateMock;
                default:
                    return null;
            }
        };

        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnCallback($callback));

        $rewriteServiceMock = $this->getMock('Magento\Core\App\Request\RewriteService', array(), array(), '', false);
        $routerListMock = $this->getMock('Magento\App\RouterListInterface',
            array(
                'getRouters',
                'current',
                'next',
                'key',
                'valid',
                'rewind'
            )
        );

        if ($isVde && $isLoggedIn) {
            $rewriteServiceMock->expects($this->once())
                ->method('applyRewrites')
                ->with($request);
            $routerListMock->expects($this->once())
                ->method('getRouters')
                ->will($this->returnValue($routers));
        }
        $arguments = array(
            'routerId' => 'frontend',
            'routerList' => $routerListMock,
            'objectManager' => $objectManager,
            'urlRewriteService' => $rewriteServiceMock
        );
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $router = $helper->getObject('\Magento\DesignEditor\Controller\Varien\Router\Standard', $arguments);
        return $router;
    }

    /**
     * @param bool $isVde
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getHelperMock($isVde)
    {
        $helper = $this->getMock('Magento\DesignEditor\Helper\Data', array('isVdeRequest'), array(), '', false);
        $helper->expects($this->once())
            ->method('isVdeRequest')
            ->will($this->returnValue($isVde));
        return $helper;
    }

    /**
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getBackendSessionMock($isVde, $isLoggedIn)
    {
        $backendSession = $this->getMock('Magento\Backend\Model\Auth\Session', array(), array(), '', false);
        $backendSession->expects($isVde ? $this->once() : $this->never())
            ->method('isLoggedIn')
            ->will($this->returnValue($isLoggedIn));

        return $backendSession;
    }

    /**
     * @param array $routers
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getStateModelMock(array $routers)
    {
        $stateModel = $this->getMock('Magento\DesignEditor\Model\State', array(), array(), '', false);

        if (array_key_exists('matched', $routers)) {
            $stateModel->expects($this->once())
                ->method('update')
                ->with(self::AREA_CODE);
        }

        return $stateModel;
    }
}
