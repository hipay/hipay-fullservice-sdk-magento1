var paymentType = "HiPay Enterprise Credit Card",
	currentBrandCC = typeCC;

casper.test.begin('Change Hash Algorithm ' + paymentType + ' with ' + typeCC, function(test) {
	phantom.clearCookies();

	casper.setFilter("page.confirm", function(msg) {
		this.echo("Confirmation message " + msg, "INFO");
		return true;
	});

	casper.start(baseURL)
	.thenOpen(urlBackend, function() {
		this.logToHipayBackend(loginBackend,passBackend);
	})
	.then(function() {
		this.selectAccountBackend("OGONE_DEV");
	})
	/* Open Integration tab */
	.then(function() {
		this.echo("Open Integration nav", "INFO");
		this.waitForUrl(/maccount/, function success() {
			this.selectHashingAlgorithm("SHA512");
		}, function fail() {
			test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
		});
	})
	.then(function() {
		this.logToBackend();
	})
	.then(function() {
		this.gotoMenuHipayEnterprise();
	})
	.then(function() {
		this.echo("Synchronize Hashing Algorithm", "INFO");
		this.waitForSelector('button#synchronize_button', function success() {
			var current = this.evaluate(function () {
				return document.querySelector('select#hipay_hipay_api_hashing_algorithm').value;
			});
			test.info("Initial Hashing Algorithm :" + current);
			if (current != 'SHA1') {
				test.fail("Initial value is wrong for Hashing : " + current );
			}
			this.thenClick('button#synchronize_button', function() {
				var newHashingAlgo = this.evaluate(function () {
					return document.querySelector('select#hipay_hipay_api_hashing_algorithm').value;
				});
				if (newHashingAlgo != 'SHA512') {
					test.fail("Synchronize doesn't work : " + current );
				} else {
					test.info("Done");
				}
			});
		}, function fail() {
			test.assertExists('button#synchronize_button', "Syncronize button exist");
		});
	})
	.thenOpen(baseURL, function() {
		this.waitUntilVisible('div.footer', function success() {
			this.selectItemAndOptions();
		}, function fail() {
			test.assertVisible("div.footer", "'Footer' exists");
		}, 10000);
	},15000)
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
	/* Fill steps payment */
	.then(function() {
		this.fillStepPayment();
	})
	.then(function() {
		this.orderReview(paymentType);
	})
	.then(function() {
		this.orderResult(paymentType);

	})
	.thenOpen(urlBackend, function() {
		this.logToHipayBackend(loginBackend,passBackend);
	})
	.then(function() {
		this.selectAccountBackend("OGONE_DEV");
	})
	.then(function() {
		cartID = casper.getOrderId();
		orderID = casper.getOrderId();
		this.processNotifications(true,false,true,false);
	})
	.thenOpen(urlBackend, function() {
		this.logToHipayBackend(loginBackend,passBackend);
	})
	.then(function() {
		this.selectAccountBackend("OGONE_DEV");
	})
	/* Open Integration tab */
	.then(function() {
		this.echo("Open Integration nav", "INFO");
		this.waitForUrl(/maccount/, function success() {
			this.selectHashingAlgorithm("SHA1");
		}, function fail() {
			test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
		});
	})
	.run(function() {
        test.done();
    });
});