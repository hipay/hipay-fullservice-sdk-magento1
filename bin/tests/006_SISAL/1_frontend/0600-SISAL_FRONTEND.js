/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SISAL
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = 'HiPay Enterprise Mooney';

casper.test.begin(
  'Test Checkout ' + paymentType + ' with ' + typeCC,
  function (test) {
    phantom.clearCookies();

    casper
      .start(baseURL + 'admin/')
      .then(function () {
        this.logToBackend();
        method.proceed(test, paymentType, 'sisalapi');
      })
      .thenOpen(baseURL, function () {
        this.selectItemAndOptions();
      })
      .then(function () {
        this.addItemGoCheckout();
      })
      .then(function () {
        this.checkoutMethod();
      })
      .then(function () {
        this.billingInformation();
      })
      .then(function () {
        this.shippingMethod();
      })
      .then(function () {
        this.choosingPaymentMethod('method_hipay_sisalapi');
      })
      .then(function () {
        this.orderReview(paymentType);
      })
      /* Fill Sisal formular */
      .then(function () {
        this.fillPaymentFormularByPaymentProduct('sisal');
      })
      .then(function () {
        this.orderResult(paymentType);
      })
      .run(function () {
        test.done();
      });
  }
);
