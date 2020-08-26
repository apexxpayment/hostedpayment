<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Framework\HTTP\Client\Curl;
use Apexx\Base\Helper\Data as ApexxBaseHelper;
use Apexx\HostedPayment\Helper\Data as HostedPaymentHelper;
use Apexx\Base\Helper\Logger\Logger as CustomLogger;

/**
 * Class CaptureSale
 * @package Apexx\HostedPayment\Gateway\Http\Client
 */
class CaptureSale implements ClientInterface
{
    const SUCCESS = 1;
    const FAILURE = 0;

    /**
     * @var array
     */
    private $results = [
        self::SUCCESS,
        self::FAILURE
    ];

    /**
     * @var Curl
     */
    protected $curlClient;

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
     * CaptureSale constructor.
     * @param Curl $curl
     * @param ApexxBaseHelper $apexxBaseHelper
     * @param HostedPaymentHelper $hostedPaymentHelper
     * @param CustomLogger $customLogger
     */
    public function __construct(
        Curl $curl,
        ApexxBaseHelper $apexxBaseHelper,
        HostedPaymentHelper $hostedPaymentHelper,
        CustomLogger $customLogger
    ) {
        $this->curlClient = $curl;
        $this->apexxBaseHelper = $apexxBaseHelper;
        $this->hostedPaymentHelper = $hostedPaymentHelper;
        $this->customLogger = $customLogger;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();

        // Set capture url
        if (isset($request['transactionId'])){
            $url = $this->apexxBaseHelper->getApiEndpoint().'capture/'.$request['transactionId'];
        } else {
            $url = $this->apexxBaseHelper->getApiEndpoint().'payment/hosted';
        }
        unset($request['transactionId']);
        //Set parameters for curl
        $resultCode = json_encode($request);

        $response = $this->apexxBaseHelper->getCustomCurl($url, $resultCode);

        $resultObject = json_decode($response);
        $responseResult = json_decode(json_encode($resultObject), True);


        $this->customLogger->debug('Hosted Capture Request:', $request);
        $this->customLogger->debug('Hosted Capture Response:', $responseResult);

        return $responseResult;
    }
}
