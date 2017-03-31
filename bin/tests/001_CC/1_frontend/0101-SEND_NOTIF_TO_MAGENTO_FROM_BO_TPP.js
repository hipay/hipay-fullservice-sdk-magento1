/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CARD (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

phantom.injectJs('bin/tests/001_CC/1_frontend/0100-CREDIT_CARD_FRONTEND.js');

var paymentType = "HiPay Enterprise Credit Card";

casper.test.begin('Send Notification to Magento from TPP BackOffice via ' + paymentType + ' with ' + typeCC, function(test) {
	phantom.clearCookies();
	var	data = "",
		output = "",
		orderID = casper.getOrderId();
		// orderID = "13257145000027";

	/* Same function for getting data request from the details */
	casper.openingNotif = function(status) {
		if(status != "116")
			this.echo("Opening Notification details with status " + status + "...", "INFO");
		this.click(x('//tr/td/span[text()="' + status + '"]/parent::td/following-sibling::td[@class="cell-right"]/a'));
		test.info("Done");
	};
	/* Getting data request from the details */
	casper.gettingData = function(status) {
		this.echo("Getting data request from details...", "INFO");
		this.waitUntilVisible('div#fsmodal', function success() {
			data = this.fetchText('textarea.copy-transaction-message-textarea');
			test.assertNotEquals(data.indexOf("status=" + status), -1, "Data request captured !");
			this.click("div.modal-backdrop");
		}, function fail() {
			test.assertVisible('div#fsmodal', "Modal window exists");
		});
	};
	/* Executing shell command for posting POST data request to Magento server */
	casper.execCommand = function() {
		data = data.replace(/\n/g, '&');
		child = spawn('/bin/bash', ['bin/generator/generator.sh', data]);
		child.stdout.on('data', function(out) {
			casper.wait(3000, function() {
				if(out.indexOf("CURL") != -1)
					this.echo(out.trim(), "INFO");
				else if(out.indexOf("200") != -1 || out.indexOf("503") != -1)
					test.info("Done");
				output = out;
			});
		});
		child.stderr.on('data', function(err) {
			casper.wait(2000, function() {
				this.echo(err, "WARNING");
			});
		});
	};
	/* Testing HTTP Status Code of the shell command */
	casper.checkHTTPCurl = function() {
		try {
			test.assertNotEquals(output.indexOf("200"), -1, "Correct HTTP Status Code 200 from CURL command !");
		} catch(e) {
			if(output.indexOf("503") != -1)
				test.fail("Failure on HTTP Status Code from CURL command: 503");
			else
				test.fail("Failure on HTTP Status Code from CURL command: " + output.trim());
		}
	};
	/* Checking status notification from Magento server on the order */
	casper.checkNotifMagento = function(status) {
		try {
			test.assertExists(x('//div[@id="order_history_block"]/ul/li[contains(., "Notification from Hipay: status: code-' + status + '")][position()=last()]'), "Notification " + status + " captured !");
			var operation = this.fetchText(x('//div[@id="order_history_block"]/ul/li[contains(., "Notification from Hipay: status: code-' + status + '")][position()=last()]/preceding-sibling::li[position()=1]'));
			operation = operation.split('\n')[4].split('.')[0].trim();
			if(status != 118)
				test.assertNotEquals(operation.indexOf('successful'), -1, "Successful operation !");
			else
				test.assertNotEquals(operation.indexOf('accepted'), -1, "Successful operation !");
		} catch(e) {
			if(String(e).indexOf('operation') != -1)
				test.fail("Failure on status operation: '" + operation + "'");
			else
				test.fail("Failure: Notification " + status + " not exists");
		}
	};

	/* Opening URL to HiPay TPP BackOffice */
	casper.start(urlbackend)
	/* Logging on the BackOffice */
	.then(function() {
		this.echo("Accessing and logging to TPP BackOffice...", "INFO");
		this.waitForUrl(/login/, function success() {
			this.fillSelectors('form', {
                'input[name="email"]': loginBackend,
                'input[name="password"]': passBackend
            }, true);
            test.info("Done");
		}, function fail() {
			test.assertUrlMatch(/login/, "Login page exists");
		});
	})
	/* Selecting sub-account related to Magento1 Server */
	.then(function() {
		this.echo("Selecting sub-account...", "INFO");
		this.waitForUrl(/dashboard/, function success() {
			this.thenClick('div#s2id_dropdown-merchant-input>a', function() {
				this.sendKeys('input[placeholder="Account name or API credential"]', "MAGENTO1");
				this.wait(1000, function() {	
					this.click(x('//span[contains(., "HIPAY_RE7_MAGENTO1 -")]'));
				});
			});
		}, function fail() {
			test.assertUrlMatch(/dashboard/, "dashboard page exists");
		});
	})
	/* Opening Transactions tab */
	.then(function() {
		this.waitForUrl(/maccount/, function success() {
			this.click('a.nav-transactions');
			test.info("Done");
		}, function fail() {
			test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
		});
	})
	/* Searching last created order */
	.then(function() {
		this.echo("Finding order # " + orderID + " in order list...", "INFO");
		this.waitForUrl(/manage/, function success() {
            this.evaluate(function(ID) {
            	document.querySelector('input#orderid').value = ID;
            	document.querySelector('input[name="submitorderbutton"]').click();
            }, orderID);
            test.info("Done");
		}, function fail() {
			test.assertUrlMatch(/manage/, "Manage page exists");
		});
	})
	/* Opening Notification tab of this order and opening details on the notification */
	.then(function() {
		this.echo("Opening Notification details with status 116...", "INFO");
		this.waitForSelector('a[href="#payment-notification"]', function success() {
			this.thenClick('a[href="#payment-notification"]', function() {
				this.wait(1000, function() {
					this.openingNotif("116");
				});
			});
		}, function fail() {
			test.assertExists('a[href="#payment-notification"]', "Notifications tab exists");
		});
	})
	.then(function() {
		this.gettingData("116");
	})
	.then(function() {
		this.execCommand();
	})
	.then(function() {
		this.checkHTTPCurl();
	})
	.then(function() {
		this.openingNotif("117");
	})
	.then(function() {
		this.gettingData("117");
	})
	.then(function() {
		this.execCommand();
	})
	.then(function() {
		this.checkHTTPCurl();
	})
	.then(function() {
		this.openingNotif("118");
	})
	.then(function() {
		this.gettingData("118");
	})
	.then(function() {
		this.execCommand();
	})
	.then(function() {
		this.checkHTTPCurl();
	})
	/* Opening admin panel Magento and accessing to details of this order */
	.thenOpen(headlink + "admin/", function() {
		authentification.proceed(test);
		this.waitForSelector(x('//span[text()="Orders"]'), function success() {
			this.echo("Checking status notifications from Magento server..." ,"INFO");
			this.click(x('//span[text()="Orders"]'));
			this.waitForSelector(x('//td[contains(., "' + orderID + '")]'), function success() {
				this.click(x('//td[contains(., "' + orderID + '")]'));
				this.waitForSelector('div#order_history_block', function success() {
					this.checkNotifMagento("116");
				}, function fail() {
					test.assertExists('div#order_history_block', "History block of this order exists");
				});
			}, function fail() {
				test.assertExists(x('//td[contains(., "' + orderID + '")]'), "Order # " + orderID + " exists");
			});
		}, function fail() {
			test.assertExists(x('//span[text()="Orders"]'), "Order tab exists");
		});
	})
	.then(function() {
		this.checkNotifMagento("117");
	})
	.then(function() {
		this.checkNotifMagento("118");
	})
	.run(function() {
        test.done();
    });
});