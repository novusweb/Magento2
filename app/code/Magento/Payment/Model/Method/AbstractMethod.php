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
namespace Magento\Payment\Model\Method;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;

/**
 * Payment method abstract model
 */
abstract class AbstractMethod extends \Magento\Object implements \Magento\Payment\Model\MethodInterface
{
    const ACTION_ORDER             = 'order';
    const ACTION_AUTHORIZE         = 'authorize';
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    const STATUS_UNKNOWN    = 'UNKNOWN';
    const STATUS_APPROVED   = 'APPROVED';
    const STATUS_ERROR      = 'ERROR';
    const STATUS_DECLINED   = 'DECLINED';
    const STATUS_VOID       = 'VOID';
    const STATUS_SUCCESS    = 'SUCCESS';

    /**
     * Bit masks to specify different payment method checks.
     * @see \Magento\Payment\Model\Method\AbstractMethod::isApplicableToQuote
     */
    const CHECK_USE_FOR_COUNTRY       = 1;
    const CHECK_USE_FOR_CURRENCY      = 2;
    const CHECK_USE_CHECKOUT          = 4;
    const CHECK_USE_INTERNAL          = 16;
    const CHECK_ORDER_TOTAL_MIN_MAX   = 32;
    const CHECK_ZERO_TOTAL            = 64;

    /**
     * @var string
     */
    protected $_code;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Payment\Block\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway                   = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canOrder                    = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize                = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture                  = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapturePartial           = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund                   = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial     = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canVoid                     = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal              = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseCheckout              = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded          = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo     = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canReviewPayment            = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canManageRecurringProfiles  = true;

    /**
     * TODO: whether a captured transaction may be voided by this gateway
     * This may happen when amount is captured, but not settled
     * @var bool
     */
    protected $_canCancelInvoice        = false;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array();

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Log adapter factory
     *
     * @var \Magento\Logger\AdapterFactory
     */
    protected $_logAdapterFactory;

    /**
     * Construct
     *
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\Logger\AdapterFactory $logAdapterFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\Logger\AdapterFactory $logAdapterFactory,
        array $data = array()
    ) {
        parent::__construct($data);
        $this->_eventManager = $eventManager;
        $this->_paymentData = $paymentData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_logAdapterFactory = $logAdapterFactory;
    }

    /**
     * Check order availability
     *
     * @return bool
     */
    public function canOrder()
    {
        return $this->_canOrder;
    }

    /**
     * Check authorise availability
     *
     * @return bool
     */
    public function canAuthorize()
    {
        return $this->_canAuthorize;
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        return $this->_canCapture;
    }

