<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class ResponseCodeValidator
 * @package Apexx\HostedPayment\Gateway\Validator
 */
class ResponseCodeValidator extends AbstractValidator
{
    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }
        $response = $validationSubject['response'];
        $paymentDataObjectInterface = \Magento\Payment\Gateway\Helper\SubjectReader::readPayment($validationSubject);

        $payment = $paymentDataObjectInterface->getPayment();

        if (isset($response['status'])){
            if ($response['status'] == 'AUTHORISED') {
                $payment->setAdditionalInformation('_id', $response['_id']);
                $payment->setAdditionalInformation('url', $response['url']);
                return $this->createResult(
                    true,
                    []
                );
            } else {
                return $this->createResult(
                    false,
                    [__('Gateway rejected the transaction.')]
                );
            }
        } elseif (isset($response['url'])) {
            $payment->setAdditionalInformation('_id', $response['_id']);
            $payment->setAdditionalInformation('url', $response['url']);
            return $this->createResult(
                true,
                []
            );
        } elseif (isset($response['message'])) {
            return $this->createResult(
                false,
                [__($response['message'])]
            );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
    }
}
