/**
 * Functionality tested
 *  - Multibanco payment
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';
describe('Multibanco payments', function () {
    beforeEach(function () {
        this.cards = cardDatas;
        let customerFixture = "customerPT";
        cy.fixture(customerFixture).as("customer");
    });

    it('Makes an authenticated order via multibanco with 3 days expiration', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activatePaymentMethods('multibanco');
        cy.configurePaymentMethods('multibanco', 'expiration_delay', '3');
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.signIn();

        cy.goToCart();

        cy.fillBillingForm(true, 'PT');
        cy.selectShippingForm(undefined);

        cy.get('#p_method_hipay_multibanco').click({force: true});

        cy.get('#payment-buttons-container button').click();
        cy.get('#review-buttons-container button').click();


        let d = new Date();

        let expDate = new Date(d.setDate(d.getDate() + 3));
        let expDateStr = (expDate.getDate() < 10 ? "0" : "") + (expDate.getDate()) + "/" + (expDate.getMonth() < 9 ? "0" : "") +
            (expDate.getMonth() + 1) + "/" + expDate.getFullYear();

        cy.get('#comprafacil-logo-multibanco').then(($p) => {
            expect($p).to.exist;
        });

        cy.get('.reference-content > div:nth-child(5)').then(($div) => {
            expect($div.text()).to.contain(expDateStr);
        });

        cy.get('a.btn:nth-child(1)').click();

        cy.checkOrderSuccess();
    });

    it('Makes an authenticated order via multibanco with 30 days expiration', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activatePaymentMethods('multibanco');
        cy.configurePaymentMethods('multibanco', 'expiration_delay', '30');
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.signIn();

        cy.goToCart();

        cy.fillBillingForm(true, 'PT');
        cy.selectShippingForm(undefined);

        cy.get('#p_method_hipay_multibanco').click({force: true});

        cy.get('#payment-buttons-container button').click();
        cy.get('#review-buttons-container button').click();


        let d = new Date();

        let expDate = new Date(d.setDate(d.getDate() + 30));
        let expDateStr = (expDate.getDate() < 10 ? "0" : "") + (expDate.getDate()) + "/" + (expDate.getMonth() < 9 ? "0" : "") +
            (expDate.getMonth() + 1) + "/" + expDate.getFullYear();

        cy.get('#comprafacil-logo-multibanco').then(($p) => {
            expect($p).to.exist;
        });

        cy.get('.reference-content > div:nth-child(5)').then(($div) => {
            expect($div.text()).to.contain(expDateStr);
        });

        cy.get('a.btn:nth-child(1)').click();

        cy.checkOrderSuccess();
    });

    it('Makes an authenticated order via multibanco with 90 days expiration', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activatePaymentMethods('multibanco');
        cy.configurePaymentMethods('multibanco', 'expiration_delay', '90');
        cy.deleteClients();
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.signIn();

        cy.goToCart();

        cy.fillBillingForm(true, 'PT');
        cy.selectShippingForm(undefined);

        cy.get('#p_method_hipay_multibanco').click({force: true});

        cy.get('#payment-buttons-container button').click();
        cy.get('#review-buttons-container button').click();


        let d = new Date();

        let expDate = new Date(d.setDate(d.getDate() + 90));
        let expDateStr = (expDate.getDate() < 10 ? "0" : "") + (expDate.getDate()) + "/" + (expDate.getMonth() < 9 ? "0" : "") +
            (expDate.getMonth() + 1) + "/" + expDate.getFullYear();

        cy.get('#comprafacil-logo-multibanco').then(($p) => {
            expect($p).to.exist;
        });

        cy.get('.reference-content > div:nth-child(5)').then(($div) => {
            expect($div.text()).to.contain(expDateStr);
        });

        cy.get('a.btn:nth-child(1)').click();

        cy.checkOrderSuccess();
    });
});