    /**
     * Check partial capture availability
     *
     * @return bool
     */
    public function canCapturePartial()
    {
        return $this->_canCapturePartial;
    }

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
        return $this->_canRefund;
    }

    /**
     * Check partial refund availability for invoice
     *
     * @return bool
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->_canRefundInvoicePartial;
    }

    /**
     * Check void availability
     *
     * @param   \Magento\Object $payment
     * @return  bool
     */
    public function canVoid(\Magento\Object $payment)
    {
        return $this->_canVoid;
    }

    /**
     * Using internal pages for input payment data
     * Can be used in admin
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return $this->_canUseInternal;
    }

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout()
    {
        return $this->_canUseCheckout;
    }

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     */
    public function canEdit()
    {
        return true;
    }

    /**
     * Check fetch transaction info availability
     *
     * @return bool
     */
    public function canFetchTransactionInfo()
    {
        return $this->_canFetchTransactionInfo;
    }

    /**
     * Fetch transaction info
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\Info $payment, $transactionId)
    {
        return array();
    }

    /**
     * Retrieve payment system relation flag
     *
     * @return bool
     */
    public function isGateway()
    {
        return $this->_isGateway;
    }

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     */
    public function isInitializeNeeded()
    {
        return $this->_isInitializeNeeded;
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        /*
        for specific country, the flag will set up as 1
        */
        if ($this->getConfigData('allowspecific') == 1) {
            $availableCountries = explode(',', $this->getConfigData('specificcountry'));
            if (!in_array($country, $availableCountries)) {
                return false;
            }

        }
        return true;
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return true;
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     * @throws \Magento\Core\Exception
     */
    public function getCode()
    {
        if (empty($this->_code)) {
            throw new \Magento\Core\Exception(__('We cannot retrieve the payment method code.'));
        }
        return $this->_code;
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType()
    {
        return $this->_formBlockType;
    }

    /**
     * Retrieve block type for display method information
     *
     * @return string
     */
    public function getInfoBlockType()
    {
        return $this->_infoBlockType;
    }

    /**
     * Retrieve payment iformation model object
     *
     * @return \Magento\Payment\Model\Info
     * @throws \Magento\Core\Exception
     */
    public function getInfoInstance()
    {
        $instance = $this->getData('info_instance');
        if (!($instance instanceof \Magento\Payment\Model\Info)) {
            throw new \Magento\Core\Exception(__('We cannot retrieve the payment information object instance.'));
        }
        return $instance;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Core\Exception
     */
    public function validate()
    {
         /**
          * to validate payment method is allowed for billing country or not
          */
         $paymentInfo = $this->getInfoInstance();
         if ($paymentInfo instanceof Payment) {
             $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
         } else {
             $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
         }
         if (!$this->canUseForCountry($billingCountry)) {
             throw new \Magento\Core\Exception(
                 __('You can\'t use the payment type you selected to make payments to the billing country.')
             );
         }
         return $this;
    }

    /**
     * Order payment abstract method
     *
     * @param \Magento\Object $payment
     * @param float $amount
     *
     * @return $this
     * @throws \Magento\Core\Exception
     */
    public function order(\Magento\Object $payment, $amount)
    {
        if (!$this->canOrder()) {
            throw new \Magento\Core\Exception(__('The order action is not available.'));
        }
        return $this;
    }

    /**
     * Authorize payment abstract method
     *
     * @param \Magento\Object $payment
     * @param float $amount
     *
     * @return $this
     * @throws \Magento\Core\Exception
     */
    public function authorize(\Magento\Object $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new \Magento\Core\Exception(__('The authorize action is not available.'));
        }
        return $this;
    }

    /**
     * Capture payment abstract method
     *
     * @param \Magento\Object $payment
     * @param float $amount
     *
     * @return $this
     * @throws \Magento\Core\Exception
     */
    public function capture(\Magento\Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new \Magento\Core\Exception(__('The capture action is not available.'));
        }

        return $this;
    }

    /**
     * Set capture transaction ID to invoice for informational purposes
     *
     * Candidate to be deprecated
     *
     * @param Invoice $invoice
     * @param Payment $payment
     * @return $this
     */
    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getLastTransId());
        return $this;
    }

    /**
     * Set refund transaction id to payment object for informational purposes
     * Candidate to be deprecated:
     * there can be multiple refunds per payment, thus payment.refund_transaction_id doesn't make big sense
     *
     * @param Invoice $invoice
     * @param Payment $payment
     * @return $this
     */
    public function processBeforeRefund($invoice, $payment)
    {
        $payment->setRefundTransactionId($invoice->getTransactionId());
        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param \Magento\Object $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Core\Exception
     */
    public function refund(\Magento\Object $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new \Magento\Core\Exception(__('The refund action is not available.'));
        }
        return $this;
    }

    /**
     * Set transaction ID into creditmemo for informational purposes
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param Payment $payment
     * @return $this
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        $creditmemo->setTransactionId($payment->getLastTransId());
        return $this;
    }

    /**
     * Cancel payment abstract method
     *
     * @param \Magento\Object $payment
     *
     * @return $this
     */
    public function cancel(\Magento\Object $payment)
    {
        return $this;
    }

    /**
     * Void payment abstract method
     *
     * @param \Magento\Object $payment
     * @return $this
     * @throws \Magento\Core\Exception
     */
    public function void(\Magento\Object $payment)
    {
        if (!$this->canVoid($payment)) {
            throw new \Magento\Core\Exception(__('Void action is not available.'));
        }
        return $this;
    }

    /**
     * Whether this method can accept or deny payment
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    public function canReviewPayment(\Magento\Payment\Model\Info $payment)
    {
        return $this->_canReviewPayment;
    }

    /**
     * Attempt to accept a payment that us under review
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return false
     * @throws \Magento\Core\Exception
     */
    public function acceptPayment(\Magento\Payment\Model\Info $payment)
    {
        if (!$this->canReviewPayment($payment)) {
            throw new \Magento\Core\Exception(__('The payment review action is unavailable.'));
        }
        return false;
    }

    /**
     * Attempt to deny a payment that us under review
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return false
     * @throws \Magento\Core\Exception
     */
    public function denyPayment(\Magento\Payment\Model\Info $payment)
    {
        if (!$this->canReviewPayment($payment)) {
            throw new \Magento\Core\Exception(__('The payment review action is unavailable.'));
        }
        return false;
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getConfigData('title');
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Core\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/' . $this->getCode() . '/' . $field;
        return $this->_coreStoreConfig->getConfig($path, $storeId);
    }

    /**
     * Assign data to info model instance
     *
     * @param array|\Magento\Object $data
     * @return $this
     */
    public function assignData($data)
    {
        if (is_array($data)) {
            $this->getInfoInstance()->addData($data);
        } elseif ($data instanceof \Magento\Object) {
            $this->getInfoInstance()->addData($data->getData());
        }
        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return $this
     */
    public function prepareSave()
    {
        return $this;
    }

    /**
     * Check whether payment method can be used
     *
     * TODO: payment method instance is not supposed to know about quote
     *
     * @param \Magento\Sales\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $checkResult = new \StdClass;
        $isActive = (bool)(int)$this->getConfigData('active', $quote ? $quote->getStoreId() : null);
        $checkResult->isAvailable = $isActive;
        $checkResult->isDeniedInConfig = !$isActive; // for future use in observers
        $this->_eventManager->dispatch('payment_method_is_active', array(
            'result'          => $checkResult,
            'method_instance' => $this,
            'quote'           => $quote,
        ));

        return $checkResult->isAvailable;
    }

    /**
     * Check whether payment method is applicable to quote
     * Purposed to allow use in controllers some logic that was implemented in blocks only before
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param int|null $checksBitMask
     * @return bool
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {
        if ($checksBitMask & self::CHECK_USE_FOR_COUNTRY) {
            if (!$this->canUseForCountry($quote->getBillingAddress()->getCountry())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_FOR_CURRENCY) {
            if (!$this->canUseForCurrency($quote->getStore()->getBaseCurrencyCode())) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_CHECKOUT) {
            if (!$this->canUseCheckout()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_USE_INTERNAL) {
            if (!$this->canUseInternal()) {
                return false;
            }
        }
        if ($checksBitMask & self::CHECK_ORDER_TOTAL_MIN_MAX) {
            $total = $quote->getBaseGrandTotal();
            $minTotal = $this->getConfigData('min_order_total');
            $maxTotal = $this->getConfigData('max_order_total');
            if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
                return false;
            }
        }

        if ($checksBitMask & self::CHECK_ZERO_TOTAL) {
            $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
            if ($total < 0.0001 && $this->getCode() != 'free') {
                return false;
            }
        }
        return true;
    }

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return $this
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return $this->getConfigData('payment_action');
    }

    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     * @return void
     */
    protected function _debug($debugData)
    {
        if ($this->getDebugFlag()) {
            $this->_logAdapterFactory
                ->create(array('fileName' => 'payment_' . $this->getCode() . '.log'))
               ->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
               ->log($debugData);
        }
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag()
    {
        return $this->getConfigData('debug');
    }

    /**
     * Used to call debug method from not Payment Method context
     *
     * @param mixed $debugData
     * @return void
     */
    public function debugData($debugData)
    {
        $this->_debug($debugData);
    }
}
