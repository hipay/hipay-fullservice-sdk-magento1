casper.test.begin('Functions', function(test) {
	casper.test.on('fail', function() {
		casper.echo('Tests réussis : ' + test.currentSuite.passes.length, 'WARNING');
	});
	casper.echo('Fonctions chargées !', 'INFO');
    test.done();
});