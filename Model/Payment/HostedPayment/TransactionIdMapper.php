<?php

namespace Apexx\HostedPayment\Model\Payment\HostedPayment;
use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\TransactionIdMapper as Base_TransactionIdMapper;

class TransactionIdMapper extends Base_TransactionIdMapper
{

    /**
     * Get transaction ID from database for Authorize.Net
     *
     * @param \Magento\Sales\Model\Order $order
     * @return null|string
     */
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $additionalInfo = $order->getPayment()->getAdditionalInformation();
        $transactionId = null;

        if ($this->isTransactionId($additionalInfo)) {
            $transactionId = $additionalInfo['_id'];
        }

        if (empty($transactionId)) {
            $transactionId = parent::getPaymentData($order);
        }

        return $transactionId;
    }

    /**
     * @param array $additionalInfo
     * @return bool
     */
    protected function isTransactionId(array $additionalInfo)
    {
        if (!isset($additionalInfo['_id'])) {
            return false;
        }
        if (empty($additionalInfo['_id'])) {
            return false;
        }
        return true;
    }
}
