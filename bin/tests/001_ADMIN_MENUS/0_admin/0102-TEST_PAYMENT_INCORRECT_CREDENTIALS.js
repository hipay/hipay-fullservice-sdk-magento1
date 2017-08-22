var initialCredential;

casper.test.begin('Test Payment With Incorrect Credentials', function(test) {
	phantom.clearCookies();
    var paymentType = "HiPay Enterprise Credit Card";

    casper.start(headlink + "admin/")
    .then(function() {
        authentification.proceed(test);
        method.proceed(test, paymentType, "cc");
    })
    /* Disactive MOTO option */
    .then(function() {
        configuration.proceedMotoSendMail(test, '0');
    })
    /* Set bad credentials inside HiPay Entreprise formular */
    .then(function(){
        initialCredential = this.evaluate(function() { return document.querySelector('input[name="groups[hipay_api][fields][api_username_test][value]"]').value; });
        test.info("Initial credential for api_user_name was :" + initialCredential);
        this.fillFormHipayEnterprise("blabla");
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
    /* HiPay CC payment */
    .then(function() {
        this.echo("Choosing payment method and filling 'Payment Information' formular with " + typeCC + "...", "INFO");
        this.waitUntilVisible('#checkout-step-payment', function success() {
            this.click('#dt_method_hipay_cc>input[name="payment[method]"]');
            if(typeCC == 'VISA')
                this.fillFormPaymentHipayCC('VI', cardsNumber[0]);
            else if(typeCC == 'CB' || typeCC == "MasterCard")
                this.fillFormPaymentHipayCC('MC', cardsNumber[1]);

            this.click("div#payment-buttons-container>button");
            test.info("Done");
        }, function fail() {
            test.info("Initial credential for api_user_name was :" + initialCredential);
            this.fillFormHipayEnterprise(initialCredential);
            test.assertVisible("#checkout-step-payment", "'Payment Information' formular exists");
        }, 10000);
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    /* Check failure page */
    .then(function() {
        this.echo("Checking order failure cause of incorrect credentials...", "INFO");
        this.waitForUrl(/checkout\/cart/, function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertExists('li.error-msg', "Correct response from Magento server !");
        }, function fail() {
            test.info("Initial credential for api_user_name was :" + initialCredential);
            this.fillFormHipayEnterprise(initialCredential);
            test.assertUrlMatch(/checkout\/cart/, "Checkout page exists");
        }, 15000);
    })
    .thenOpen(headlink + "admin/", function() {
        authentification.proceed(test);
    })
    /* Reinitialize credentials inside HiPay Enterprise */
    .then(function() {
        this.echo("Accessing to Hipay Enterprise menu...", "INFO");
        this.click(x('//span[text()="Configuration"]'));
        this.waitForUrl(/admin\/system_config/, function success() {
            this.click(x('//span[contains(., "HiPay Enterprise")]'));
            test.info("Done");
            this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
                test.info("Initial credential for api_user_name was :" + initialCredential);
                this.fillFormHipayEnterprise(initialCredential);
            }, function fail() {
                test.assertExists(x('//h3[text()="HiPay Enterprise"]'), "Hipay Enterprise admin page exists");
            }, 10000);
        }, function fail() {
            test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
        }, 10000);
    })
    .run(function() {
        test.done();
    });
});