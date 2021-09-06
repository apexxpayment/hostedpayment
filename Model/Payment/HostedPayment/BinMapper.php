<?php

namespace Apexx\HostedPayment\Model\Payment\HostedPayment;

use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\BinMapper as BaseBinMapper;

/**
 * Class BinMapper
 * @package Mido\Apexx\Model\Payment\HostedPayment
 */
class BinMapper extends BaseBinMapper
{
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $additionalInfo = $order->getPayment()->getAdditionalInformation();
        $bin = null;

        if ($this->isCardNumber($additionalInfo)) {
            $bin = substr($additionalInfo['card_number'], 0, 6);
        }

        if (empty($bin)) {
            $bin = parent::getPaymentData($order);
        }

        return $bin;
    }

    /**
     * @param array $additionalInfo
     * @return bool
     */
    protected function isCardNumber(array $additionalInfo)
    {
        if (!isset($additionalInfo['card_number'])) {
            return false;
        }
        if (empty($additionalInfo['card_number'])) {
            return false;
        }
        if (strlen($additionalInfo['card_number']) < 6) {
            return false;
        }
        return true;
    }
}
