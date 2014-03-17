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

namespace Magento\Theme\Block\Html\Head;
use Magento\Theme\Block\Html\Head\AssetBlockInterface;

/**
 * Script page block
 */
class Script extends \Magento\View\Element\AbstractBlock implements AssetBlockInterface
{
    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\View\Asset\ViewFileFactory $viewFileFactory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\View\Asset\ViewFileFactory $viewFileFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->setAsset(
            $viewFileFactory->create(array(
                'file' => (string)$this->getFile(),
                'contentType' => \Magento\View\Publisher::CONTENT_TYPE_JS
            ))
        );
    }

    /**
     * Get block asset
     *
     * @return \Magento\View\Asset\AssetInterface
     */
    public function getAsset()
    {
        return $this->getData('asset');
    }
}
