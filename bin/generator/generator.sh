if [ "$1" = "--help" ]; then
	echo "Usage: sh/bash generator.sh \" data \""
	echo "Parameter data: the data request that you can find on details from one of the notifications of your order"
else
	echo "Executing CURL command in order to simulate a HTTP POST request to Magento server..."

	status=$(curl -d "$1" -sw '%{http_code}' http://localhost:8095/hipay/notify)

	echo $status
fi