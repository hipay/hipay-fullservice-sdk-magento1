exports.proceedMotoSendMail = function proceedMotoSendMail(test, state) {

//============================================================== //
//===               SELECT SYSTEM CONFIGURATION               === //
//============================================================== //
    casper.then(
        function () {
            // Select Panel orders
            // Select the last product in grid
            test.comment('Select System/Configuration panel');
            this.click(x('//span[text()="System"]'));
            casper.waitForSelector(x('//span[text()="Configuration"]'),
                function success() {
                    this.click(x('//span[text()="Configuration"]'));
                    casper.waitForSelector(x('//span[text()[contains(.,"HiPay Fullservice")]]'),
                        function success() {
                            this.click(x('//span[text()[contains(.,"HiPay Fullservice")]]'));

                            casper.waitForSelector(x('//span[text()[contains(.,"HiPay Fullservice")]]'),
                                function success() {
                                    this.click('#hipay_hipay_api_moto-head');

                                    this.sendKeys('#hipay_hipay_api_moto_moto_send_email', 'Yes');

                                    // Fill configuration
                                    this.fill('form#config_edit_form', {
                                        'groups[hipay_api_moto][fields][moto_send_email][value]': state,
                                    }, true);

                                    // Save configuration
                                    this.click(x('//span[text()="Save Config"]'))

                                    // Test si l'enregistrement is done
                                    casper.waitForSelector(x('//span[text()[contains(.,"The configuration has been saved.")]]'),
                                        function success() {
                                                test.pass('Configuration is ok');
                                        }, function fail() {
                                                test.fail('Configuration is fail');
                                        });
                                }, function fail() {
                                });
                        },
                        function fail() {
                            test.assertExists(x('//span[text()[contains(.,"HiPay Fullservice")]]'));
                        }
                        , 20000);
                },
                function fail() {
                    test.assertExists(x('//span[text()="Configuration"]'));
                }
                , 20000);
        }
    );

};

