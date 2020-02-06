/**
 * Functionality tested
 *  - Populating DSP2 fields when they should be with the right values
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';
describe('DSP2 field population', function () {
    beforeEach(function () {
        this.cards = cardDatas;
        cy.fixture('notification').as("notification");
        let customerFixture = "customerFR";
        cy.fixture(customerFixture).as("customer");
    });

    it('Makes an authenticated order with all kinds of products', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectShirtItem(2);
        cy.selectMugItem(1);
        cy.selectVirtualItem(2);

        cy.signIn();

        cy.goToCart();

        cy.fillBillingForm(true);
        cy.selectShippingForm(undefined);

        cy.get('#p_method_hipay_cc').click();

        cy.get('#hipay_cc_cc_type').select('Visa');
        cy.get('#hipay_cc_cc_number').type(this.cards.visa.ok.cardNumber);
        cy.get('#hipay_cc_expiration').select(parseInt(this.cards.visa.ok.expiryMonth).toString());
        cy.get('#hipay_cc_expiration_yr').select(parseInt(this.cards.visa.ok.expiryYear).toString());
        cy.get('#hipay_cc_cc_cid').type(this.cards.visa.ok.cvc);

        cy.get('#payment-buttons-container button').click();
        cy.get('#review-buttons-container button').click();

        cy.checkOrderSuccess();

        cy.get('.col-main > p:nth-child(4) > a:nth-child(1)').invoke('text').then((orderId) => {
            cy.getOrderRequest('cc', orderId).then((request) => {
                cy.log(request).then(() => {
                    expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq(this.customer.email, "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("1", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq(undefined, "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.eq("1", "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("1", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq(undefined, "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq(undefined, "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.eq("0", "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.eq("0", "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.eq("0", "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.eq("0", "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment).to.eq(undefined, "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping.shipping_used_date).to.match(/(.*)/, "[account_info.shipping.shipping_used_date]");
                    expect(request.account_info.shipping.name_indicator).to.eq("1", "[account_info.shipping.name_indicator]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('true', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");

                    // Recurring info
                    expect(request.recurring_info).to.eq(undefined, "[recurring_info]");
                });
            });
        });
    });

    it('Makes an authenticated order with one-click', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activateOneClick('cc');
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectShirtItem(2);
        cy.selectMugItem(1);
        cy.selectVirtualItem(2);

        cy.signIn();

        cy.goToCart();

        cy.fillBillingForm(true);

        cy.get('#opc-shipping').click();

        cy.fillShippingForm(true);
        cy.selectShippingForm(undefined);

        cy.get('#p_method_hipay_cc').click();

        cy.get('#hipay_cc_cc_type').select('Visa');
        cy.get('#hipay_cc_cc_number').type(this.cards.visa.ok.cardNumber);
        cy.get('#hipay_cc_expiration').select(parseInt(this.cards.visa.ok.expiryMonth).toString());
        cy.get('#hipay_cc_expiration_yr').select(parseInt(this.cards.visa.ok.expiryYear).toString());
        cy.get('#hipay_cc_cc_cid').type(this.cards.visa.ok.cvc);

        cy.get('#hipay_cc_create_alias_oneclick').click();

        cy.get('#payment-buttons-container button').click();
        cy.get('#review-buttons-container button').click();

        cy.checkOrderSuccess();
        cy.saveLastOrderId();
    });


    it('Connect to BO stage and send authorization', function () {

        cy.fixture('order').then((order) => {
            // Send 116 notif to save card
            cy.connectAndSelectAccountOnHipayBO();

            cy.openTransactionOnHipayBO(order.lastOrderId);
            cy.openNotificationOnHipayBO(116).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
                cy.log(this.lastTransactionId);
                order.lastTransactionId = this.lastTransactionId;
                cy.writeFile('cypress/fixtures/order.json', order);
            });

            cy.openNotificationOnHipayBO(118).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
            });
        });
    });

    it('Makes an authenticated order with reorder and one-click', function () {
        cy.server();
        cy.route('POST', '/checkout/onepage/saveBilling/').as('saveBilling');
        cy.route('POST', '/checkout/onepage/getAdditional/').as('getAdditional');
        cy.route('GET', '/checkout/onepage/progress/?prevStep=billing').as('progressBilling');
        cy.route('POST', '/checkout/onepage/saveShipping/').as('saveShipping');
        cy.route('GET', '/checkout/onepage/progress/?prevStep=shipping').as('progressShipping');

        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activateOneClick('cc');
        cy.adminLogOut();

        cy.logIn();

        cy.fixture('order').then((order) => {
            cy.visit(order.lastOrderLink);
            cy.get('a.link-reorder').click();
            cy.goToCart();

            cy.get('#billing\\:use_for_shipping_no').click();
            cy.get('button[onclick="billing.save()"]').click();

            cy.wait('@saveBilling');
            cy.wait('@getAdditional');
            cy.wait('@progressBilling');

            cy.get('#shipping-address-select option:nth-child(2)').then(($option) => {
                cy.get('#shipping-address-select').select($option.text());
            });
            cy.get('button[onclick="shipping.save()"]').click();

            cy.wait('@saveShipping');
            cy.wait('@progressShipping');

            cy.selectShippingForm(undefined);

            cy.get('#p_method_hipay_cc').click();
            cy.get('#payment-buttons-container button').click();
            cy.get('#review-buttons-container button').click();

            cy.checkOrderSuccess();

            cy.get('.col-main > p:nth-child(4) > a:nth-child(1)').invoke('text').then((orderId) => {
                cy.getOrderRequest('cc', orderId).then((request) => {
                    cy.log(request);
                    cy.fixture('order').then((order) => {

                        expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                        expect(request.previous_auth_info.transaction_reference).to.eq(order.lastTransactionId, "[previous_auth_info.transaction_reference]");

                        // Merchant risk statement
                        expect(request.merchant_risk_statement.email_delivery_address).to.eq(this.customer.email, "[merchant_risk_statement.email_delivery_address]");
                        expect(request.merchant_risk_statement.delivery_time_frame).to.eq("1", "[merchant_risk_statement.delivery_time_frame]");
                        expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                        expect(request.merchant_risk_statement.pre_order_date).to.eq(undefined, "[merchant_risk_statement.pre_order_date]");
                        expect(request.merchant_risk_statement.reorder_indicator).to.eq("2", "[merchant_risk_statement.reorder_indicator]");
                        expect(request.merchant_risk_statement.shipping_indicator).to.eq("2", "[merchant_risk_statement.shipping_indicator]");
                        expect(request.merchant_risk_statement.gift_card).to.eq(undefined, "[merchant_risk_statement.gift_card]");

                        let d = new Date();
                        let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                        // Account info
                        //  -> Customer
                        expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                        expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                        expect(request.account_info.customer.password_change).to.eq(undefined, "[account_info.customer.password_change]");
                        //  -> Purchase
                        expect(request.account_info.purchase.count).to.eq("1", "[account_info.purchase.count]");
                        expect(request.account_info.purchase.card_stored_24h).to.eq("1", "[account_info.purchase.card_stored_24h]");
                        expect(request.account_info.purchase.payment_attempts_24h).to.eq("1", "[account_info.purchase.payment_attempts_24h]");
                        expect(request.account_info.purchase.payment_attempts_1y).to.eq("1", "[account_info.purchase.payment_attempts_1y]");
                        //  -> Payment
                        expect(request.account_info.payment.enrollment_date).to.eq(today, "[account_info.payment.enrollment_date]");
                        //  -> Shipping
                        expect(request.account_info.shipping.shipping_used_date).to.eq(today, "[account_info.shipping.shipping_used_date]");
                        expect(request.account_info.shipping.name_indicator).to.eq("1", "[account_info.shipping.name_indicator]");

                        // Device Channel
                        expect(request.device_channel).to.eq("2", "[device_channel]");

                        // Browser info
                        expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                        expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                        expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                        expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                        expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                        expect(request.browser_info.javascript_enabled).to.eq('true', "[browser_info.javascript_enabled]");
                        expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                        expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                        expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                        expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
                    });
                });
            });
        });
    });

    it('Makes a non authenticated order with physical products', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectShirtItem(2);
        cy.selectMugItem(1);
        cy.goToCart();

        cy.checkoutAsGuest();
        cy.fillBillingForm();

        cy.get('#opc-shipping').click();

        cy.fillShippingForm(true);
        cy.selectShippingForm(undefined);

        cy.get('#p_method_hipay_cc').click();

        cy.get('#hipay_cc_cc_type').select('Visa');
        cy.get('#hipay_cc_cc_number').type(this.cards.visa.ok.cardNumber);
        cy.get('#hipay_cc_expiration').select(parseInt(this.cards.visa.ok.expiryMonth).toString());
        cy.get('#hipay_cc_expiration_yr').select(parseInt(this.cards.visa.ok.expiryYear).toString());
        cy.get('#hipay_cc_cc_cid').type(this.cards.visa.ok.cvc);

        cy.get('#payment-buttons-container button').click();
        cy.get('#review-buttons-container button').click();

        cy.checkOrderSuccess();

        cy.get('.col-main > p:nth-child(4)').invoke('text').then((orderId) => {
            orderId = orderId.match(/[0-9]+/)[0];
            cy.getOrderRequest('cc', orderId).then((request) => {

                cy.log(request).then(() => {
                    expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq(undefined, "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq(undefined, "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq(undefined, "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.eq(undefined, "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("3", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq(undefined, "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    expect(request.account_info).to.eq(undefined, "[account_info]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('true', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
                });
            });
        });
    });

    it('Makes an authenticated order with only virtual products', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectVirtualItem(30);

        cy.signIn();

        cy.goToCart();

        cy.fillBillingForm(true);

        cy.get('#p_method_hipay_cc').click();

        cy.get('#hipay_cc_cc_type').select('Visa');
        cy.get('#hipay_cc_cc_number').type(this.cards.visa.ok.cardNumber);
        cy.get('#hipay_cc_expiration').select(parseInt(this.cards.visa.ok.expiryMonth).toString());
        cy.get('#hipay_cc_expiration_yr').select(parseInt(this.cards.visa.ok.expiryYear).toString());
        cy.get('#hipay_cc_cc_cid').type(this.cards.visa.ok.cvc);

        cy.get('#payment-buttons-container button').click();
        cy.get('#review-buttons-container button').click();

        cy.checkOrderSuccess();

        cy.get('.col-main > p:nth-child(4)').invoke('text').then((orderId) => {
            orderId = orderId.match(/[0-9]+/)[0];
            cy.getOrderRequest('cc', orderId).then((request) => {

                cy.log(request).then(() => {
                    expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq(this.customer.email, "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("1", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq(undefined, "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.eq("1", "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("5", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq(undefined, "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq(undefined, "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.eq("0", "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.eq("0", "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.eq("0", "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.eq("0", "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment).to.eq(undefined, "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping).to.eq(undefined, "[account_info.shipping]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.device_fingerprint).to.match(/(.*)/, "[browser_info.device_fingerprint]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('true', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
                });
            });
        });
    });

    it('Makes a split payment order and pays the first split', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.goToHipaySplitPaymentProfileAdmin();
        cy.createPaymentProfile('cypressProfile', 'Month', '3', '24');
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activatePaymentMethods('ccxtimes');
        cy.selectPaymentProfile('ccxtimes', 'cypressProfile');

        cy.selectShirtItem(48);
        cy.selectMugItem(24);
        cy.selectVirtualItem(48);

        cy.signIn();

        cy.goToCart();

        cy.fillBillingForm(true);
        cy.selectShippingForm(undefined);

        cy.get('#p_method_hipay_ccxtimes').click();

        cy.get('#hipay_ccxtimes_cc_type').select('Visa');
        cy.get('#hipay_ccxtimes_cc_number').type(this.cards.visa.ok.cardNumber);
        cy.get('#hipay_ccxtimes_expiration').select(parseInt(this.cards.visa.ok.expiryMonth).toString());
        cy.get('#hipay_ccxtimes_expiration_yr').select(parseInt(this.cards.visa.ok.expiryYear).toString());
        cy.get('#hipay_ccxtimes_cc_cid').type(this.cards.visa.ok.cvc);

        cy.get('#payment-buttons-container button').click();
        cy.get('#review-buttons-container button').click();

        cy.checkOrderSuccess();
        cy.saveLastOrderId();

        cy.get('.col-main > p:nth-child(4) > a:nth-child(1)').invoke('text').then((orderId) => {
            cy.getOrderRequest('ccxtimes', orderId).then((request) => {
                cy.log(request).then(() => {
                    expect(request.orderid).to.match(new RegExp('^' + orderId + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq(this.customer.email, "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("1", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq(undefined, "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.match(/[12]/, "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("1", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq(undefined, "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());

                    let expDate = new Date(d.setMonth(d.getMonth()+(23*3)));
                    let expDateStr = expDate.getFullYear() + (expDate.getMonth() < 9 ? "0" : "") +
                        (expDate.getMonth() + 1) + "" + (expDate.getDate() < 10 ? "0" : "") + (expDate.getDate());

                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq(undefined, "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.match(/(.*)/, "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.match(/(.*)/, "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.match(/(.*)/, "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.match(/(.*)/, "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment).to.eq(undefined, "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping.shipping_used_date).to.match(/(.*)/, "[account_info.shipping.shipping_used_date]");
                    expect(request.account_info.shipping.name_indicator).to.eq("1", "[account_info.shipping.name_indicator]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('true', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");

                    // Recurring info
                    expect(request.recurring_info.frequency).to.eq("84", "[recurring_info.frequency]");
                    // expect(request.recurring_info.expiration_date).to.eq(expDateStr, "[recurring_info.expiration_date]");
                });
            });
        });

        cy.adminLogOut();
    });

    it('Connect to BO stage and send authorization for split', function () {

        cy.fixture('order').then((order) => {
            // Send 116 notif to save card
            cy.connectAndSelectAccountOnHipayBO();

            cy.openTransactionOnHipayBO(order.lastOrderId);
            cy.openNotificationOnHipayBO(116).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
                cy.log(this.lastTransactionId);
                order.lastTransactionId = this.lastTransactionId;
                cy.writeFile('cypress/fixtures/order.json', order);
            });

            cy.openNotificationOnHipayBO(118).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
            });
        });
    });

    it('Pays the second split of a split payment', function () {
        cy.logToAdmin();
        cy.goToHipaySplitPaymentAdmin();

        cy.get('tr.even:nth-child(1)').click();
        cy.get('.content-header-floating *[title="Pay now"]').click({force:true});
        cy.get('.success-msg').contains('The split payment has been paid');

        cy.fixture('order').then((order) => {
            cy.getOrderRequest('ccxtimes', order.lastOrderId + '-split-').then((request) => {
                cy.log(request).then(() => {
                    expect(request.orderid).to.match(new RegExp('^' + order.lastOrderId + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq(this.customer.email, "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("1", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq(undefined, "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.match(/[12]/, "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("1", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq(undefined, "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());

                    let expDate = new Date(d.setMonth(d.getMonth()+(23*3)));
                    let expDateStr = expDate.getFullYear() + (expDate.getMonth() < 9 ? "0" : "") +
                        (expDate.getMonth() + 1) + "" + (expDate.getDate() < 10 ? "0" : "") + (expDate.getDate());

                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq(undefined, "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.match(/(.*)/, "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.match(/(.*)/, "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.match(/(.*)/, "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.match(/(.*)/, "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment).to.eq(undefined, "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping.shipping_used_date).to.match(/(.*)/, "[account_info.shipping.shipping_used_date]");
                    expect(request.account_info.shipping.name_indicator).to.eq("1", "[account_info.shipping.name_indicator]");

                    // Device Channel
                    expect(request.device_channel).to.eq("3", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('true', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");

                    // Recurring info
                    expect(request.recurring_info.frequency).to.eq("84", "[recurring_info.frequency]");
                    //expect(request.recurring_info.expiration_date).to.eq(expDateStr, "[recurring_info.expiration_date]");
                });
            });
        });
    });
});
