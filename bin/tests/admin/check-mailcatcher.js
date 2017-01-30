exports.checkMail = function checkMail(test) {

    casper.thenOpen('http://smtp:1080/ ',
        function success() {
            this.waitForSelector('nav#messages tbody tr:first-child',
                function success() {
                    this.click('nav#messages tr:first-child');

                    this.waitForSelector(x('//span[text()=" Je paye ma commande maintenant!"]'),
                        function success() {
                            // Test si ler dernier mail recu correspond à la commande
                            this.click(x('//span[text()=" Je paye ma commande maintenant!"]'));
                            test.comment('Click in email to access hosted page');

                            casper.waitForUrl('/payment\/web\/pay/', function () {
                                test.comment('Redirect to hosted page DONE');
                            });
                        },
                        function fail() {
                            test.assertExists(x('//span[text()=" Je paye ma commande maintenant!"]'));
                        });


                }, function fail() {
                    test.assertExists('nav#messages tbody tr:first-child');
                });
        },
        function fail() {

        }
    );

}
;

