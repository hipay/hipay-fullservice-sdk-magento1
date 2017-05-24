var paymentType = "HiPay Enterprise Credit Card";

casper.test.begin('Send Notification to Magento from TPP BackOffice via ' + paymentType + ' with ' + typeCC, function(test) {
	phantom.clearCookies();
	var	data = "",
		hash = "",
		output = "",
		orderID = casper.getOrderId();
		// orderID = "28997145000067";

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
			//hash = this.fetchText(x('//tr/td/pre[contains(., "Hash")]')).split('\n')[7].split(':')[1].trim();
			data = this.fetchText('textarea.copy-transaction-message-textarea');
			try {
				//test.assert(hash.length > 1, "Hash Code captured !");
				test.assertNotEquals(data.indexOf("status=" + status), -1, "Data request captured !");
			} catch(e) {
				if(String(e).indexOf("Hash") != -1)
					test.fail("Failure: Hash Code not captured");
				else
					test.fail("Failure: data request not captured");
			}
			this.click("div.modal-backdrop");
		}, function fail() {
			test.assertVisible('div#fsmodal', "Modal window exists");
		});
	};
	/* Executing shell command for posting POST data request to Magento server */
	casper.execCommand = function(code, retry) {
		data = data.replace(/\n/g, '&');
		child = spawn('/bin/bash', ['bin/generator/generator.sh', data, code]);
		try {
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
		} catch(e) {
			if(!retry) {
				this.echo("Error during file execution! Retry command...", "WARNING");
				this.execCommand(code, true);
			}
			else
				test.fail("Failure on child processing command");
		}
	};
	/* Testing HTTP Status Code of the shell command */
	casper.checkHTTPCurl = function(httpCode) {
		try {
			test.assertNotEquals(output.indexOf(httpCode), -1, "Correct HTTP Status Code " + httpCode + " from CURL command !");
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
	casper.start(urlBackend)
	/* Logging on the BackOffice */
	.then(function() {
		this.logToBackend();
	})
	/* Selecting sub-account related to Magento1 Server */
	.then(function() {
		this.selectAccountBackend("OGONE_DEV");
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
		this.execCommand(hash);
	})
	.then(function() {
		this.checkHTTPCurl("200");
	})
	.then(function() {
		this.openingNotif("117");
	})
	.then(function() {
		this.gettingData("117");
	})
	.then(function() {
		this.execCommand(hash);
	})
	.then(function() {
		this.checkHTTPCurl("200");
	})
	.then(function() {
		this.openingNotif("118");
	})
	.then(function() {
		this.gettingData("118");
	})
	.then(function() {
		this.execCommand(hash);
	})
	.then(function() {
		this.checkHTTPCurl("200");
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
	/* Check HTTP Code 403 from shell command for notification to Magento server */
	.then(function() {
		if(typeof order == "undefined")
			this.execCommand("randomString");
	})
	.then(function() {
		if(typeof order == "undefined")
			this.checkHTTPCurl("403");
	})
	.run(function() {
        test.done();
    });
});