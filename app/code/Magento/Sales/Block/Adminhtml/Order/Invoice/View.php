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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml invoice create
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Block\Adminhtml\Order\Invoice;

class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Admin session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendSession
     * @param \Magento\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Auth\Session $backendSession,
        \Magento\Registry $registry,
        array $data = array()
    ) {
        $this->_backendSession = $backendSession;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId    = 'invoice_id';
        $this->_controller  = 'adminhtml_order_invoice';
        $this->_mode        = 'view';
        $this->_session = $this->_backendSession;

        parent::_construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');

        if (!$this->getInvoice()) {
            return;
        }

        if ($this->_isAllowedAction('Magento_Sales::cancel') && $this->getInvoice()->canCancel() && !$this->_isPaymentReview()) {
            $this->_addButton('cancel', array(
                'label'     => __('Cancel'),
                'class'     => 'delete',
                'onclick'   => 'setLocation(\''.$this->getCancelUrl().'\')'
                )
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::emails')) {
            $this->addButton('send_notification', array(
                'label'     => __('Send Email'),
                'onclick'   => 'confirmSetLocation(\''
                . __('Are you sure you want to send an Invoice email to customer?')
                . '\', \'' . $this->getEmailUrl() . '\')'
            ));
        }

        $orderPayment = $this->getInvoice()->getOrder()->getPayment();

        if ($this->_isAllowedAction('Magento_Sales::creditmemo') && $this->getInvoice()->getOrder()->canCreditmemo()) {
            if (($orderPayment->canRefundPartialPerInvoice()
                && $this->getInvoice()->canRefund()
                && $orderPayment->getAmountPaid() > $orderPayment->getAmountRefunded())
                || ($orderPayment->canRefund() && !$this->getInvoice()->getIsUsedForRefund())
            ) {
                $this->_addButton('capture', array( // capture?
                    'label'     => __('Credit Memo'),
                    'class'     => 'go',
                    'onclick'   => 'setLocation(\''.$this->getCreditMemoUrl().'\')'
                    )
                );
            }
        }

        if ($this->_isAllowedAction('Magento_Sales::capture') && $this->getInvoice()->canCapture() && !$this->_isPaymentReview()) {
            $this->_addButton('capture', array(
                'label'     => __('Capture'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getCaptureUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->canVoid()) {
            $this->_addButton('void', array(
                'label'     => __('Void'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getVoidUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->getId()) {
            $this->_addButton('print', array(
                'label'     => __('Print'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
                )
            );
        }
    }

    /**
     * Check whether order is under payment review
     *
     * @return bool
     */
    protected function _isPaymentReview()
    {
        $order = $this->getInvoice()->getOrder();
        return $order->canReviewPayment() || $order->canFetchPaymentReviewUpdate();
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    public function getHeaderText()
    {
        if ($this->getInvoice()->getEmailSent()) {
            $emailSent = __('the invoice email was sent');
        } else {
            $emailSent = __('the invoice email is not sent');
        }
        return __('Invoice #%1 | %2 | %4 (%3)', $this->getInvoice()->getIncrementId(), $this->getInvoice()->getStateName(), $emailSent, $this->formatDate($this->getInvoice()->getCreatedAtDate(), 'medium', true));
    }

    public function getBackUrl()
    {
        return $this->getUrl(
            'sales/order/view',
            array(
                'order_id'  => $this->getInvoice() ? $this->getInvoice()->getOrderId() : null,
                'active_tab'=> 'order_invoices'
            ));
    }

    public function getCaptureUrl()
    {
        return $this->getUrl('sales/*/capture', array('invoice_id'=>$this->getInvoice()->getId()));
    }

    public function getVoidUrl()
    {
        return $this->getUrl('sales/*/void', array('invoice_id'=>$this->getInvoice()->getId()));
    }

    public function getCancelUrl()
    {
        return $this->getUrl('sales/*/cancel', array('invoice_id'=>$this->getInvoice()->getId()));
    }

    public function getEmailUrl()
    {
        return $this->getUrl('sales/*/email', array(
            'order_id'  => $this->getInvoice()->getOrder()->getId(),
            'invoice_id'=> $this->getInvoice()->getId(),
        ));
    }

    public function getCreditMemoUrl()
    {
        return $this->getUrl('sales/order_creditmemo/start', array(
            'order_id'  => $this->getInvoice()->getOrder()->getId(),
            'invoice_id'=> $this->getInvoice()->getId(),
        ));
    }

    public function getPrintUrl()
    {
        return $this->getUrl('sales/*/print', array(
            'invoice_id' => $this->getInvoice()->getId()
        ));
    }

    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getInvoice()->getBackUrl()) {
                return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getInvoice()->getBackUrl() . '\')');
            }
            return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('sales/invoice/') . '\')');
        }
        return $this;
    }

    /**
     * Check whether is allowed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
