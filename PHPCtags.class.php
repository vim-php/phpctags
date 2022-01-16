<?php
use PhpParser\ParserFactory;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;

class PHPCtags
{
    const VERSION = '0.10.0';

    private $mFile;

    private $mFiles;

    private static $mKinds = array(
        't' => 'trait',
        'c' => 'class',
        'm' => 'method',
        'f' => 'function',
        'p' => 'property',
        'd' => 'constant',
        'v' => 'variable',
        'i' => 'interface',
        'n' => 'namespace',
    );

    private $mParser;
    private $mLines;
    private $mOptions;
    private $tagdata;
    private $cachefile;
    private $filecount;

    public function __construct($options)
    {
        $this->mParser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->mLines = array();
        $this->mOptions = $options;
        $this->filecount = 0;
    }

    public function setMFile($file)
    {
        if (empty($file)) {
            throw new PHPCtagsException('No File specified.');
        }

        if (!file_exists($file)) {
            throw new PHPCtagsException('Warning: cannot open source file "' . $file . '" : No such file');
        }

        if (!is_readable($file)) {
            throw new PHPCtagsException('Warning: cannot open source file "' . $file . '" : File is not readable');
        }

        $this->mFile = realpath($file);
    }

    public static function getMKinds()
    {
        return self::$mKinds;
    }

    public function addFile($file)
    {
        $this->mFiles[realpath($file)] = 1;
    }

    public function setCacheFile($file) {
        $this->cachefile = $file;
    }

    public function addFiles($files)
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    private function getNodeAccess($node)
    {
        if ($node->isPrivate()) return 'private';
        if ($node->isProtected()) return 'protected';
        return 'public';
    }

    /**
     * stringSortByLine
     *
     * Sort a string based on its line delimiter
     *
     * @author Techlive Zheng
     *
     * @access public
     * @static
     *
     * @param string  $str     string to be sorted
     * @param boolean $foldcse case-insensitive sorting
     *
     * @return string sorted string
     **/
    public static function stringSortByLine($str, $foldcase=FALSE)
    {
        $arr = explode("\n", $str);
        if (!$foldcase)
            sort($arr, SORT_STRING);
        else
            sort($arr, SORT_STRING | SORT_FLAG_CASE);
        $str = implode("\n", $arr);
        return $str;
    }

    private static function helperSortByLine($a, $b)
    {
        return $a['line'] > $b['line'] ? 1 : 0;
    }

