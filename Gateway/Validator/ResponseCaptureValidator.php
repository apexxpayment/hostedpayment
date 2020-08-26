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
 * Class ResponseCaptureValidator
 * @package Apexx\HostedPayment\Gateway\Validator
 */
class ResponseCaptureValidator extends AbstractValidator
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

        $paymentDataObjectInterface = \Magento\Payment\Gateway\Helper\SubjectReader::readPayment($validationSubject);

        $response = $validationSubject['response'];
        $payment = $paymentDataObjectInterface->getPayment();

        if (isset($response['url'])) {
            // capture_now enable
            $payment->setAdditionalInformation('_id', $response['_id']);
            $payment->setAdditionalInformation('url', $response['url']);
            return $this->createResult(
                true,
                []
            );
        } elseif (isset($response['status'])) {
            // capture_now disable
            if ($response['status'] == 'CAPTURED') {
                if (isset($response['reason_code'])) {
                    $payment->setAdditionalInformation('reason_code', $response['reason_code']);
                }
                if (isset($response['_id'])) {
                    $payment->setAdditionalInformation('_id', $response['_id']);
                }
                if (isset($response['authorization_code'])) {
                    $payment->setAdditionalInformation('authorization_code', $response['authorization_code']);
                }
                if (isset($response['merchant_reference'])) {
                    $payment->setAdditionalInformation('merchant_reference', $response['merchant_reference']);
                }
                if (isset($response['amount'])) {
                    $payment->setAdditionalInformation('amount', $response['amount']);
                }
                if (isset($response['status'])) {
                    $payment->setAdditionalInformation('status', $response['status']);
                }
                if (isset($response['card']['card_number'])) {
                    $payment->setAdditionalInformation('card_number', $response['card']['card_number']);
                }
                if (isset($response['card']['expiry_month'])) {
                    $payment->setAdditionalInformation('expiry_month', $response['card']['expiry_month']);
                }
                if (isset($response['card']['expiry_year'])) {
                    $payment->setAdditionalInformation('expiry_year', $response['card']['expiry_year']);
                }

                return $this->createResult(
                    true,
                    []
                );
            } elseif ($response['status'] == 'FAILED') {
                if (isset($response['errors'])) {
                    if (isset($response['errors'][0]['error_message'])) {
                        return $this->createResult(
                            false,
                            [__($response['errors'][0]['error_message'])]
                        );
                    } else {
                        return $this->createResult(
                            false,
                            [__($response['reason_message'])]
                        );
                    }
                } else {
                    return $this->createResult(
                        false,
                        [__('Gateway rejected the transaction.')]
                    );
                }

            } elseif ($response['status'] == 'DECLINED') {
                if (isset($response['errors'])) {
                    if (isset($response['errors'][0]['error_message'])) {
                        return $this->createResult(
                            false,
                            [__($response['errors'][0]['error_message'])]
                        );
                    } else {
                        return $this->createResult(
                            false,
                            [__($response['reason_message'])]
                        );
                    }
                } else {
                    return $this->createResult(
                        false,
                        [__('Gateway rejected the transaction.')]
                    );
                }

            } else {
                     return $this->createResult(
                        false,
                        [__('Gateway rejected the transaction.')]
                    );
            }
        } else {
            if (isset($response['message'])) {
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
}
