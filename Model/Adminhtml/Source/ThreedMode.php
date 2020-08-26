<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Model\Adminhtml\Source;

/**
 * Class ThreedMode
 * @package Apexx\HostedPayment\Model\Adminhtml\Source
 */
class ThreedMode
{
    public function toOptionArray()
    {
        return [
                    ['value' => 'sca', 'label' => __('sca (sca)')],
                    ['value' => 'frictionless', 'label' => __('frictionless (frictionless)')],
        ];
    }
}
