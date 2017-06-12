casper.test.begin('test Child Process', function(test) {
	phantom.clearCookies();

	var data = "state=completed&reason=&test=true&mid=00001332858&attempt_id=1&authorization_code=test123&transaction_reference=134379725897&date_created=2017-05-24T15%3A00%3A41%2B0000&date_updated=2017-05-24T15%3A00%3A49%2B0000&date_authorized=2017-05-24T15%3A00%3A45%2B0000&status=118&message=Captured&authorized_amount=232.33&captured_amount=232.33&refunded_amount=0.00&decimals=2&currency=EUR&ip_address=172.17.0.1&ip_country=&device_id=&cdata1=&cdata2=&cdata3=&cdata4=&cdata5=&cdata6=&cdata7=&cdata8=&cdata9=&cdata10=&avs_result=&cvc_result=&eci=7&payment_product=visa&payment_method%5Btoken%5D=f39bfab2b6c96fa30dcc0e55aa3da4125a49ab03&payment_method%5Bbrand%5D=VISA&payment_method%5Bpan%5D=411111%2A%2A%2A%2A%2A%2A1111&payment_method%5Bcard_holder%5D=JOHN+DOE&payment_method%5Bcard_expiry_month%5D=02&payment_method%5Bcard_expiry_year%5D=2021&payment_method%5Bissuer%5D=JPMORGAN+CHASE+BANK%2C+N.A.&payment_method%5Bcountry%5D=US&three_d_secure%5Beci%5D=6&three_d_secure%5Benrollment_status%5D=N&three_d_secure%5Benrollment_message%5D=Cardholder+Not+Enrolled&three_d_secure%5Bauthentication_status%5D=X&three_d_secure%5Bauthentication_message%5D=Unable+to+authenticate&three_d_secure%5Bauthentication_token%5D=&three_d_secure%5Bxid%5D=&fraud_screening%5Bscoring%5D=600&fraud_screening%5Bresult%5D=accepted&fraud_screening%5Breview%5D=&order%5Bid%5D=5898145000009&order%5Bdate_created%5D=2017-05-24T15%3A00%3A41%2B0000&order%5Battempts%5D=1&order%5Bamount%5D=232.33&order%5Bshipping%5D=5.00&order%5Btax%5D=17.33&order%5Bdecimals%5D=2&order%5Bcurrency%5D=EUR&order%5Bcustomer_id%5D=&order%5Blanguage%5D=en_GB&order%5Bemail%5D=email%40yopmail.com"

	casper.execCommand = function() {
		child = spawn('/bin/bash', ['bin/generator/generator.sh', data]);
		try {
			child.stdout.on('data', function(out) {
				casper.wait(3000, function() {
					if(out.indexOf("CURL") != -1)
						this.echo(out.trim(), "INFO");
					else if(out.indexOf("200") != -1 || out.indexOf("503") != -1)
						test.info("Done");
					output = out;
				});
			});
			child.stderr.on('data', function(err) {
				casper.wait(2000, function() {
					this.echo(err, "WARNING");
				});
			});
		} catch(e) {
			this.echo("Error during file execution! Retry command...", "WARNING");
			this.execCommand();
		}
	};

	casper.start(urlBackend)
	.then(function() {
		this.execCommand();
	})
	.run(function() {
        test.done();
    });
});