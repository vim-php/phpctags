<?php

$phar = new Phar('build/phpctags.phar', 0, 'phpctags.phar');

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
