<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Apexx\HostedPayment\Helper\Data as HostedPaymentHelper;

/**
 * Class InvoiceCaptureHandler
 * @package Apexx\HostedPayment\Gateway\Response
 */
class InvoiceCaptureHandler implements HandlerInterface
{
    const TXN_ID = '_id';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var HostedPaymentHelper
     */
    protected  $hostedPaymentHelper;

    /**
     * InvoiceCaptureHandler constructor.
     * @param SubjectReader $subjectReader
     * @param HostedPaymentHelper $hostedPaymentHelper
     */
    public function __construct(
        SubjectReader $subjectReader,
        HostedPaymentHelper $hostedPaymentHelper
    )
    {
        $this->subjectReader = $subjectReader;
        $this->hostedPaymentHelper = $hostedPaymentHelper;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $paymentDO->getPayment();

        if (isset($response['status'])) {
            if($response['status'] == 'AUTHORISED') {
                $payment->setTransactionId($response[self::TXN_ID]);
                $payment->setIsTransactionClosed(false);
                $payment->setTransactionAdditionalInfo('raw_details_info',$response);
            }
            if($response['status'] == 'CAPTURED') {
                $payment->setTransactionId($response[self::TXN_ID]);
                $payment->setIsTransactionClosed(false);
                $payment->setTransactionAdditionalInfo('raw_details_info',$response);
            }
        }
    }
}
