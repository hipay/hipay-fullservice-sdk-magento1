/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SEPA DIRECT DEBIT
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise SEPA Direct Debit";

casper.test.begin('Test Checkout ' + paymentType + ' with Electronic Signature', function(test) {
	phantom.clearCookies();

    casper.start(headlink + "admin/")
    /* Active SEPA payment method without electronic signature */
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "sdd", ['select[name="groups[hipay_sdd][fields][electronic_signature][value]"]', '0']);
    })
    .thenOpen(headlink, function() {
        this.selectItemAndOptions();
    })
    .then(function() {
        this.addItemGoCheckout();
    })
    .then(function() {
        this.checkoutMethod();
    })
    .then(function() {
        this.billingInformation();
    })
    .then(function() {
        this.shippingMethod();
    })
    /* Choose and fill payment formular */
    .then(function() {
    	this.echo("Choosing payment method and filling 'Payment Information' formular...", "INFO");
    	this.waitUntilVisible('#checkout-step-payment', function success() {
            method_hipay="method_hipay_sdd";
            if (this.visible('p[class="bugs"]')) {
                this.click('input#p_' + method_hipay);
            } else {
                this.click('#dt_' + method_hipay +'>input[name="payment[method]"]');
            }

            this.fillSelectors('form#co-payment-form', {
                'select[name="payment[cc_gender]"]': "M",
                'input[name="payment[cc_firstname]"]': "TEST",
                'input[name="payment[cc_lastname]"]': "TEST",
                'input[name="payment[cc_iban]"]': ibanNumber[0],
                'input[name="payment[cc_code_bic]"]': bicNumber[1],
                'input[name="payment[cc_bank_name]"]': "BANK TEST"
            }, false);
    		this.click("div#payment-buttons-container>button");
    		test.info("Done");
		}, function fail() {
        	test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 10000);
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    .then(function() {
        this.orderResult(paymentType);
    })
    .run(function() {
        test.done();
    });
});