casper.test.begin('Test Magento Admin Menus', function(test) {
	phantom.clearCookies();

    casper.start(headlink + "admin/")
    .then(function() {
    	authentification.proceed(test);
    })
    /* Check HiPay Split Payments menu */
    .then(function() {
    	this.echo("Checking HiPay Split Payments menu...", "INFO");
    	this.waitForUrl(/admin\/dashboard/, function success() {
	    	this.click(x('//span[text()="Split payments"]'));
	    	this.waitForUrl(/admin\/splitPayment/, function success() {
	    		test.assertTextExists('HiPay Split Payments', "HiPay Split Payments menu activated !");
	    	}, function fail() {
	    		test.assertUrlMatch(/admin\/splitPayment/, "Split Payments admin page exists");
	    	}, 10000);
	    }, function fail() {
	    	test.assertUrlMatch(/admin\/dashboard/, "Dashboard admin page exists");
	    }, 10000);
    })
    /* Check HiPay Enterprise menu */
    .then(function() {
    	this.echo("Checking Hipay Enterprise menu...", "INFO");
    	this.click(x('//span[text()="Configuration"]'));
    	this.waitForUrl(/admin\/system_config/, function success() {
    		this.click(x('//span[contains(., "HiPay Enterprise")]'));
    		this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
    			test.assertTextExists("HiPay Enterprise", "HiPay Enterprise menu activated !");
    		}, function fail() {
    			test.assertExists(x('//h3[text()="HiPay Enterprise"]'), "Hipay Enterprise admin page exists");
    		}, 10000);
    	}, function fail() {
    		test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
    	}, 10000);
    })
    /* Check Payment Methods bloc count */
    .then(function() {
    	this.echo("Checking Payments Methods blocs...", "INFO");
    	this.click(x('//span[contains(., "Payment Methods")]'));
    	this.waitForSelector(x('//h3[text()="Payment Methods"]'), function success() {
    		var methods = this.evaluate(function() {
    			return document.querySelectorAll('div.entry-edit>div.section-config').length;
    		});
			test.assert(this.exists('div.section-config>div>a#payment_hipay_cc-head') && methods > 1, methods + " Payments Methods blocs counted !");
    	}, function fail() {
    		test.assertExists(x('//h3[text()="Payment Methods"]'), "Payment Methods admin page exists");
    	}, 10000)
    })
    .run(function() {
        test.done();
    });
});