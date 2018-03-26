#!/bin/bash

if [ $? -eq 0 ]; then
	echo "Composer installed !"
else
	composer install
	if [ $? -eq 0 ]; then
		echo "Composer installed !"
	else
		echo "Error during composer install"
		exit
	fi
fi

composer package

if [ -f "dist/"*".tgz" ]; then
	echo "Composer packaged !"
else
	echo "Error during packaging composer"
fi
