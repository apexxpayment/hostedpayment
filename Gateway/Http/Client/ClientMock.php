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
 * Class ClientMock
 * @package Apexx\HostedPayment\Gateway\Http\Client
 */
class ClientMock implements ClientInterface
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
     * ClientMock constructor.
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
        $url = $this->apexxBaseHelper->getApiEndpoint().'payment/hosted';

        $resultCode = json_encode($transferObject->getBody());

        $response = $this->apexxBaseHelper->getCustomCurl($url, $resultCode);

        $resultObject = json_decode($response);
        $responseResult = json_decode(json_encode($resultObject), True);

        $this->customLogger->debug('Hosted Authorize Request:', $transferObject->getBody());
        $this->customLogger->debug('Hosted Authorize Response:', $responseResult);

        return $responseResult;
    }
}
