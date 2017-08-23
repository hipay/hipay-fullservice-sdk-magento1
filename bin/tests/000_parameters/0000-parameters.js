var fs = require('fs'),
	utils = require('utils'),
	childProcess = require("child_process"),
	spawn = childProcess.spawn,
	x = require('casper').selectXPath,
	defaultViewPortSizes = { width: 1920, height: 1080 },
	headlink = casper.cli.get('url'),
	urlMailCatcher = casper.cli.get('url-mailcatcher'),
	typeCC = casper.cli.get('type-cc'),
	loginBackend = casper.cli.get('login-backend'),
	passBackend = casper.cli.get('pass-backend'),
	loginPaypal = casper.cli.get('login-paypal'),
	passPaypal = casper.cli.get('pass-paypal'),
	countryPaypal = 'US',
	order = casper.cli.get('order'),
	orderID = 0,
	cardsNumber = [
		"4111111111111111", // VISA
		"5234131094136942", // CB & MC
		"4000000000000002" // VISA 3DSecure
	],
	ibanNumber = [
		"GB29NWBK60161331926819",
		"FR1420041010050500013M02606"
	],
	bicNumber = [
		"ALBLGB2L",
		"PSSTFRPPXXX"
	],
	headerModule = "../../Modules/",
	urlBackend = "https://stage-merchant.hipay-tpp.com/",
	method = require(headerModule + 'step-config-method'),
    checkout = require(headerModule + 'step-checkout'),
    authentification = require(headerModule + 'step-authentification'),
    configuration = require(headerModule + 'step-configuration'),
    mailcatcher = require(headerModule + 'step-mailcatcher'),
    pay = require(headerModule + 'step-pay-hosted'),
    pathHeader = "bin/tests/",
    pathErrors = pathHeader + "errors/",
    allowedCurrencies = [
    	{ currency: 'EUR', symbol: '€' },
    	{ currency: 'USD', symbol: '$' }
    ],
    currentCurrency = allowedCurrencies[0],
    generatedCPF = "373.243.176-26";

casper.test.begin('Parameters', function(test) {
	/* Set default viewportSize and UserAgent */
	casper.userAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
	casper.options.viewportSize = {width: defaultViewPortSizes["width"], height: defaultViewPortSizes["height"]};

	//casper.options.waitTimeout = 10000;

	/* Set default card type if it's not defined */
	if(typeof typeCC == "undefined")
		typeCC = "VISA";

	/* Say if BackOffice TPP credentials are set or not */
	if(loginBackend != "" && passBackend != "")
		test.info("Backend credentials set");
	else
		test.comment("No Backend credentials");

	if(loginPaypal != "" && passPaypal != "")
		test.info("PayPal credentials set");
	else
		test.comment("No PayPal credentials");

	casper.echo('Paramètres chargés !', 'INFO');
	test.done();
});
	