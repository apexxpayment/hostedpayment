<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Apexx\HostedPayment\Helper\Data as HostedPaymentHelper;
use Apexx\Base\Helper\Data as ApexxBaseHelper;
use Magento\Checkout\Model\Session As CheckoutSession;

/**
 * Class CaptureDataBuilder
 * @package Apexx\HostedPayment\Gateway\Request
 */
class CaptureDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var HostedPaymentHelper
     */
    protected  $hostedPaymentHelper;

    /**
     * @var ApexxBaseHelper
     */
    protected  $apexxBaseHelper;
    protected $checkoutSession;


    public function __construct(
        SubjectReader $subjectReader,
        HostedPaymentHelper $hostedPaymentHelper,
        ApexxBaseHelper $apexxBaseHelper,
        CheckoutSession $checkoutSession
    )
    {
        $this->subjectReader = $subjectReader;
        $this->hostedPaymentHelper = $hostedPaymentHelper;
        $this->apexxBaseHelper = $apexxBaseHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Create capture request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];

        $order = $paymentDO->getOrder();
        $delivery = $order->getShippingAddress();
        $amount = $buildSubject['amount']*100;
        $billing = $order->getBillingAddress();

        $payment = $paymentDO->getPayment();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }

        if($payment->getLastTransId())
        {
            $requestData = [
                "transactionId" => $payment->getParentTransactionId()
                    ?: $payment->getLastTransId(),
                "amount" => $amount,
                "capture_reference" => "Capture".$order->getOrderIncrementId()
            ];
        } else {
            $requestData= [
                //"account" => $this->apexxBaseHelper->getAccountId(),
                //"account" => '112ec893bb9d45cca5f521c750f97f5d',
                "organisation" => $this->apexxBaseHelper->getOrganizationId(),
                "currency" => $this->checkoutSession->getQuote()->getQuoteCurrencyCode(),
                "amount" => $amount,
                "capture_now" => $this->hostedPaymentHelper->getHostedPaymentAction(),
                "dynamic_descriptor" => $this->hostedPaymentHelper->getDynamicDescriptor(),
                "merchant_reference" => 'JOURNEYBOX'.$order->getOrderIncrementId(),
                "return_url" => $this->hostedPaymentHelper->getReturnUrl(),
                "webhook_transaction_update" => $this->hostedPaymentHelper->getWebhookUrl(),
                "transaction_type" => $this->hostedPaymentHelper->getTransType(),
                "locale" => $this->apexxBaseHelper->getStoreLocale(),
                "billing_address" => [
                    "first_name" => $billing->getFirstname(),
                    "last_name" => $billing->getLastname(),
                    "email" => $billing->getEmail(),
                    "address" => $billing->getStreetLine1().''.$billing->getStreetLine2(),
                    "city" => $billing->getCity(),
                    "state" => $billing->getRegionCode(),
                    "postal_code" => $billing->getPostcode(),
                    "country" => $billing->getCountryId(),
                    "phone" => $billing->getTelephone()
                ],
                "three_ds" => [
                    "three_ds_required" => $this->hostedPaymentHelper->getThreeDsRequired()
                ],
                "show_custom_fields" => [
                    "card_holder_name" => $this->hostedPaymentHelper->getCardHolderName(),
                    "address" => $this->hostedPaymentHelper->getCardAddress(),
                    "address_required" => $this->hostedPaymentHelper->getCardAddReq(),
                    "display_horizontal" => $this->hostedPaymentHelper->getDisplayHorizontal()
                ],
                "show_custom_labels" => [
                    "expiry_date" => $this->hostedPaymentHelper->getCardExpiryDate(),
                    "cvv" => $this->hostedPaymentHelper->getCardCvv()
                ],
                "show_order_summary" => $this->hostedPaymentHelper->getOrderSummary(),
                "transaction_css_template" => $this->hostedPaymentHelper->getCssTemplate()
            ];
        }

        return $requestData;
    }
}
