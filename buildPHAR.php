<?php
// fnmatch implementation taken from:
// https://www.php.net/manual/en/function.fnmatch.php#100207
if (!function_exists('fnmatch'))
    define('FNM_PATHNAME', 1);
    define('FNM_NOESCAPE', 2);
    define('FNM_PERIOD', 4);
    define('FNM_CASEFOLD', 16);

    function fnmatch($pattern, $string, $flags=0)
    {
        return pcre_fnmatch($pattern, $string, $flags);
    }
}

function pcre_fnmatch($pattern, $string, $flags = 0)
{
    $modifiers = null;
    $transforms = array(
        '\*' => '.*',
        '\?' => '.',
        '\[\!' => '[^',
        '\[' => '[',
        '\]' => ']',
        '\.' => '\.',
        '\\' => '\\\\'
    );

    // Forward slash in string must be in pattern:
    if ($flags & FNM_PATHNAME) {
        $transforms['\*'] = '[^/]*';
    }

    // Back slash should not be escaped:
    if ($flags & FNM_NOESCAPE) {
        unset($transforms['\\']);
    }

    // Perform case insensitive match:
    if ($flags & FNM_CASEFOLD) {
        $modifiers .= 'i';
    }

    // Period at start must be the same as pattern:
    if ($flags & FNM_PERIOD) {
        if (strpos($string, '.') === 0 && strpos($pattern, '.') !== 0) {
            return false;
        }
    }

    $pattern = '#^'
        . strtr(preg_quote($pattern, '#'), $transforms)
        . '$#'
        . $modifiers
    ;

    return (boolean)preg_match($pattern, $string);
}

$phar = new Phar('build/phpctags.phar', 0, 'phpctags.phar');

if (version_compare(PHP_VERSION, '5.4.0') < 0) {
    class RecursiveCallbackFilterIterator extends RecursiveFilterIterator
    {
        public function __construct(RecursiveIterator $iterator, $callback)
        {
            $this->callback = $callback;
            parent::__construct($iterator);
        }

        public function accept()
        {
            $callback = $this->callback;
            return $callback(
                parent::current(),
                parent::key(),
                parent::getInnerIterator()
            );
        }

        public function getChildren()
        {
            return new self(
                $this->getInnerIterator()->getChildren(),
                $this->callback
            );
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

                foreach ($excludes as $exclude) {
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
