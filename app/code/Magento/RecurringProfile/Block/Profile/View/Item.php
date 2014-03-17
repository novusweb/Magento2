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

namespace Magento\RecurringProfile\Block\Profile\View;

/**
 * Recurring profile view item
 */
class Item extends \Magento\RecurringProfile\Block\Profile\View
{
    /**
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $_option;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Catalog\Model\Product $product,
        array $data = array()
    ) {
        $this->_option = $option;
        $this->_product = $product;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Prepare item info
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->_shouldRenderInfo = true;
        $key = 'order_item_info';

        foreach (array(
            'name' => __('Product Name'),
            'sku' => __('SKU'),
            'qty' => __('Quantity'),
        ) as $itemKey => $label) {
            $value = $this->_recurringProfile->getInfoValue($key, $itemKey);
            if ($value) {
                $this->_addInfo(array('label' => $label, 'value' => $value,));
            }
        }

        $request = $this->_recurringProfile->getInfoValue($key, 'info_buyRequest');
        if (empty($request)) {
            return;
        }

        $request = unserialize($request);
        if (empty($request['options'])) {
            return;
        }

        $options = $this->_option->getCollection()
            ->addIdsToFilter(array_keys($request['options']))
            ->addTitleToResult($this->_recurringProfile->getInfoValue($key, 'store_id'))
            ->addValuesToResult();

        foreach ($options as $option) {
            $this->_option->setId($option->getId());

            $group = $option->groupFactory($option->getType())
                ->setOption($option)
                ->setRequest(new \Magento\Object($request))
                ->setProduct($this->_product)
                ->setUseQuotePath(true)
                ->setQuoteItemOption($this->_option)
                ->validateUserValue($request['options']);

            $skipHtmlEscaping = false;
            if ('file' == $option->getType()) {
                $skipHtmlEscaping = true;

                $downloadParams = array(
                    'id'  => $this->_recurringProfile->getId(),
                    'option_id' => $option->getId(),
                    'key' => $request['options'][$option->getId()]['secret_key']
                );
                $group->setCustomOptionDownloadUrl('recurringProfile/download/downloadProfileCustomOption')
                    ->setCustomOptionUrlParams($downloadParams);
            }

            $optionValue = $group->prepareForCart();

            $this->_addInfo(array(
                'label' => $option->getTitle(),
                'value' => $group->getFormattedOptionValue($optionValue),
                'skip_html_escaping' => $skipHtmlEscaping
            ));
        }
    }
}
