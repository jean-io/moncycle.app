#!/bin/bash

## moncycle.app
##
## licence Creative Commons CC BY-NC-SA
##
## https://www.moncycle.app
## https://github.com/jean-io/moncycle.app

DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
cd $DIR

curl -L "http://www.fpdf.org/fr/dl.php?v=184&f=zip" > ./fpdf.zip
curl -L "https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.6.0.zip" > ./phpmailer.zip
curl -L "https://code.jquery.com/jquery-3.6.0.min.js" > ./jquery.js
curl -L "https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js" > ./chart.js

sha512sum -c ./checksum/*.sha512

if [[ $? -eq 1 ]]
then
	exit 1
fi

unzip -o ./fpdf.zip -d ./fpdf
rm -v ./fpdf.zip

unzip -o ./phpmailer.zip -d ./phpmailer
rm -v ./phpmailer.zip
mv ./phpmailer/PHPMailer*/* ./phpmailer

echo -e "fini!"
