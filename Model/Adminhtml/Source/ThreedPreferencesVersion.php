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
class ThreedPreferencesVersion
{
     public function toOptionArray()
    {
        return [
                ['value' => '1.0', 'label' => __('Version 1.0')],
                ['value' => '2.0', 'label' => __('Version 2.0')]
        ];
    }
}
