<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */

namespace Apexx\HostedPayment\Api;

interface HostedIframeUrlInterface
{
    /**
     * @param string $orderId
     * @return string
     */
    public function getHostedIframeUrl($orderId);
}
