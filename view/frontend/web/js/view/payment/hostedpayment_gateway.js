define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'hostedpayment_gateway',
                component: 'Apexx_HostedPayment/js/view/payment/method-renderer/hostedpayment_gateway'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
