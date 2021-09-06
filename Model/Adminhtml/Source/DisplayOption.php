<?php
/**
 * Custom payment method in Magento 2
 * @category    HostedPayment
 * @package     Apexx_HostedPayment
 */
namespace Apexx\HostedPayment\Model\Adminhtml\Source;

/**
 * Class PaymentAction
 * @package Apexx\HostedPayment\Model\Adminhtml\Source
 */
class DisplayOption
{
    /**
     * Different payment actions.
     */
    const DISPLAY_IFRAME = 'iframe';

    const DISPLAY_MODAL_POPUP = 'modal_popup';

    public function toOptionArray()
    {
        return [
                    [
                        'value' => self::DISPLAY_IFRAME,
                        'label' => __('iframe')
                    ],
                    [
                        'value' => self::DISPLAY_MODAL_POPUP,
                        'label' => __('Modal Popup')
                    ],
        ];
    }
}
