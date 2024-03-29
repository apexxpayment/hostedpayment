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
 * Class ResponseCancelValidator
 * @package Apexx\HostedPayment\Gateway\Validator
 */
class ResponseCancelValidator extends AbstractValidator
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

        if (isset($response['status'])){
            if ($response['status'] == 'CANCELLED') {
                return $this->createResult(
                    true,
                    []
                );
            } elseif ($response['status'] == 'FAILED') {
                if ($response['errors']) {
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
