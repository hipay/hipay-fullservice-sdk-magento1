casper.test.begin('Test MOTO Payment With Incorrect Credentials', function(test) {
	phantom.clearCookies();
    var paymentType = "HiPay Enterprise Hosted Page";

    casper.start(headlink + "admin/")
    .then(function() {
    	authentification.proceed(test);
        method.proceed(test, paymentType, "hosted");
    })
    /* Active MOTO option */
    .then(function() {
        configuration.proceedMotoSendMail(test, '1');
    })
    /* Set bad credentials inside HiPay Entreprise formular and create order via admin panel */
    .then(function() {
        initialCredential = this.evaluate(function() { return document.querySelector('input[name="groups[hipay_api_moto][fields][api_username_test][value]"]').value; });
        test.info("Initial credential for api_user_name was :" + initialCredential);
        this.fillFormHipayEnterprise("blabla", true);
        checkout.proceed(test, paymentType, "hosted");
    })
    /* Submit order */
    .then(function() {
        this.echo("Submitting order...", "INFO");
        this.waitForSelector(x('//span[text()="Submit Order"]'), function success() {
            this.click(x('//span[text()="Submit Order"]'));
            test.info("Done");
        }, function fail() {
            this.fillFormHipayEnterprise(initialCredential, true);
            test.assertExists(x('//span[text()="Submit Order"]'), "Submit order button exists");
        });
    })
    /* Check failure page */
    .then(function() {
        this.echo("Checking order failure cause of incorrect credentials...", "INFO");
        this.waitForUrl(/admin\/sales_order\/index/, function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertExists('li.error-msg', "Correct response from Magento server !");
        }, function fail() {
            this.fillFormHipayEnterprise(initialCredential, true);
            test.assertUrlMatch(/admin\/sales_order\/index/, "Orders admin page exists");
        }, 10000);
    })
    /* Reinitialize HiPay Enterprise credentials */
    .then(function() {
        this.echo("Accessing to Hipay Enterprise menu...", "INFO");
        this.click(x('//span[text()="Configuration"]'));
        this.waitForUrl(/admin\/system_config/, function success() {
            this.click(x('//span[contains(., "HiPay Enterprise")]'));
            test.info("Done");
            this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
                this.fillFormHipayEnterprise(initialCredential, true);
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