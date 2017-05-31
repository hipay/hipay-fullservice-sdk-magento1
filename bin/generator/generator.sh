if [ "$1" = "--help" ] || [ "$1" = "" ]; then
	echo "Usage: sh/bash generator.sh \"<data>\" <hashCode> <serverAddress>"
	echo "Parameter data: the data request that you can find on details from one of the notifications of your order"
	echo "Parameter hashCode: the hashCode value in order to grant Magento server for notifications"
	echo "Parameter serverAddress: the serverAdress value is your Magento 1 server address"
else
	if [ "$2" = "randomString" ]; then
		echo "Simulating 403 error with bad signature during CURL Command..."
	else
		echo "Executing CURL command in order to simulate a HTTP POST request to Magento server..."
	fi

	status=$(curl -H "X-ALLOPASS-SIGNATURE: $2" -d "$1" -sw '%{http_code}' $3)

	echo $status
fi