//============================================================== //
//===               CHECK MAIL AND PAY THE ORDER             === //
//============================================================== //
exports.checkMail = function checkMail(test) {
    //  Access to mailcatcher URL in container
    casper.thenOpen(URL_MAILCATCHER,
        function success() {
            this.waitForSelector('nav#messages tbody tr:first-child',
                function success() {
                    this.click('nav#messages tr:first-child td');
                    casper.wait(5000,
                        function () {
                            // Switch to detail
                            this.page.switchToChildFrame(0);

                            // Process payment
                            this.waitForSelector(x('//a[@id="pay_order"]'),
                                function success() {
                                    // TODO Test number of order
                                    url = this.getElementAttribute('a#pay_order', 'href');

                                    casper.thenOpen(url,
                                        function success() {
                                            casper.waitForUrl('/payment\/web\/pay/', function () {
                                                test.comment('Redirect to hosted page DONE');

                                                // Fill payment form hosted
                                                pay.proceed(test);

                                                // Check if payment is OK
                                                casper.waitForUrl(BASE_URL + 'checkout/onepage/success/', function () {
                                                    test.assertHttpStatus(200);
                                                    test.assertNotExists('.error-msg','No error message');
                                                    test.pass('HOSTED PAYMENT MOTO SUCCESSFUL')
                                                }, null, 10000);

                                            });
                                        },function fail(){
                                            test.fail('Redirect to hosted page KO')
                                        });
                                },
                                function fail() {
                                    test.assertExists(x('//a[text()=" Je paye ma commande maintenant!"]'));
                                });
                        });

                }, function fail() {
                    test.assertExists('nav#messages tbody tr:first-child');
                });
        },
        function fail() {
            this.echo("Erreur", "ERROR");
        }
    );

}
;

