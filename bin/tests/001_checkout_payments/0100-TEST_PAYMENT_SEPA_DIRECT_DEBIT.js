casper.test.begin("Test Checkout on Hipay Sepa Direct Debit Payment Information", function(test) {
	phantom.clearCookies();

	casper.start("https://stage-secure-gateway.hipay-tpp.com/payment/web/pay/visa/2e5b819b-2e1f-4fb3-8a8a-aee695669f64")
	.then(function() {
		
	})
	.run(function() {
		if(!casper.cli.get("circle"))
			this.echo('Tests réussis : ' + test.currentSuite.passes.length, 'INFO');
		test.done();
	});
});