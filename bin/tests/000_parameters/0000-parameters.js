var fs = require('fs'),
	childProcess = require("child_process"),
	spawn = childProcess.spawn,
	x = require('casper').selectXPath,
	defaultViewPortSizes = { width: 1920, height: 1080 },
	headlink = casper.cli.get('url'),
	urlMailCatcher = casper.cli.get('url-mailcatcher'),
	typeCC = casper.cli.get('type-cc'),
	loginBackend = casper.cli.get('login-backend'),
	passBackend = casper.cli.get('pass-backend'),
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
	urlbackend = "https://stage-merchant.hipay-tpp.com/",
	method = require(headerModule + 'step-config-method'),
    checkout = require(headerModule + 'step-checkout'),
    authentification = require(headerModule + 'step-authentification'),
    configuration = require(headerModule + 'step-configuration'),
    mailcatcher = require(headerModule + 'step-mailcatcher'),
    pay = require(headerModule + 'step-pay-hosted');

casper.test.begin('Parameters', function(test) {
	casper.userAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
	casper.options.viewportSize = {width: defaultViewPortSizes["width"], height: defaultViewPortSizes["height"]};

	casper.echo('Paramètres chargés !', 'INFO');
	test.done();
});
	