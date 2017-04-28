casper.test.begin('Test Magento Device Fingerprint', function(test) {
	phantom.clearCookies();
    var paymentType = "HiPay Enterprise Credit Card",
        ioBB = "";

    casper.start(headlink + "admin/")
    .then(function() {
    	authentification.proceed(test);
    })
    .then(function() {
    	this.echo("Accessing Hipay Enterprise menu...", "INFO");
    	this.click(x('//span[text()="Configuration"]'));
    	this.waitForUrl(/admin\/system_config/, function success() {
    		this.click(x('//span[contains(., "HiPay Enterprise")]'));
            test.info("Done");
    		this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
    			this.echo("Changing Device Fingerprint field...", "INFO");
                var valueFingerprint = this.evaluate(function() { return document.querySelector('select[name="groups[hipay_api][fields][fingerprint][value]"]').value; });
                if(valueFingerprint == 0)
                    test.info("Device Fingerprint configuration already done");
                else {
                    this.fillSelectors("form#config_edit_form", {
                        'select[name="groups[hipay_api][fields][fingerprint][value]"]': "0"
                    }, false);
                    this.click(x('//span[text()="Save Config"]'));
                    this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
                        test.info("Device Fingerprint Configuration done");
                    }, function fail() {
                        test.fail('Failed to apply Device Fingerprint Configuration on the system');
                    }, 15000);
                }
    		}, function fail() {
    			test.assertExists(x('//h3[text()="HiPay Enterprise"]'), "Hipay Enterprise admin page exists");
    		}, 10000);
    	}, function fail() {
    		test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
    	}, 10000);
    })
    .thenOpen(headlink, function() {
        this.selectItemAndOptions();
    })
    .then(function() {
        this.addItemGoCheckout();
    })
    .then(function() {
        this.waitForUrl(/checkout\/onepage/, function success() {
            this.echo("Checking 'ioBB' field inside checkout page...", "INFO");
            test.assertDoesntExist('input#ioBB', "'ioBB' field is present and not empty !");
        }, function fail() {
            test.assertUrlMatch(/checkout\/onepage/, "Checkout page exists");
        }, 10000)
    })
    .run(function() {
        test.done();
    });
});