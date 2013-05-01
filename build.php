<?php

$phar = new Phar('phpctags.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 'phpctags.phar');

$filter = function ($current) {
    $excludes = array(
        'bin/*',
        '.git/*',
        '.gitignore',
        'composer.phar',
        'phpctags.phar',
    );

    foreach($excludes as $exclude) {
        if(fnmatch(__DIR__.DIRECTORY_SEPARATOR.$exclude, $current->getPathName())
            || fnmatch('.', $current->getFileName())
            || fnmatch('..', $current->getFileName())) {
                return false;
        }
    }

    return true;
};

$dir = new RecursiveDirectoryIterator(__DIR__);
$files = new RecursiveCallbackFilterIterator($dir, $filter);

// TODO: This way is easier, but seems mess up the directory structure.
//       Need further investigation.
//
// $phar->buildFromIterator(
//     new RecursiveIteratorIterator($files, RecursiveIteratorIterator::SELF_FIRST),
//     __DIR__);

$iterators = new RecursiveIteratorIterator($files, RecursiveIteratorIterator::SELF_FIRST);
$pattern = "!^".__DIR__."/!";

foreach($iterators as $iterator) {
    if($iterator->isFile()) {
        $phar->addFile($iterator->getPathName(), preg_replace($pattern, '', $iterator->getPathName()));
    }
}

$shebang = "#!/usr/bin/env php\n";
$stub = $phar->createDefaultStub('bootstrap.php');
$phar->setStub($shebang.$stub);
