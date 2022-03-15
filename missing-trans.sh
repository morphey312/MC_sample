#!/bin/bash

BASEPATH="./resources/js"
BASEPATHOC="./database/seeds/card-templates"
MAKECSV="1"

if [ "$1" != "" ]
then
    BASEPATH="$1"
fi

# main file UA

php artisan translate:dump "./current.tmp" "$BASEPATH/lang/ua.js"
php artisan translate:grab --path="$BASEPATH" --unique --with-baked --only-missing --merge="./current.tmp" --languages=UA "./missing.tmp"

echo "resources/js/lang/ua.js"
echo ">>>"
echo
php artisan translate:build --allow-empty "./missing.tmp" "php://stdout"
echo
echo "<<<"

rm "./current.tmp"
if [ "$MAKECSV" == "1" ]
then
    mv "./missing.tmp" "./missing-ua.csv"
else
    rm "./missing.tmp"
fi


# main file SK

php artisan translate:dump "./current.tmp" "$BASEPATH/lang/sk.js"
php artisan translate:grab --path="$BASEPATH" --unique --with-baked --only-missing --merge="./current.tmp" --languages=SK "./missing.tmp"

echo "resources/js/lang/sk.js"
echo ">>>"
echo
php artisan translate:build --allow-empty "./missing.tmp" "php://stdout"
echo
echo "<<<"

rm "./current.tmp"
if [ "$MAKECSV" == "1" ]
then
    mv "./missing.tmp" "./missing-sk.csv"
else
    rm "./missing.tmp"
fi


# outpatient card file UA

php artisan translate:dump "./current.tmp" "$BASEPATH/lang/outpatient-card-ua.js"
php artisan translate:grab --path="$BASEPATHOC" --unique --only-missing --merge="./current.tmp" --languages=UA "./missing.tmp"

echo "resources/js/lang/outpatient-card-ua.js"
echo ">>>"
echo
php artisan translate:build --allow-empty "./missing.tmp" "php://stdout"
echo
echo "<<<"

rm "./current.tmp"
if [ "$MAKECSV" == "1" ]
then
    mv "./missing.tmp" "./missing-outpatient-card-ua.csv"
else
    rm "./missing.tmp"
fi


# outpatient card file SK

php artisan translate:dump "./current.tmp" "$BASEPATH/lang/outpatient-card-sk.js"
php artisan translate:grab --path="$BASEPATHOC" --unique --only-missing --merge="./current.tmp" --languages=SK "./missing.tmp"

echo "resources/js/lang/outpatient-card-sk.js"
echo ">>>"
echo
php artisan translate:build --allow-empty "./missing.tmp" "php://stdout"
echo
echo "<<<"

rm "./current.tmp"
if [ "$MAKECSV" == "1" ]
then
    mv "./missing.tmp" "./missing-outpatient-card-sk.csv"
else
    rm "./missing.tmp"
fi