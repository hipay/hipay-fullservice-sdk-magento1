#!/bin/bash

echo "STEP 1 : DEPLOY ON DOCKER MACHINE"

composer install

if [ $? -eq 0 ]; then
	echo "Composer installed !"
else
	rm -rf vendor/
	composer install
	if [ $? -eq 0 ]; then
		echo "Composer installed !"
	else
		echo "Error during installing composer"
		exit
	fi
fi

composer package

if [ -f "dist/"*".tgz" ]; then
	echo "Composer packaged !"
else
	echo "Error during packaging composer"
fi

mkdir $CIRCLE_ARTIFACTS/continuous_deployement

cp dist/*.tgz $CIRCLE_ARTIFACTS/continuous_deployement/