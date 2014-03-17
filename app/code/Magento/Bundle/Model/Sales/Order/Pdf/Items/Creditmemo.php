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
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

/**
 * Sales Order Creditmemo Pdf default items renderer
 */
class Creditmemo extends \Magento\Bundle\Model\Sales\Order\Pdf\Items\AbstractItems
{
    /**
     * Core string
     *
     * @var \Magento\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Filter\FilterManager $filterManager
     * @param \Magento\Stdlib\String $string
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\App\Filesystem $filesystem,
        \Magento\Filter\FilterManager $filterManager,
        \Magento\Stdlib\String $string,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->string = $string;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Draw item line
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();

        $items = $this->getChilds($item);
        $prevOptionId = '';
        $drawItems  = array();
        $leftBound  = 35;
        $rightBound = 565;

        foreach ($items as $childItem) {
            $x      = $leftBound;
            $line   = array();

            $attributes = $this->getSelectionAttributes($childItem);
            if (is_array($attributes)) {
                $optionId = $attributes['option_id'];
            } else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = array(
                    'lines'  => array(),
                    'height' => 15
                );
            }

            // draw selection attributes
            if ($childItem->getOrderItem()->getParentItem()) {
                if ($prevOptionId != $attributes['option_id']) {
                    $line[0] = array(
                        'font'  => 'italic',
                        'text'  => $this->string->split($attributes['option_label'], 38, true, true),
                        'feed'  => $x
                    );

                    $drawItems[$optionId] = array(
                        'lines'  => array($line),
                        'height' => 15
                    );

                    $line = array();
                    $prevOptionId = $attributes['option_id'];
                }
            }

            // draw product titles
            if ($childItem->getOrderItem()->getParentItem()) {
                $feed = $x + 5;
                $name = $this->getValueHtml($childItem);
            } else {
                $feed = $x;
                $name = $childItem->getName();
            }

            $line[] = array(
                'text'  => $this->string->split($name, 35, true, true),
                'feed'  => $feed
            );

            $x += 220;

            // draw SKUs
            if (!$childItem->getOrderItem()->getParentItem()) {
                $text = array();
                foreach ($this->string->split($item->getSku(), 17) as $part) {
                    $text[] = $part;
                }
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $x
                );
            }

            $x += 100;

            // draw prices
            if ($this->canShowPriceInfo($childItem)) {
                // draw Total(ex)
                $text = $order->formatPriceTxt($childItem->getRowTotal());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $x,
                    'font'  => 'bold',
                    'align' => 'right',
                    'width' => 50
                );
                $x += 50;

                // draw Discount
                $text = $order->formatPriceTxt(-$childItem->getDiscountAmount());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $x,
                    'font'  => 'bold',
                    'align' => 'right',
                    'width' => 50
                );
                $x += 50;

                // draw QTY
                $text = $childItem->getQty() * 1;
                $line[] = array(
                    'text'  => $childItem->getQty() * 1,
                    'feed'  => $x,
                    'font'  => 'bold',
                    'align' => 'center',
                    'width' => 30
                );
                $x += 30;

                // draw Tax
                $text = $order->formatPriceTxt($childItem->getTaxAmount());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $x,
                    'font'  => 'bold',
                    'align' => 'right',
                    'width' => 45
                );
                $x += 45;

                // draw Total(inc)
                $text = $order->formatPriceTxt(
                    $childItem->getRowTotal() + $childItem->getTaxAmount() - $childItem->getDiscountAmount()
                );
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $rightBound,
                    'font'  => 'bold',
                    'align' => 'right',
                );
            }

            $drawItems[$optionId]['lines'][] = $line;

        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = array();
                    $lines[][] = array(
                        'text'  => $this->string->split(
                            $this->filterManager->stripTags($option['label']),
                            40,
                            true,
                            true
                        ),
                        'font'  => 'italic',
                        'feed'  => $leftBound
                    );

                    if ($option['value']) {
                        $text = array();
                        $printValue = isset($option['print_value'])
                            ? $option['print_value']
                            : $this->filterManager->stripTags($option['value']);
                        $values = explode(', ', $printValue);
                        foreach ($values as $value) {
                            foreach ($this->string->split($value, 30, true, true) as $subValue) {
                                $text[] = $subValue;
                            }
                        }

                        $lines[][] = array(
                            'text'  => $text,
                            'feed'  => $leftBound + 5
                        );
                    }

                    $drawItems[] = array(
                        'lines'  => $lines,
                        'height' => 15
                    );
                }
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, array('table_header' => true));
        $this->setPage($page);
    }
}
