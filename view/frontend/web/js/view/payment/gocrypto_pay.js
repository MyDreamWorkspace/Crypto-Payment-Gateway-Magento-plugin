
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
                type: 'gocrypto_pay',
                component: 'Eligmaltd_GoCryptoPay/js/view/payment/method-renderer/gocrypto_pay'
            }
        );
        return Component.extend({});
    }
);
