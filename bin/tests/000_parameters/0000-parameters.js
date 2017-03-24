var fs = require('fs'),
	childProcess = require("child_process"),
	x = require('casper').selectXPath,
	defaultViewPortSizes = { width: 1920, height: 1080 },
	headlink = casper.cli.get('url'),
	mailcatcher = casper.cli.get('url-mailcatcher'),
	typeCC = casper.cli.get('type-cc');

casper.test.begin('Parameters', function(test) {
	casper.userAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
	casper.options.viewportSize = {width: defaultViewPortSizes["width"], height: defaultViewPortSizes["height"]};

	casper.echo('Paramètres chargés !', 'INFO');
	test.done();
});
	