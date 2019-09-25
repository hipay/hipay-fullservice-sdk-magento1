/**
 * Functionality tested
 *  - Oneclick card registration
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';
describe('Oneclick card registration', function () {
    beforeEach(function () {
        this.cards = cardDatas;
        cy.fixture('notification').as("notification");
        let customerFixture = "customerFR";
        cy.fixture(customerFixture).as("customer");
    });

    it('Makes an authenticated order with one-click', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activateOneClick('cc');
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectShirtItem(1);

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
            cy.get('#hipay_cc_use_alias_oneclick').then(($input) => {
                expect($input).to.exist;
                expect($input).to.be.visible;
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

        cy.selectShirtItem(1);

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
            cy.get('#hipay_cc_use_alias_oneclick').then(($input) => {
                expect($input).to.exist;
                expect($input).to.be.visible;
            });
        });
    });
});