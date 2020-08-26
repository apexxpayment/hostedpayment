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
     * HostedIframeUrl constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param HostedPaymentHelper $hostedPaymentHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        HostedPaymentHelper $hostedPaymentHelper,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->hostedPaymentHelper = $hostedPaymentHelper;
        $this->logger = $logger;
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
                $iframeUrl = $additionalInformation['url'];
                $response['url'] = $iframeUrl;
            }

            return json_encode($response);
        }  catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $response;
    }
}
