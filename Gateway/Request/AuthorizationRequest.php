<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Apexx\Base\Helper\Data as ApexxBaseHelper;
use Apexx\HostedPayment\Helper\Data as HostedPaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session As CheckoutSession;

/**
 * Class AuthorizationRequest
 * @package Apexx\HostedPayment\Gateway\Request
 */
class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ApexxBaseHelper
     */
    protected  $apexxBaseHelper;

    /**
     * @var HostedPaymentHelper
     */
    protected  $hostedPaymentHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    protected $checkoutSession;


    public function __construct(
        ConfigInterface $config,
        ApexxBaseHelper $apexxBaseHelper,
        HostedPaymentHelper $hostedPaymentHelper,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        CheckoutSession $checkoutSession
    ) {
        $this->config = $config;
        $this->cartRepository = $cartRepository;
        $this->apexxBaseHelper = $apexxBaseHelper;
        $this->hostedPaymentHelper = $hostedPaymentHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Builds ENV request
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

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order= $payment->getOrder();
        $billing = $order->getBillingAddress();
       $amount = $buildSubject['amount']*100;

        $requestData= [
            //"account" => $this->apexxBaseHelper->getAccountId(),
            //"account" => '112ec893bb9d45cca5f521c750f97f5d',
            "organisation" => $this->apexxBaseHelper->getOrganizationId(),
            "amount" => $amount,
            "currency" => $this->checkoutSession->getQuote()->getQuoteCurrencyCode(),
            "capture_now" => $this->hostedPaymentHelper->getHostedPaymentAction(),
            "dynamic_descriptor" => $this->hostedPaymentHelper->getDynamicDescriptor(),
            "merchant_reference" => $order->getOrderIncrementId(),
            "return_url" => $this->apexxBaseHelper->getStoreUrl().'apexxhosted/index/response',
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
                "phone" => preg_replace('/[^\dxX]/', '', $billing->getTelephone())
            ],
            "three_ds" => [
                "three_ds_required" => $this->hostedPaymentHelper->getThreeDsRequired(),
                "three_ds_version" => $this->hostedPaymentHelper->getThreeDsVersion()
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
        return $requestData;
    }
}
