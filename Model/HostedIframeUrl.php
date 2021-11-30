<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */

namespace Apexx\HostedPayment\Model;

use \Magento\Sales\Api\OrderRepositoryInterface;
use Apexx\HostedPayment\Model\Ui\ConfigProvider;
use Apexx\HostedPayment\Helper\Data as HostedPaymentHelper;
use Psr\Log\LoggerInterface;
use Apexx\Base\Helper\Data as ApexxBaseHelper;
use Magento\Checkout\Model\Session As CheckoutSession;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Payment\Gateway\Http\TransferInterface;
use Apexx\Base\Helper\Logger\Logger as CustomLogger;

/**
 * Class HostedIframeUrl
 * @package Apexx\HostedPayment\Model
 */
class HostedIframeUrl implements \Apexx\HostedPayment\Api\HostedIframeUrlInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var HostedPaymentHelper
     */
    protected  $hostedPaymentHelper;

    /**
     * Logger for exception details
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ApexxBaseHelper
     */
    protected  $apexxBaseHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    protected $checkoutSession;

    /**
     * @var Curl
     */
    protected $curlClient;
    /**
     * @var CustomLogger
     */
    protected $customLogger;



    /**
     * HostedIframeUrl constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param HostedPaymentHelper $hostedPaymentHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        HostedPaymentHelper $hostedPaymentHelper,
        LoggerInterface $logger,
        ApexxBaseHelper $apexxBaseHelper,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        CheckoutSession $checkoutSession,
        Curl $curl,
        CustomLogger $customLogger
    ) {
        $this->orderRepository = $orderRepository;
        $this->hostedPaymentHelper = $hostedPaymentHelper;
        $this->logger = $logger;
        $this->cartRepository = $cartRepository;
        $this->apexxBaseHelper = $apexxBaseHelper;
        $this->checkoutSession = $checkoutSession;
        $this->curlClient = $curl;
        $this->customLogger = $customLogger;
    }


    /**
     * @param string $orderId
     * @return null|string
     */
    public function getHostedIframeUrl($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();
        $response = [];
        try {
        if ($payment->getMethod() === 'hostedpayment_gateway') {
            $additionalInformation = $payment->getAdditionalInformation();
            if(isset($additionalInformation['url']) && $additionalInformation['url']!=''){
                $url=$additionalInformation['url'];
            }else{
                $requestData = $this->getRequestData($order);
                $responseFromHost = $this->placeRequest($requestData);
                $url = $responseFromHost['url'];
            }
            $iframeUrl = $url;
            $response['url'] = $iframeUrl;
        }

            return json_encode($response);
        }  catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $response;
    }
    
    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function getRequestData($order){
        $billing = $order->getBillingAddress();
        $amount = $order->getGrandTotal()*100;
        $merchantReference = $this->apexxBaseHelper->encryptDecrypt(1, $order->getIncrementId());
        $requestData= [
            "organisation" => $this->apexxBaseHelper->getOrganizationId(),
            "amount" => $amount,
            "currency" => $order->getStoreCurrencyCode(),
            "capture_now" => $this->hostedPaymentHelper->getHostedPaymentAction(),
            "dynamic_descriptor" => $this->hostedPaymentHelper->getDynamicDescriptor(),
            "merchant_reference" => $merchantReference,
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

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest($request)
    {
        $url = $this->apexxBaseHelper->getApiEndpoint().'payment/hosted';

        $resultCode = json_encode($request);

        $response = $this->apexxBaseHelper->getCustomCurl($url, $resultCode);

        $resultObject = json_decode($response);
        $responseResult = json_decode(json_encode($resultObject), True);

        $this->customLogger->debug('Hosted Authorize Request:', $request);
        $this->customLogger->debug('Hosted Authorize Response:', $responseResult);

        return $responseResult;
    }

}