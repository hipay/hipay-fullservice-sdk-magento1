var initialCredential,
    currentBrandCC = typeCC;

casper.test.begin('Test Payment With Incorrect Credentials', function(test) {
	phantom.clearCookies();
    var paymentType = "HiPay Enterprise Credit Card";

    casper.start(baseURL + "admin/")
    .then(function() {
        this.logToBackend();
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
    .thenOpen(baseURL, function() {
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
        this.fillStepPayment();
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
    .then(function() {
        this.logToBackend();
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