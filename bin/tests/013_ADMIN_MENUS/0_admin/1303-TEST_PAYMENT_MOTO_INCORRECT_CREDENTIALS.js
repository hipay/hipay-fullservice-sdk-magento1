casper.test.begin('Test MOTO Payment With Incorrect Credentials', function(test) {
	phantom.clearCookies();
    var paymentType = "HiPay Enterprise Hosted Page";

    casper.start(headlink + "admin/")
    .then(function() {
    	authentification.proceed(test);
        method.proceed(test, paymentType, "hosted");
    })
    .then(function() {
        configuration.proceedMotoSendMail(test, '1');
    })
    .then(function() {
        this.fillFormHipayEnterprise("blabla", true);
        checkout.proceed(test, paymentType, "hosted");
    })
    .then(function() {
        this.echo("Submitting order...", "INFO");
        this.waitForSelector(x('//span[text()="Submit Order"]'), function success() {
            this.click(x('//span[text()="Submit Order"]'));
            test.info("Done");
        }, function fail() {
            test.assertExists(x('//span[text()="Submit Order"]'), "Submit order button exists");
        });
    })
    .then(function() {
        this.echo("Checking order failure cause of incorrect credentials...", "INFO");
        this.waitForUrl(/admin\/sales_order\/index/, function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertTextExists('Incorrect Credentials : API User Not Found', "Correct response from Magento server !");
        }, function fail() {
            test.assertUrlMatch(/admin\/sales_order\/index/, "Orders admin page exists");
        }, 10000);
    })
    .then(function() {
        this.echo("Accessing to Hipay Enterprise menu...", "INFO");
        this.click(x('//span[text()="Configuration"]'));
        this.waitForUrl(/admin\/system_config/, function success() {
            this.click(x('//span[contains(., "HiPay Enterprise")]'));
            test.info("Done");
            this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
                this.fillFormHipayEnterprise(correctCredConfigAdmin, true);
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