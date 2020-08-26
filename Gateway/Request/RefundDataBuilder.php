<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Apexx\Base\Helper\Data as ApexxBaseHelper;
use Apexx\HostedPayment\Helper\Data as HostedPaymentHelper;

/**
 * Class RefundDataBuilder
 * @package Apexx\HostedPayment\Gateway\Request
 */
class RefundDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ApexxBaseHelper
     */
    protected  $apexxBaseHelper;

    /**
     * @var HostedPaymentHelper
     */
    protected  $hostedPaymentHelper;

    /**
     * RefundDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param ApexxBaseHelper $apexxBaseHelper
     * @param HostedPaymentHelper $hostedPaymentHelper
     */
    public function __construct(
        SubjectReader $subjectReader,
        ApexxBaseHelper $apexxBaseHelper,
        HostedPaymentHelper $hostedPaymentHelper
    )
    {
        $this->subjectReader = $subjectReader;
        $this->apexxBaseHelper = $apexxBaseHelper;
        $this->hostedPaymentHelper = $hostedPaymentHelper;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        /** @var Payment $orderPayment */
        $orderPayment = $paymentDO->getPayment();

        // Send Parameters to Paypal Payment Client
        $order = $paymentDO->getOrder();
        $amount = $buildSubject['amount'];

        //Get last transaction id for authorization
        $lastTransId = $this->apexxBaseHelper->getHostedPayTxnId($order->getId());

        if ($lastTransId != '') {
            $requestData = [
                "transactionId" => $lastTransId,
                "amount" => ($amount * 100),
                "reason" => time()."-".$order->getOrderIncrementId(),
                "capture_id" => $orderPayment->getParentTransactionId()
            ];
        } else {
            $requestData = [
                "transactionId" => $orderPayment->getParentTransactionId(),
                "amount" => ($amount * 100),
                "reason" => time()."-".$order->getOrderIncrementId()
            ];
        }

        return $requestData;
    }
}
