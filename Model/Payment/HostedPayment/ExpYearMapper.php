<?php

namespace Apexx\HostedPayment\Model\Payment\HostedPayment;
use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\ExpYearMapper as Base_ExpYearMapper;

class ExpYearMapper extends Base_ExpYearMapper
{
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $additionalInfo = $order->getPayment()->getAdditionalInformation();
        $expYear = null;

        if ($this->isExpYear($additionalInfo)) {
            $expYear = $additionalInfo['expiry_year'];
        }

        if (empty($expYear)) {
            $expYear = parent::getPaymentData($order);
        }

        return $expYear;
    }

    /**
     * @param array $additionalInfo
     * @return bool
     */
    protected function isExpYear(array $additionalInfo)
    {
        if (!isset($additionalInfo['expiry_year'])) {
            return false;
        }
        if (empty($additionalInfo['expiry_year'])) {
            return false;
        }
        return true;
    }
}
