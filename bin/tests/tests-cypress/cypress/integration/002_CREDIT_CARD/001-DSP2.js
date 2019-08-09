/**
 * Functionality tested
 *  - Populating DSP2 fields when they should be with the right values
 */
var utils = require('../../support/utils');
describe('DSP2 field population', function () {
    beforeEach(function () {
        cy.fixture('cards').as("cards");
        cy.fixture('notification').as("notification");
        let customerFixture = "customerFR";
        cy.fixture(customerFixture).as("customer");
    });

    /*it('Makes an authenticated order with virtual products', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.signIn();

        cy.goToCart();

        cy.fillBillingForm(true);
        cy.selectShippingForm(undefined);

        cy.get('#p_method_hipay_cc').click();

        cy.get('#hipay_cc_cc_type').select('Visa');
        cy.get('#hipay_cc_cc_number').type(this.cards.visa.ok.cardNumber);
        cy.get('#hipay_cc_expiration').select(this.cards.visa.ok.expiryMonth);
        cy.get('#hipay_cc_expiration_yr').select(this.cards.visa.ok.expiryYear);
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
                    expect(request.merchant_risk_statement.pre_order_date).to.eq("", "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.match(/[12]/, "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("1", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq("", "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.match(/(.*)/, "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.match(/(.*)/, "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.match(/(.*)/, "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.match(/(.*)/, "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment.enrollment_date).to.match(/(.*)/, "[account_info.payment.enrollment_date]");
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
                    expect(request.recurring_info.frequency).to.eq('', "[browser_info.screen_width]");
                    expect(request.recurring_info.expiration_date).to.eq('', "[browser_info.timezone]");
                });
            });
        });
    });*/


    it('Makes an authenticated order with one-click', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activateOneClick('cc');
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
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
        cy.get('#hipay_cc_expiration').select(this.cards.visa.ok.expiryMonth);
        cy.get('#hipay_cc_expiration_yr').select(this.cards.visa.ok.expiryYear);
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
                        expect(request.merchant_risk_statement.pre_order_date).to.eq("", "[merchant_risk_statement.pre_order_date]");
                        expect(request.merchant_risk_statement.reorder_indicator).to.eq("2", "[merchant_risk_statement.reorder_indicator]");
                        expect(request.merchant_risk_statement.shipping_indicator).to.eq("2", "[merchant_risk_statement.shipping_indicator]");
                        expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                        let d = new Date();
                        let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                        // Account info
                        //  -> Customer
                        expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                        expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                        expect(request.account_info.customer.password_change).to.eq(today, "[account_info.customer.password_change]");
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

    /*it('Makes an authenticated order with out of stock products, no virtual products and an account name different from the shipping name', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.changeProductStock(1, 300);
        cy.changeProductStock(13, 300);
        cy.changeProductStock(6, 0, '2025-08-01');
        cy.adminLogOut();

        cy.visit("/index.php?controller=authentication&create_account=1");
        cy.get("#customer-form input[name='firstname']").type(this.customer.firstName + "a")
        cy.get("#customer-form input[name='lastname']").type(this.customer.lastName)
        cy.get("#customer-form input[name='email']").type(this.customer.email)
        cy.get("#customer-form input[name='password']").type(this.customer.password)
        cy.get("#customer-form button[data-link-action='save-customer']").click();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.goToCart();

        cy.fillShippingForm();

        cy.get('#checkout-addresses-step > h1:nth-child(1) > span:nth-child(3)').click();

        cy.get('*[data-link-action="edit-address"]').click();
        cy.get('*[name="firstname"').clear().type(this.customer.firstName);
        cy.get('#use_same_address').click();

        cy.get('#delivery-address .form-footer > .continue').click();

        cy.get('#invoice-address input[name="address1"]').type(this.customer.streetAddress + "1");
        cy.get('#invoice-address input[name="postcode"]').type(this.customer.zipCode);
        cy.get('#invoice-address input[name="city"]').type(this.customer.city);
        cy.get('#invoice-address select[name="id_country"]').select(this.customer.country, {force: true});
        if (this.customer.state !== undefined) {
            cy.get('#billing_state').select(this.customer.state, {force: true});
        }
        cy.get('#invoice-address .form-footer > .continue').click();

        cy.selectShippingForm(undefined);

        cy.get('input[data-module-name="credit_card"]').click({force: true});

        cy.get('#card-number').type(this.cards.visa.ok.cardNumber);
        cy.get('#the-card-name-id').type(this.cards.visa.ok.cardHolder);
        cy.get('#expiry-month').select(this.cards.visa.ok.expiryMonth);
        cy.get('#expiry-year').select(this.cards.visa.ok.expiryYear.substr(2));
        cy.get('#cvc').type(this.cards.visa.ok.cvc);

        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});

        cy.checkOrderSuccess();

        cy.window().then((win) => {
            let idCart = new URL(win.location.href).searchParams.get('id_cart');

            cy.logToAdmin();
            cy.getOrderRequest(idCart).then((request) => {
                cy.adminLogOut();

                cy.log(request).then(() => {
                    expect(request.orderid).to.match(new RegExp('^' + idCart + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq("", "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("2", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq("20250801", "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.eq("1", "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.match(/[23]/, "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq(today, "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq(today, "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq(today, "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.match(/(.*)/, "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.match(/(.*)/, "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.match(/(.*)/, "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.match(/(.*)/, "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment.enrollment_date).to.match(/(.*)/, "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping.shipping_used_date).to.match(/(.*)/, "[account_info.shipping.shipping_used_date]");
                    expect(request.account_info.shipping.name_indicator).to.eq("2", "[account_info.shipping.name_indicator]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.device_fingerprint).to.match(/(.*)/, "[browser_info.device_fingerprint]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('1', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
                });
            });
        });
    });

    it('Makes a non authenticated order with physical products', function () {
        cy.logToAdmin();
        cy.deleteClients();

        cy.changeProductStock(1, 300);
        cy.changeProductStock(6, 300);
        cy.changeProductStock(13, 300);
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.goToCart();

        cy.fillBillingForm();
        cy.fillShippingForm();

        cy.get('#checkout-addresses-step > h1:nth-child(1) > span:nth-child(3)').click();
        cy.get('*[data-link-action="different-invoice-address"]').click();
        cy.get('#invoice-address input[name="address1"]').type(this.customer.streetAddress + "1");
        cy.get('#invoice-address input[name="postcode"]').type(this.customer.zipCode);
        cy.get('#invoice-address input[name="city"]').type(this.customer.city);
        cy.get('#invoice-address select[name="id_country"]').select(this.customer.country, {force: true});
        if (this.customer.state !== undefined) {
            cy.get('#billing_state').select(this.customer.state, {force: true});
        }
        cy.get('#invoice-address .form-footer > .continue').click();

        cy.selectShippingForm(undefined);

        cy.get('input[data-module-name="credit_card"]').click({force: true});

        cy.get('#card-number').type(this.cards.visa.ok.cardNumber);
        cy.get('#expiry-month').select(this.cards.visa.ok.expiryMonth);
        cy.get('#expiry-year').select(this.cards.visa.ok.expiryYear.substr(2));
        cy.get('#cvc').type(this.cards.visa.ok.cvc);

        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});

        cy.checkOrderSuccess();

        cy.window().then((win) => {
            let idCart = new URL(win.location.href).searchParams.get('id_cart');

            cy.logToAdmin();
            cy.getOrderRequest(idCart).then((request) => {
                cy.adminLogOut();

                cy.log(request).then(() => {
                    expect(request.orderid).to.match(new RegExp('^' + idCart + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq("", "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq("", "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.eq("", "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("3", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq("", "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq("", "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq("", "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.eq("", "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.eq("", "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.eq("", "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.eq("", "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment.enrollment_date).to.eq("", "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping.shipping_used_date).to.eq("", "[account_info.shipping.shipping_used_date]");
                    expect(request.account_info.shipping.name_indicator).to.eq("1", "[account_info.shipping.name_indicator]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.device_fingerprint).to.match(/(.*)/, "[browser_info.device_fingerprint]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('1', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
                });
            });
        });
    });

    it('Makes a non authenticated order with virtual products', function () {
        cy.logToAdmin();
        cy.deleteClients();

        cy.changeProductStock(1, 300);
        cy.changeProductStock(6, 300);
        cy.changeProductStock(13, 300);
        cy.adminLogOut();

        cy.selectVirtualItem(2);
        cy.goToCart();

        cy.fillBillingForm();
        cy.fillShippingForm();

        cy.get('input[data-module-name="credit_card"]').click({force: true});

        cy.get('#card-number').type(this.cards.visa.ok.cardNumber);
        cy.get('#expiry-month').select(this.cards.visa.ok.expiryMonth);
        cy.get('#expiry-year').select(this.cards.visa.ok.expiryYear.substr(2));
        cy.get('#cvc').type(this.cards.visa.ok.cvc);

        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});

        cy.checkOrderSuccess();

        cy.window().then((win) => {
            let idCart = new URL(win.location.href).searchParams.get('id_cart');

            cy.logToAdmin();
            cy.getOrderRequest(idCart).then((request) => {
                cy.adminLogOut();

                cy.log(request).then(() => {
                    expect(request.orderid).to.match(new RegExp('^' + idCart + "(.*)$"), "[orderid]");

                    // Merchant risk statement
                    expect(request.merchant_risk_statement.email_delivery_address).to.eq(this.customer.email, "[merchant_risk_statement.email_delivery_address]");
                    expect(request.merchant_risk_statement.delivery_time_frame).to.eq("1", "[merchant_risk_statement.delivery_time_frame]");
                    expect(request.merchant_risk_statement.purchase_indicator).to.eq("1", "[merchant_risk_statement.purchase_indicator]");
                    expect(request.merchant_risk_statement.pre_order_date).to.eq("", "[merchant_risk_statement.pre_order_date]");
                    expect(request.merchant_risk_statement.reorder_indicator).to.eq("", "[merchant_risk_statement.reorder_indicator]");
                    expect(request.merchant_risk_statement.shipping_indicator).to.eq("5", "[merchant_risk_statement.shipping_indicator]");
                    expect(request.merchant_risk_statement.gift_card).to.eq("", "[merchant_risk_statement.gift_card]");

                    let d = new Date();
                    let today = d.getFullYear() + (d.getMonth() < 9 ? "0" : "") + (d.getMonth() + 1) + "" + (d.getDate() < 10 ? "0" : "") + (d.getDate());
                    // Account info
                    //  -> Customer
                    expect(request.account_info.customer.account_change).to.eq("", "[account_info.customer.account_change]");
                    expect(request.account_info.customer.opening_account_date).to.eq("", "[account_info.customer.opening_account_date]");
                    expect(request.account_info.customer.password_change).to.eq("", "[account_info.customer.password_change]");
                    //  -> Purchase
                    expect(request.account_info.purchase.count).to.eq("", "[account_info.purchase.count]");
                    expect(request.account_info.purchase.card_stored_24h).to.eq("", "[account_info.purchase.card_stored_24h]");
                    expect(request.account_info.purchase.payment_attempts_24h).to.eq("", "[account_info.purchase.payment_attempts_24h]");
                    expect(request.account_info.purchase.payment_attempts_1y).to.eq("", "[account_info.purchase.payment_attempts_1y]");
                    //  -> Payment
                    expect(request.account_info.payment.enrollment_date).to.eq("", "[account_info.payment.enrollment_date]");
                    //  -> Shipping
                    expect(request.account_info.shipping.shipping_used_date).to.eq("", "[account_info.shipping.shipping_used_date]");
                    expect(request.account_info.shipping.name_indicator).to.eq("1", "[account_info.shipping.name_indicator]");

                    // Device Channel
                    expect(request.device_channel).to.eq("2", "[device_channel]");

                    // Browser info
                    expect(request.browser_info.color_depth).to.match(/(.*)/, "[browser_info.color_depth]");
                    expect(request.browser_info.device_fingerprint).to.match(/(.*)/, "[browser_info.device_fingerprint]");
                    expect(request.browser_info.http_accept).to.match(/(.*)/, "[browser_info.http_accept]");
                    expect(request.browser_info.http_user_agent).to.match(/(.*)/, "[browser_info.http_user_agent]");
                    expect(request.browser_info.ipaddr).to.match(/(.*)/, "[browser_info.ipaddr]");
                    expect(request.browser_info.java_enabled).to.match(/(.*)/, "[browser_info.java_enabled]");
                    expect(request.browser_info.javascript_enabled).to.eq('1', "[browser_info.javascript_enabled]");
                    expect(request.browser_info.language).to.match(/(.*)/, "[browser_info.language]");
                    expect(request.browser_info.screen_height).to.match(/(.*)/, "[browser_info.screen_height]");
                    expect(request.browser_info.screen_width).to.match(/(.*)/, "[browser_info.screen_width]");
                    expect(request.browser_info.timezone).to.match(/(.*)/, "[browser_info.timezone]");
                });
            });
        });
    });*/
});