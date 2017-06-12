exports.proceed = function proceed(test) {
    /* Connection to admin panel */
    casper.then(function() {
        this.echo("Connecting to admin panel...", "INFO");
        this.waitForSelector("#loginForm", function success() {
            this.fillSelectors('form#loginForm', {
                'input[name="login[username]"]': 'hipay',
                'input[name="login[password]"]': 'hipay123'
            }, false);
            this.click('.form-buttons input[type=submit]');
            this.waitForSelector(".header-top", function success() {
                test.info("Done");
            }, function fail() {
                test.assertExists(".error-msg", "Incorrect credentials !");
            }, 20000);
        }, function fail() {
            this.waitForUrl(/admin\/dashboard/, function success() {
                test.info("Already logged to admin panel !");
            }, function fail() {
                test.assertUrlMatch(/admin\/dashboard/, "Admin dashboard exists");
            });
        });
    });
};