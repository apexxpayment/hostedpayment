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
 * Class ResponseRefundValidator
 * @package Apexx\HostedPayment\Gateway\Validator
 */
class ResponseRefundValidator extends AbstractValidator
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
            if ($response['status'] == 'REFUNDED') {
                return $this->createResult(
                    true,
                    []
                );
            } 
        elseif ($response['status'] == 'DECLINED') {
                    return $this->createResult(
                    false,
                    [__($response['reason_message'])]
                ); 

            }
        elseif ($response['status'] == 'FAILED') {
                    return $this->createResult(
                    false,
                    [__($response['reason_message'])]
                ); 
        } 
        else {
            if (isset($response['reason_message'])) {
                return $this->createResult(
                    false,
                    [__($response['reason_message'])]
                );
            } else {
                return $this->createResult(
                    false,
                    [__('Gateway rejected the transaction.')]
                );
            }
        }

    }
        else
        {   
            if(isset($response['message']))
            {
            return $this->createResult(
                        false,
                        [__($response['message'])]
                    );
            }
            else
            {
                return $this->createResult(
                        false,
                        [__('Gateway rejected the transaction.')]
                    );
            }
        }
    }
}