exports.proceed = function proceed(test, iframe) {

    /* Fill formular according to the template, with or without iframe */
    casper.fillHostedForm = function fillHostedForm() {
        var holder = "MC",
            month = "12",
            year = "2020",
            code = "500";
        this.wait(10000, function() {
            if(this.exists('iframe#tokenizerFrame')) {
                this.withFrame(0, function() {
                    this.fillSelectors('form#tokenizerForm', {
                        'input[name="tokenizerForm:cardNumber"]': cardsNumber[0],
                        'input[name="tokenizerForm:cardHolder"]': holder,
                        'select[name="tokenizerForm:cardExpiryMonth"]': month,
                        'select[name="tokenizerForm:cardExpiryYear"]': year,
                        'input[name="tokenizerForm:cardSecurityCode"]': code
                    }, false);
                });
            }
            else {
                this.withFrame(0, function() {
                    this.fillSelectors('form#form-payment', {
                        'input[name="cardNumber"]': cardsNumber[0],
                        'input[name="cardHolder"]': holder,
                        'select[name="cardExpiryMonth"]': month,
                        'select[name="cardExpiryYear"]': year,
                        'input[name="cardSecurityCode"]': code
                    }, false);
                });
            }
            this.thenClick('#submit-button', function() {
                test.info("Done");
            });
        });
    };

    /* Check template formular and choose card type */
    casper.then(function() {
        this.echo("Filling hosted payment formular...", "INFO");
        if(!iframe)
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
        this.waitForSelector('input#payment-product-switcher-visa', function success() {
            this.evaluate(function() {
                document.querySelector('input#payment-product-switcher-visa').click();
            });
            this.fillHostedForm();
        }, function fail() {
            this.echo("VISA input doesn't exists. Checking for select field...", 'WARNING');
            this.waitForSelector('select#payment-product-switcher', function success() {
                this.warn("OK. This payment template is deprecated");
                this.fillSelectors('#form-payment', {
                    'select[name="paymentproductswitcher"]': "visa"
                });
                this.fillHostedForm();
            }, function fail() {
                test.assertExists('select#payment-product-switcher', "Select field exists");
            });
        });
    });
};