/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Apexx_HostedPayment/js/model/iframe',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage'
], function ($, Component, iframe, fullScreenLoader, urlBuilder,storage) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Apexx_HostedPayment/payment/iframe-methods',
            paymentReady: false
        },
        redirectAfterPlaceOrder: false,
        isInAction: iframe.isInAction,

        /**
         * @return {exports}
         */
        initObservable: function () {
            this._super()
                .observe('paymentReady');

            return this;
        },

        /**
         * @return {*}
         */
        isPaymentReady: function () {
            return this.paymentReady();
        },

        /**
         * Places order in pending payment status.
         */
        placePendingPaymentOrder: function () {
            if (this.placeOrder()) {
                this.isInAction(true);
                // capture all click events
                document.addEventListener('click', iframe.stopEventPropagation, true);
            }
        },

        /**
         * @return {*}
         */
        getPlaceOrderDeferredObject: function () {
            var self = this;
            return this._super().fail(function () {
                fullScreenLoader.stopLoader();
                self.isInAction(false);
                document.removeEventListener('click', iframe.stopEventPropagation, true);
            });
        },

        placeOrder: function (data, event) {
            var self = this;
            self.isInAction(true);

            if (event) {
                event.preventDefault();
            }
            if (this.validate()) {
                self.isPlaceOrderActionAllowed(false);
                self.getPlaceOrderDeferredObject()
                    .fail(
                        function (response) {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                        function (response) {
                            fullScreenLoader.startLoader();
                            var iframeurl = self.getHostedIframeUrl(response);
                            self.getHostedIframeUrl(response).done(function (responseJSON) {
                                var response = JSON.parse(responseJSON);
                                $('#hostedpayment_gateway-iframe').attr('src',response.url);
                                fullScreenLoader.stopLoader();
                                self.setIframeSize();
                            });
                        }
                    );
            }
            return false;
        },

        setIframeSize: function()
        {
           var iframe = jQuery(window.top.document).find("#hostedpayment_gateway-iframe");
            if(checkoutConfig.payment.hostedpayment_gateway.iFrameHeight !== '')
            {
            iframe.height(checkoutConfig.payment.hostedpayment_gateway.iFrameHeight);
            }
            else
            iframe.height('400');

             if(checkoutConfig.payment.hostedpayment_gateway.iFrameWidth !== '')
            {
                iframe.width(checkoutConfig.payment.hostedpayment_gateway.iFrameWidth);
            }
            else
            {
                iframe.width('400');
            }
        },
        getHostedIframeUrl: function (orderId) {
            var serviceUrl = urlBuilder.createUrl('/apexxhosted/orders/:orderId/iframe-url', {
                orderId: orderId
            });
            return storage.get(serviceUrl);
        },

        /**
         * After place order callback
         */
        afterPlaceOrder: function () {
            if (this.iframeIsLoaded) {
                document.getElementById(this.getCode() + '-iframe')
                    .contentWindow.location.reload();
            }

            this.paymentReady(true);
            this.iframeIsLoaded = false;
            this.isPlaceOrderActionAllowed(true);
            fullScreenLoader.stopLoader();
            },

        /**
         * Hide loader when iframe is fully loaded.
         */
        iframeLoaded: function () {
            fullScreenLoader.stopLoader();
        }
    });
});
