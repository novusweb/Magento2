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
/** @var $this \Magento\Catalog\Model\Resource\Setup */
$this->startSetup();

/**
 * Create table 'recurring_profile'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('recurring_profile'))
    ->addColumn('profile_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Profile Id')
    ->addColumn('state', \Magento\DB\Ddl\Table::TYPE_TEXT, 20, array(
        'nullable'  => false,
    ), 'State')
    ->addColumn('customer_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
    ), 'Customer Id')
    ->addColumn('store_id', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
    ), 'Store Id')
    ->addColumn('method_code', \Magento\DB\Ddl\Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
    ), 'Method Code')
    ->addColumn('created_at', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
    ), 'Created At')
    ->addColumn('updated_at', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
    ), 'Updated At')
    ->addColumn('reference_id', \Magento\DB\Ddl\Table::TYPE_TEXT, 32, array(
    ), 'Reference Id')
    ->addColumn('subscriber_name', \Magento\DB\Ddl\Table::TYPE_TEXT, 150, array(
    ), 'Subscriber Name')
    ->addColumn('start_datetime', \Magento\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
    ), 'Start Datetime')
    ->addColumn('internal_reference_id', \Magento\DB\Ddl\Table::TYPE_TEXT, 42, array(
        'nullable'  => false,
    ), 'Internal Reference Id')
    ->addColumn('schedule_description', \Magento\DB\Ddl\Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
    ), 'Schedule Description')
    ->addColumn('suspension_threshold', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
    ), 'Suspension Threshold')
    ->addColumn('bill_failed_later', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Bill Failed Later')
    ->addColumn('period_unit', \Magento\DB\Ddl\Table::TYPE_TEXT, 20, array(
        'nullable'  => false,
    ), 'Period Unit')
    ->addColumn('period_frequency', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
    ), 'Period Frequency')
    ->addColumn('period_max_cycles', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
    ), 'Period Max Cycles')
    ->addColumn('billing_amount', \Magento\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
    ), 'Billing Amount')
    ->addColumn('trial_period_unit', \Magento\DB\Ddl\Table::TYPE_TEXT, 20, array(
    ), 'Trial Period Unit')
    ->addColumn('trial_period_frequency', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
    ), 'Trial Period Frequency')
    ->addColumn('trial_period_max_cycles', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
    ), 'Trial Period Max Cycles')
    ->addColumn('trial_billing_amount', \Magento\DB\Ddl\Table::TYPE_TEXT, null, array(
    ), 'Trial Billing Amount')
    ->addColumn('currency_code', \Magento\DB\Ddl\Table::TYPE_TEXT, 3, array(
        'nullable'  => false,
    ), 'Currency Code')
    ->addColumn('shipping_amount', \Magento\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
    ), 'Shipping Amount')
    ->addColumn('tax_amount', \Magento\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
    ), 'Tax Amount')
    ->addColumn('init_amount', \Magento\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
    ), 'Init Amount')
    ->addColumn('init_may_fail', \Magento\DB\Ddl\Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Init May Fail')
    ->addColumn('order_info', \Magento\DB\Ddl\Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
    ), 'Order Info')
    ->addColumn('order_item_info', \Magento\DB\Ddl\Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
    ), 'Order Item Info')
    ->addColumn('billing_address_info', \Magento\DB\Ddl\Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
    ), 'Billing Address Info')
    ->addColumn('shipping_address_info', \Magento\DB\Ddl\Table::TYPE_TEXT, '64k', array(
    ), 'Shipping Address Info')
    ->addColumn('profile_vendor_info', \Magento\DB\Ddl\Table::TYPE_TEXT, '64k', array(
    ), 'Profile Vendor Info')
    ->addColumn('additional_info', \Magento\DB\Ddl\Table::TYPE_TEXT, '64k', array(
    ), 'Additional Info')
    ->addIndex(
        $this->getIdxName(
            'recurring_profile',
            array('internal_reference_id'),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        array('internal_reference_id'), array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
    ->addIndex($this->getIdxName('recurring_profile', array('customer_id')),
        array('customer_id'))
    ->addIndex($this->getIdxName('recurring_profile', array('store_id')),
        array('store_id'))
    ->addForeignKey($this->getFkName('recurring_profile', 'customer_id', 'customer_entity', 'entity_id'),
        'customer_id', $this->getTable('customer_entity'), 'entity_id',
        \Magento\DB\Ddl\Table::ACTION_SET_NULL, \Magento\DB\Ddl\Table::ACTION_CASCADE)
    ->addForeignKey($this->getFkName('recurring_profile', 'store_id', 'core_store', 'store_id'),
        'store_id', $this->getTable('core_store'), 'store_id',
        \Magento\DB\Ddl\Table::ACTION_SET_NULL, \Magento\DB\Ddl\Table::ACTION_CASCADE)
    ->setComment('Sales Recurring Profile');
$this->getConnection()->createTable($table);

/**
 * Create table 'recurring_profile_order'
 */
$table = $this->getConnection()
    ->newTable($this->getTable('recurring_profile_order'))
    ->addColumn('link_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Link Id')
    ->addColumn('profile_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Profile Id')
    ->addColumn('order_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Order Id')
    ->addIndex(
        $this->getIdxName(
            'recurring_profile_order',
            array('profile_id', 'order_id'),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        array('profile_id', 'order_id'), array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
    ->addIndex($this->getIdxName('recurring_profile_order', array('order_id')),
        array('order_id'))
    ->addForeignKey(
        $this->getFkName(
            'recurring_profile_order',
            'order_id',
            'sales_flat_order',
            'entity_id'
        ),
        'order_id', $this->getTable('sales_flat_order'), 'entity_id',
        \Magento\DB\Ddl\Table::ACTION_CASCADE, \Magento\DB\Ddl\Table::ACTION_CASCADE)
    ->addForeignKey(
        $this->getFkName(
            'recurring_profile_order',
            'profile_id',
            'recurring_profile',
            'profile_id'
        ),
        'profile_id', $this->getTable('recurring_profile'), 'profile_id',
        \Magento\DB\Ddl\Table::ACTION_CASCADE, \Magento\DB\Ddl\Table::ACTION_CASCADE)
    ->setComment('Sales Recurring Profile Order');
$this->getConnection()->createTable($table);
