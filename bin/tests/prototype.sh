green='\033[0;32m'
yellow='\033[0;33m'
red='\033[0;31m'
blue='\033[0;36m'
noColor='\033[0m'

failfastDefault='n'
relance='y'
autreDefault='y'
autre='y'
cacheDefault='n'
debugDefault='n'

clear

printf "${green}************************************************************
******************* Magento1 + CasperJS *******************
************************************************************${noColor}\n"
printf "\n${yellow}MENU${noColor}"
printf "\n${green}1${noColor}: Teste tout les moyens de paiement sur chaque environnement\n${green}2${noColor}: Teste chaque environnement d'un moyen de paiement
${green}3${noColor}: Teste un environnement précis d'un moyen de paiement\n${green}4${noColor}: Teste un ou plusieurs environnements de moyens de paiement
${green}5${noColor}: Teste un environnement précis d'un moyen de paiement en mode DEBUG\n${green}CTRL+C${noColor} pour sortir\n\n"

menuDefault='3'
read -p "Saisissez le numéro correspondant à votre demande. Default: [$(printf $yellow)Teste un fichier précis$(printf $noColor)] : " menu
menu=${menu:-$menuDefault}

BASE_URL="http://localhost:8095/"
URL_MAILCATCHER="http://localhost:1095/"
header="bin/tests/"
pathPreFile=${header}000*/*.js
pathDir=${header}00[123]*
pathFile=${pathDir}/[0-1]*/[0-1][0-9][0-9][0-9]-*.js

case $menu in
	1)
		printf "\n"
		read -p "Effacer le cache pour les tests (y/n) Default: [$(printf $yellow)${cacheDefault}$(printf $noColor)] : " cache
		cache=${cache:-$cacheDefault}

		printf "\n"
		read -p "Activer le fail-fast (y/n) Default: [$(printf $yellow)${failfastDefault}$(printf $noColor)] : " failfast
		failfast=${failfast:-$failfastDefault}

		clear

		if [[ "$cache" == "y" ]]; then
			if [ -d ~/.local/share/Ofi\ Labs/PhantomJS/ ]; then
				rm -rf ~/.local/share/Ofi\ Labs/PhantomJS/*
				echo "Cache cleared !\n"
			else
				echo "Pas de cache à effacer !\n"
			fi
		fi

		if [[ "$failfast" == "y" ]]; then
			printf "${yellow}Exécution des tests CasperJS avec fail-fast${noColor}\n\n"
			casperjs test $pathPreFile ${pathDir}/*/*.js --url=$BASE_URL --type-cc=VISA --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --xunit=${header}result.xml \
			&& casperjs test $pathPreFile ${header}001*/*/*.js --url=$BASE_URL --type-cc=MasterCard --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND
		else
			printf "${yellow}Exécution des tests CasperJS sans fail-fast${noColor}\n\n"
			casperjs test $pathPreFile ${pathDir}/*/*.js --url=$BASE_URL --type-cc=VISA --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --xunit=${header}result.xml \
			&& casperjs test $pathPreFile ${header}001*/*/*.js --url=$BASE_URL --type-cc=MasterCard --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND
		fi
		;;
	2)
		echo "wait"
		;;
	3)
		echo "wait"
		;;
	4)
		echo "wait"
		;;
	5)
		echo "wait"
		;;
	*)
		printf "${red}Commande invalide ! Veuillez réessayez...${noColor}"
		sleep 2
		bash $header'prototype.sh';;
esac