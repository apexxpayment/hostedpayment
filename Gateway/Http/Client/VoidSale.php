<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Apexx\Base\Helper\Data as ApexxBaseHelper;
use Apexx\HostedPayment\Helper\Data as HostedPaymentHelper;
use Apexx\Base\Helper\Logger\Logger as CustomLogger;

/**
 * Class VoidSale
 * @package Apexx\HostedPayment\Gateway\Http\Client
 */
class VoidSale implements ClientInterface
{
    /**
     * @var ApexxBaseHelper
     */
    protected  $apexxBaseHelper;

    /**
     * @var HostedPaymentHelper
     */
    protected  $hostedPaymentHelper;

    /**
     * @var CustomLogger
     */
    protected $customLogger;

    /**
     * VoidSale constructor.
     * @param ApexxBaseHelper $apexxBaseHelper
     * @param HostedPaymentHelper $hostedPaymentHelper
     * @param CustomLogger $customLogger
     */
    public function __construct(
        ApexxBaseHelper $apexxBaseHelper,
        HostedPaymentHelper $hostedPaymentHelper,
        CustomLogger $customLogger
    ) {
        $this->apexxBaseHelper = $apexxBaseHelper;
        $this->hostedPaymentHelper = $hostedPaymentHelper;
        $this->customLogger = $customLogger;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array|mixed
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        $apiType = $this->apexxBaseHelper->getApiType();
        if ($apiType == 'Atomic') {
            $url = $this->apexxBaseHelper->getApiEndpoint().'cancel/payment/'.$request['transactionId'];
        } else {
            // Set cancel url
            $url = $this->apexxBaseHelper->getApiEndpoint().$request['transactionId'].'/cancel';
        }
        //Set parameters for curl
        unset($request['transactionId']);
        $resultCode = json_encode($request);

        $response = $this->apexxBaseHelper->getCustomCurl($url, $resultCode);
        $resultObject = json_decode($response);
        $responseResult = json_decode(json_encode($resultObject), True);

        $this->customLogger->debug('Hosted Void Request:', $request);
        $this->customLogger->debug('Hosted Void Response:', $responseResult);

        return $responseResult;
    }
}
