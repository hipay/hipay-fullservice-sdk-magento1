casper.test.begin('Test Checkout formular', function(test) {
	phantom.clearCookies();

	casper.start("https://stage-secure-gateway.hipay-tpp.com/payment/web/pay/visa/0b706112-db9c-4a68-8e87-64ec40eb766c")
	.then(function() {
		this.capture('ok.png');
		this.echo(this.exists('form#form-payment'), "INFO");
	})
	.run(function() {
		test.done();
	})
});