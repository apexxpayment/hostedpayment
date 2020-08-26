<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Model\Adminhtml\Source;

/**
 * Class ThreedPreferences
 * @package Apexx\HostedPayment\Model\Adminhtml\Source
 */
class ThreedPreferences
{
     public function toOptionArray()
    {
        return [
                ['value' => 'sca', 'label' => __('sca (sca)')],
                ['value' => 'frictionless', 'label' => __('frictionless (frictionless)')],
                ['value' => 'nopref', 'label' => __('nopref (nopref)')],
                ['value' => 'scamandate', 'label' => __('scamandate (scamandate)')],
        ];
    }
}
