#!/bin/bash
echo "1. Changing to phpctags project root"
cd ..

echo "2. Checking composer.json exist or not"
if [ ! -e composer.json ]; then
    echo "composer.json does not exist, quit the building process."
    exit 0;
fi

echo "3. Installing vendor libraries"
curl -s http://getcomposer.org/installer | php
php composer.phar install

echo "4. Making executable phpctags"
php -dphar.readonly=0 ./build/empir make phpctags.phar bootstrap.php . --fexclude="./build/excluding-list.txt"
mv phpctags.phar phpctags
chmod +x phpctags

echo "5. Moving phpctags to ./bin/phpctags"
if [ ! -d bin ]; then
    mkdir -p bin
fi
mv phpctags ./bin/phpctags

echo "Done! Now, you can move ./bin/phpctags to /usr/bin/phpctags or anywhere you want."
cd -
