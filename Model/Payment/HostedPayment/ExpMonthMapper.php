<?php

namespace Apexx\HostedPayment\Model\Payment\HostedPayment;

use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\ExpMonthMapper as Base_ExpMonthMapper;

class ExpMonthMapper extends Base_ExpMonthMapper
{
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $additionalInfo = $order->getPayment()->getAdditionalInformation();
        $expMonth = null;

        if ($this->isExpMonth($additionalInfo)) {
            $expMonth = $additionalInfo['expiry_month'];
        }

        if (empty($expMonth)) {
            $expMonth = parent::getPaymentData($order);
        }

        return $expMonth;
    }

    /**
     * @param array $additionalInfo
     * @return bool
     */
    protected function isExpMonth(array $additionalInfo)
    {
        if (!isset($additionalInfo['expiry_month'])) {
            return false;
        }
        if (empty($additionalInfo['expiry_month'])) {
            return false;
        }
        return true;
    }
}
