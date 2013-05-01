install:
	@echo "Installing vendor libraries ...\n"
	@curl -s http://getcomposer.org/installer | php
	@php composer.phar install

build: install
	@echo "Building phpctags ...\n"
	@php -dphar.readonly=0 build.php
	@echo "Moving phpctags to ./bin/phpctags ...\n"
	@chmod +x phpctags.phar
	@mkdir -p ./bin
	@mv phpctags.phar ./bin/phpctags
	@echo "Done! Now, you can move ./phpctags to /usr/bin/phpctags, /usr/local/bin/phpctags or any place you want.\n"

clean:
	@echo "Cleaning old build and vendor libraries ...\n"
	@rm -f ./composer.phar
	@rm -f ./composer.lock
	@rm -rf ./bin
	@rm -rf ./vendor
