<?php

$phar = new Phar('build/phpctags.phar', 0, 'phpctags.phar');

if (version_compare(PHP_VERSION, '5.4.0') < 0) {
    class RecursiveCallbackFilterIterator extends RecursiveFilterIterator {
        public function __construct ( RecursiveIterator $iterator, $callback ) {
            $this->callback = $callback;
            parent::__construct($iterator);
        }

        public function accept () {
            $callback = $this->callback;
            return $callback(parent::current(), parent::key(), parent::getInnerIterator());
        }

        public function getChildren () {
            return new self($this->getInnerIterator()->getChildren(), $this->callback);
        }
    }
}

$phar->buildFromIterator(
    new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator(
                getcwd(),
                FilesystemIterator::SKIP_DOTS
            ),
            function ($current) {
                $excludes = array(
                    '.*',
                    'tags',
                    'build/*',
                    'tests/*',
                    'Makefile',
                    'bin/phpctags',
                    'buildPHAR.php',
                );

                foreach($excludes as $exclude) {
                    if (fnmatch(getcwd().'/'.$exclude, $current->getPathName())) {
                        return false;
                    }
                }

                return true;
            }
        )
    ),
    getcwd()
);

$phar->setStub(
    "#!/usr/bin/env php\n".$phar->createDefaultStub('bootstrap.php')
);
