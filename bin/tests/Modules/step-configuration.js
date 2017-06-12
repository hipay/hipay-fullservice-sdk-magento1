exports.proceedMotoSendMail = function proceedMotoSendMail(test, state) {
    /* Selection configuration system */
    casper.then(function() {
        this.echo("Selecting configuration system...", "INFO");
        this.waitForSelector(x('//span[text()="Configuration"]'), function success() {
            this.click(x('//span[text()="Configuration"]'));
            this.waitForSelector(x('//span[contains(.,"HiPay Enterprise")]'), function success() {
                this.click(x('//span[contains(.,"HiPay Enterprise")]'));
                this.waitForSelector('#hipay_hipay_api_moto-head', function success() {
                    this.click('#hipay_hipay_api_moto-head');
                    var valueMOTO = this.evaluate(function() { return document.querySelector('select[name="groups[hipay_api_moto][fields][moto_send_email][value]"]').value; });
                    if(valueMOTO != state) {
                        this.fillSelectors('form#config_edit_form', {
                            'select[name="groups[hipay_api_moto][fields][moto_send_email][value]"]': state
                        }, false);
                        this.click(x('//span[text()="Save Config"]'));
                        this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
                            if(state == 1)
                                test.info("MOTO Configuration done");
                            else
                                test.info("Normal Configuration done");
                        }, function fail() {
                            test.fail('Failed to apply MOTO Configuration on the system');
                        },10000);
                    }
                    else {
                        if(state == 1)
                            test.info("MOTO configuration already done");
                        else
                            test.info("Normal configuration already done");
                    }
                }, function fail() {
                    test.assertExists('#hipay_hipay_api_moto-head', "HiPay Enterprise MOTO Configuration tab exists");
                });
            }, function fail() {
                test.assertExists(x('//span[contains(.,"HiPay Enterprise")]'), "HiPay Enterprise menu exists");
            }, 20000);
        }, function fail() {
            test.assertExists(x('//span[text()="Configuration"]'), "Configuration menu exists");
        }, 20000);
    });
};