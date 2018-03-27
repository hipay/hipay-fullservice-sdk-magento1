/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : AURA
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Aura";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    // phantom.clearCookies();

    casper.on('url.changed', function(url) {
        test.comment(url);
    });


    casper.start(baseURL + "admin/")
     .then(function() {
         this.logToBackend();
         method.proceed(test, paymentType, "aura");
     })
     .then(function() {
         this.setCurrencySetup('BRL');
     })
    .thenOpen(baseURL, function() {
        this.selectItemAndOptions();
    })
    .then(function() {
        this.addItemGoCheckout();
    })
    .then(function() {
        this.checkoutMethod();
    })
    .then(function() {
        this.billingInformation("BR");
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
        this.choosingPaymentMethod('method_hipay_aura');
    })
    .then(function() {
        this.waitUntilVisible('div#payment-buttons-container>button', function success() {
            this.fillSelectors("form#co-payment-form", {
                'input[name="payment[national_identification_number]"]': generatedCPF
            }, false);
            this.click("div#payment-buttons-container>button");
            test.info("Done");
        }, function fail() {
            test.assertVisible("div#payment-buttons-container>button", "Button payment");
        }, 2000);
    })
    .then(function() {
        this.orderReview(paymentType);
    })
    .then(function() {
        this.echo("Filling payment page...", "INFO");
        this.waitForUrl(/payment$/, function success() {
             this.click('input[name="btnSubmit"]');
             this.waitUntilVisible(x('//h2[text()="Payment resume"]'), function success() {
                 this.click('input#optStatusAccepted');
                 this.click('input#btnConfirmPayment');
                 this.waitForSelector('input#new-sexy-button', function success() {
                     this.click('input#new-sexy-button');
                     test.info("Done");
                 }, function fail() {
                     test.assertVisible('input#new-sexy-button', "Transaction informations alert exists");
                 },20000);
             }, function fail() {
                 test.assertVisible(x('//h2[text()="Payment resume"]'), "Modal window 'Payment resume' exists");
             });
         }, function fail() {
             test.comment(this.currentUrl);
             this.thenOpen("https://sandbox.astropaycard.com/test_bank/payment", function() {
                 this.wait(2000, function() {
                     this.capture(pathErrors + 'ok.png');
                 });
             });
              test.assertUrlMatch(/payment$/, "AURA payment page exists");
         }, 15000);
    })
    .then(function() {
        this.echo("Checking order success...", "INFO");
        this.waitForUrl(/retorno/, function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            this.setOrderId(false);
        }, function fail() {
            this.echo("Success payment page doesn't exists. Checking for pending payment page...", 'WARNING');
                test.assertUrlMatch(/retorno/, "Checkout result page exists");
        }, 10000);
    })
    .thenOpen(baseURL + 'admin/', function() {
        this.logToBackend();
    })
    .then(function() {
        this.setCurrencySetup('EUR');
    })
    .run(function() {
        test.done();
    });
});