    private function struct($node, $reset=FALSE, $parent=array())
    {
        static $scope = array();
        static $structs = array();

        if ($reset) {
            $structs = array();
        }

        $kind = $name = $line = $access = $extends = '';
        $implements = array();

        if (!empty($parent)) array_push($scope, $parent);

        if (is_array($node)) {
            foreach ($node as $subNode) {
                $this->struct($subNode);
            }
        } elseif ($node instanceof Stmt\Expression) {
            foreach ($node as $subNode) {
                $this->struct($subNode);
            }
        } elseif ($node instanceof Stmt\Class_) {
            $kind = 'c';
            $name = $node->name;
            $extends = $node->extends;
            $implements = $node->implements;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('class' => $name));
            }
        } elseif ($node instanceof Stmt\Property) {
            $kind = 'p';
            $prop = $node->props[0];
            $name = $prop->name;
            $line = $prop->getLine();
            $access = $this->getNodeAccess($node);
        } elseif ($node instanceof Stmt\ClassConst) {
            $kind = 'd';
            $cons = $node->consts[0];
            $name = $cons->name;
            $line = $cons->getLine();
        } elseif ($node instanceof Stmt\ClassMethod) {
            $kind = 'm';
            $name = $node->name;
            $line = $node->getLine();
            $access = $this->getNodeAccess($node);
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('method' => $name));
            }
        } elseif ($node instanceof Stmt\If_) {
            foreach ($node as $subNode) {
                $this->struct($subNode);
            }
        } elseif ($node instanceof Stmt\Const_) {
            $kind = 'd';
            $cons = $node->consts[0];
            $name = $cons->name;
            $line = $node->getLine();
        } elseif ($node instanceof Stmt\Global_) {
            $kind = 'v';
            $prop = $node->vars[0];
            $name = $prop->name;
            $line = $node->getLine();
        } elseif ($node instanceof Stmt\Static_) {
            //@todo
        } elseif ($node instanceof Stmt\Declare_) {
            //@todo
        } elseif ($node instanceof Stmt\TryCatch) {
            foreach ($node as $subNode) {
                $this->struct($subNode);
            }
        } elseif ($node instanceof Stmt\Function_) {
            $kind = 'f';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('function' => $name));
            }
        } elseif ($node instanceof Stmt\Interface_) {
            $kind = 'i';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('interface' => $name));
            }
        } elseif ($node instanceof Stmt\Trait_) {
            $kind = 't';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('trait' => $name));
            }
        } elseif ($node instanceof Stmt\Namespace_) {
            $kind = 'n';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('namespace' => $name));
            }
        } elseif ($node instanceof Expr\Assign) {
            if (isset($node->var->name) && is_string($node->var->name)) {
                $kind = 'v';
                $node = $node->var;
                $name = $node->name;
                $line = $node->getLine();
            }
        } elseif ($node instanceof Expr\AssignRef) {
            if (isset($node->var->name) && is_string($node->var->name)) {
                $kind = 'v';
                $node = $node->var;
                $name = $node->name;
                $line = $node->getLine();
            }
        } elseif ($node instanceof Expr\FuncCall) {
            switch ($node->name) {
                case 'define':
                    $kind = 'd';
                    $node = $node->args[0]->value;
                    $name = $node->value;
                    $line = $node->getLine();
                    break;
            }
        } else {
            // we don't care the rest of them.
        }

        if (!empty($kind) && !empty($name) && !empty($line)) {
            $structs[] = array(
                'file' => $this->mFile,
                'kind' => $kind,
                'name' => $name,
                'extends' => $extends,
                'implements' => $implements,
                'line' => $line,
                'scope' => $scope,
                'access' => $access,
            );
        }

        if (!empty($parent)) array_pop($scope);

        // if no --sort is given, sort by occurrence
        if (!isset($this->mOptions['sort']) || $this->mOptions['sort'] == 'no') {
            usort($structs, 'self::helperSortByLine');
        }

        return $structs;
    }

    private function render($structure)
    {
        $str = '';
        foreach ($structure as $struct) {
            $file = $struct['file'];

            if (!in_array($struct['kind'], $this->mOptions['kinds'])) {
                continue;
            }

            if (!isset($files[$file]))
                $files[$file] = file($file);

            $lines = $files[$file];

            if (empty($struct['name']) || empty($struct['line']) || empty($struct['kind']))
                return;

            if  ($struct['name'] instanceof Expr\Variable) {
                $str .= $struct['name']->name;
            }else{
                $str .= $struct['name'];
            }

            $str .= "\t" . $file;

            if ($this->mOptions['excmd'] == 'number') {
                $str .= "\t" . $struct['line'];
            } else { //excmd == 'mixed' or 'pattern', default behavior
                $str .= "\t" . "/^" . rtrim($lines[$struct['line'] - 1], "\n") . "$/";
            }

            if ($this->mOptions['format'] == 1) {
                $str .= "\n";
                continue;
            }

            $str .= ";\"";

            #field=k, kind of tag as single letter
            if (in_array('k', $this->mOptions['fields'])) {
                in_array('z', $this->mOptions['fields']) && $str .= "kind:";
                $str .= "\t" . $struct['kind'];
            } else
            #field=K, kind of tag as fullname
            if (in_array('K', $this->mOptions['fields'])) {
                in_array('z', $this->mOptions['fields']) && $str .= "kind:";
                $str .= "\t" . self::$mKinds[$struct['kind']];
            }

            #field=n
            if (in_array('n', $this->mOptions['fields'])) {
                $str .= "\t" . "line:" . $struct['line'];
            }

            #field=s
            if (in_array('s', $this->mOptions['fields']) && !empty($struct['scope'])) {
                // $scope, $type, $name are current scope variables
                $scope = array_pop($struct['scope']);
                $type = key($scope);
                $name = current($scope);
                switch ($type) {
                    case 'class':
                        // n_* stuffs are namespace related scope variables
                        // current > class > namespace
                        $n_scope = array_pop($struct['scope']);
                        if(!empty($n_scope)) {
                            $n_type = key($n_scope);
                            $n_name = current($n_scope);
                            $s_str = 'class:' . $n_name . '\\' . $name;
                        } else {
                            $s_str = 'class:' . $name;
                        }
                        break;
                    case 'method':
                        // c_* stuffs are class related scope variables
                        // current > method > class > namespace
                        $c_scope = array_pop($struct['scope']);
                        $c_type = key($c_scope);
                        $c_name = current($c_scope);
                        $n_scope = array_pop($struct['scope']);
                        if(!empty($n_scope)) {
                            $n_type = key($n_scope);
                            $n_name = current($n_scope);
                            $s_str = 'method:' . $n_name . '\\' . $c_name . '::' . $name;
                        } else {
                            $s_str = 'method:' . $c_name . '::' . $name;
                        }
                        break;
                    default:
                        $s_str = $type . ':' . $name;
                        break;
                }
                $str .= "\t" . $s_str;
            }

            #field=i
            if(in_array('i', $this->mOptions['fields'])) {
                $inherits = array();
                if(!empty($struct['extends'])) {
                    $inherits[] = $struct['extends']->toString();
                }
                if(!empty($struct['implements'])) {
                    foreach($struct['implements'] as $interface) {
                        $inherits[] = $interface->toString();
                    }
                }
                if(!empty($inherits))
                    $str .= "\t" . 'inherits:' . implode(',', $inherits);
            }

            #field=a
            if (in_array('a', $this->mOptions['fields']) && !empty($struct['access'])) {
                $str .= "\t" . "access:" . $struct['access'];
            }

            $str .= "\n";
        }

        // remove the last line ending and carriage return
        $str = trim(str_replace("\x0D", "", $str));

        return $str;
    }

    private function full_render() {
        // Files will have been rendered already, just join and export.

        $str = '';
        foreach($this->mLines as $file => $data) {
          $str .= $data.PHP_EOL;
        }

        // sort the result as instructed
        if (isset($this->mOptions['sort']) && ($this->mOptions['sort'] == 'yes' || $this->mOptions['sort'] == 'foldcase')) {
            $str = self::stringSortByLine($str, $this->mOptions['sort'] == 'foldcase');
        }

        // Save all tag information to a file for faster updates if a cache file was specified.
        if (isset($this->cachefile)) {
            file_put_contents($this->cachefile, serialize($this->tagdata));
            if ($this->mOptions['V']) {
                echo "Saved cache file.".PHP_EOL;
            }
        }

        $str = trim($str);

        return $str;
    }

    public function export()
    {
        $start = microtime(true);

        if (empty($this->mFiles)) {
            throw new PHPCtagsException('No File specified.');
        }

        foreach (array_keys($this->mFiles) as $file) {
            $this->process($file);
        }

        $content = $this->full_render();

        $end = microtime(true);

        if ($this->mOptions['V']) {
            echo PHP_EOL."It took ".($end-$start)." seconds.".PHP_EOL;
        }

        return $content;
    }

    private function process($file)
    {
        // Load the tag md5 data to skip unchanged files.
        if (!isset($this->tagdata) && isset($this->cachefile) && file_exists(realpath($this->cachefile))) {
            if ($this->mOptions['V']) {
                echo "Loaded cache file.".PHP_EOL;
            }
            $this->tagdata = unserialize(file_get_contents(realpath($this->cachefile)));
        }

        if (is_dir($file) && isset($this->mOptions['R'])) {
            $iterator = new RecursiveIteratorIterator(
                new ReadableRecursiveDirectoryIterator(
                    $file,
                    FilesystemIterator::SKIP_DOTS |
                    FilesystemIterator::FOLLOW_SYMLINKS
                )
            );

            $extensions = $this->mOptions['extensions'];

            foreach ($iterator as $filename) {
                if (!in_array(substr($filename, strrpos($filename, '.')), $extensions)) {
                    continue;
                }

                // multiple --exclude options can be specified
                if (isset($this->mOptions['exclude'])) {
                    $exclude = $this->mOptions['exclude'];

                    if (is_array($exclude)) {
                        foreach ($exclude as $item) {
                            if (false !== strpos($filename, $item)) {
                                continue 2;
                            }
                        }
                    } elseif (false !== strpos($filename, $exclude)) {
                        continue;
                    }
                }

                try {
                    $this->process_single_file($filename);
                } catch(Exception $e) {
                    echo "PHPParser: {$e->getMessage()} - {$filename}".PHP_EOL;
                }
            }
        } else {
            try {
                $this->process_single_file($file);
            } catch(Exception $e) {
                echo "PHPParser: {$e->getMessage()} - {$file}".PHP_EOL;
            }
        }
    }

    private function process_single_file($filename)
    {
        if ($this->mOptions['V'] && $this->filecount > 1 && $this->filecount % 64 == 0) {
            echo " ".$this->filecount." files".PHP_EOL;
        }
        $this->filecount++;
        $startfile = microtime(true);

        $this->setMFile((string) $filename);
        $file = file_get_contents($this->mFile);
        $md5 = md5($file);
        if (isset($this->tagdata[$this->mFile][$md5])) {
            // The file is the same as the previous time we analyzed and saved.
            $this->mLines[$this->mFile] = $this->tagdata[$this->mFile][$md5];
            if ($this->mOptions['V']) {
                echo ".";
            }
            return;
        }

        $struct = $this->struct($this->mParser->parse($file), TRUE);
        $finishfile = microtime(true);
        $this->mLines[$this->mFile] = $this->render($struct);
        $finishmerge = microtime(true);
        $this->tagdata[$this->mFile][$md5] = $this->mLines[$this->mFile];
        if ($this->mOptions['debug']) {
            echo "Parse: ".($finishfile - $startfile).", Merge: ".($finishmerge-$finishfile)."; (".$this->filecount.")".$this->mFile.PHP_EOL;
        } else if ($this->mOptions['V']) {
            echo ".";
        }
    }

}

class PHPCtagsException extends Exception {
    public function __toString() {
        return "\nPHPCtags: {$this->message}\n";
    }
}

class ReadableRecursiveDirectoryIterator extends RecursiveDirectoryIterator {
    function getChildren() {
        try {
          return new ReadableRecursiveDirectoryIterator(
            $this->getPathname(),
            FilesystemIterator::SKIP_DOTS |
            FilesystemIterator::FOLLOW_SYMLINKS);

        } catch(UnexpectedValueException $e) {
            file_put_contents('php://stderr', "\nPHPPCtags: {$e->getMessage()} - {$this->getPathname()}\n");
            return new RecursiveArrayIterator(array());
        }
    }
}
