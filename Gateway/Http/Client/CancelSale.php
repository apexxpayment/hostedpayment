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
 * Class CancelSale
 * @package Apexx\HostedPayment\Gateway\Http\Client
 */
class CancelSale implements ClientInterface
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
     * CancelSale constructor.
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
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        // Set cancel url
        $url = $this->apexxBaseHelper->getApiEndpoint().$request['transactionId'].'/cancel';
        //Set parameters for curl
        unset($request['transactionId']);
        $resultCode = json_encode($request);

        $response = $this->apexxBaseHelper->getCustomCurl($url, $resultCode);
        $resultObject = json_decode($response);
        $responseResult = json_decode(json_encode($resultObject), True);

        $this->customLogger->debug('Hosted Cancel Request:', $request);
        $this->customLogger->debug('Hosted Cancel Response:', $responseResult);

        return $responseResult;
    }
}
