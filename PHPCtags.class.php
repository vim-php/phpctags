<?php
class PHPCtags
{
    const VERSION = '0.5.1';

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

    private $mStructs;

    private $mOptions;

    public function __construct($options)
    {
        $this->mParser = new PHPParser_Parser(new PHPParser_Lexer);
        $this->mStructs = array();
        $this->mOptions = $options;
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
        } elseif ($node instanceof PHPParser_Node_Stmt_Class) {
            $kind = 'c';
            $name = $node->name;
            $extends = $node->extends;
            $implements = $node->implements;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('class' => $name));
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Property) {
            $kind = 'p';
            $prop = $node->props[0];
            $name = $prop->name;
            $line = $prop->getLine();
            $access = $this->getNodeAccess($node);
        } elseif ($node instanceof PHPParser_Node_Stmt_ClassConst) {
            $kind = 'd';
            $cons = $node->consts[0];
            $name = $cons->name;
            $line = $cons->getLine();
        } elseif ($node instanceof PHPParser_Node_Stmt_ClassMethod) {
            $kind = 'm';
            $name = $node->name;
            $line = $node->getLine();
            $access = $this->getNodeAccess($node);
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('method' => $name));
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_If) {
            foreach ($node as $subNode) {
                $this->struct($subNode);
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Const) {
            $kind = 'd';
            $cons = $node->consts[0];
            $name = $cons->name;
            $line = $node->getLine();
        } elseif ($node instanceof PHPParser_Node_Stmt_Global) {
            $kind = 'v';
            $prop = $node->vars[0];
            $name = $prop->name;
            $line = $node->getLine();
        } elseif ($node instanceof PHPParser_Node_Stmt_Static) {
            //@todo
        } elseif ($node instanceof PHPParser_Node_Stmt_Declare) {
            //@todo
        } elseif ($node instanceof PHPParser_Node_Stmt_TryCatch) {
            foreach ($node as $subNode) {
                $this->struct($subNode);
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Function) {
            $kind = 'f';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('function' => $name));
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Interface) {
            $kind = 'i';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('interface' => $name));
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Trait ) {
            $kind = 't';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('trait' => $name));
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Namespace) {
            $kind = 'n';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, FALSE, array('namespace' => $name));
            }
        } elseif ($node instanceof PHPParser_Node_Expr_Assign) {
            if (is_string($node->var->name)) {
                $kind = 'v';
                $node = $node->var;
                $name = $node->name;
                $line = $node->getLine();
            }
        } elseif ($node instanceof PHPParser_Node_Expr_AssignRef) {
            if (is_string($node->var->name)) {
                $kind = 'v';
                $node = $node->var;
                $name = $node->name;
                $line = $node->getLine();
            }
        } elseif ($node instanceof PHPParser_Node_Expr_FuncCall) {
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

    private function render()
    {
        $str = '';
        foreach ($this->mStructs as $struct) {
            $file = $struct['file'];

            if (!isset($files[$file]))
                $files[$file] = file($file);

            $lines = $files[$file];

            if (empty($struct['name']) || empty($struct['line']) || empty($struct['kind']))
                return;

            $str .= $struct['name'];

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
                list($type, $name) = each($scope);
                switch ($type) {
                    case 'class':
                        // n_* stuffs are namespace related scope variables
                        // current > class > namespace
                        $n_scope = array_pop($struct['scope']);
                        if(!empty($n_scope)) {
                            list($n_type, $n_name) = each($n_scope);
                            $s_str = 'class:' . $n_name . '\\' . $name;
                        } else {
                            $s_str = 'class:' . $name;
                        }
                        break;
                    case 'method':
                        // c_* stuffs are class related scope variables
                        // current > method > class > namespace
                        $c_scope = array_pop($struct['scope']);
                        list($c_type, $c_name) = each($c_scope);
                        $n_scope = array_pop($struct['scope']);
                        if(!empty($n_scope)) {
                            list($n_type, $n_name) = each($n_scope);
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

        // remove the last line ending
        $str = trim($str);

        // sort the result as instructed
        if (isset($this->mOptions['sort']) && ($this->mOptions['sort'] == 'yes' || $this->mOptions['sort'] == 'foldcase')) {
            $str = self::stringSortByLine($str, $this->mOptions['sort'] == 'foldcase');
        }

        return $str;
    }

    public function export()
    {
        if (empty($this->mFiles)) {
            throw new PHPCtagsException('No File specified.');
        }

        foreach (array_keys($this->mFiles) as $file) {
            $this->process($file);
        }

        return $this->render();
    }

    private function process($file)
    {
        if (is_dir($file) && isset($this->mOptions['R'])) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $file,
                    FilesystemIterator::SKIP_DOTS |
                    FilesystemIterator::FOLLOW_SYMLINKS
                )
            );

            $extensions = array('.php', '.php3', '.php4', '.php5', '.phps');

            foreach ($iterator as $filename) {
                if (!in_array(substr($filename, strrpos($filename, '.')), $extensions)) {
                    continue;
                }

                if (isset($this->mOptions['exclude']) && false !== strpos($filename, $this->mOptions['exclude'])) {
                    continue;
                }

                try {
                    $this->setMFile((string) $filename);
                    $this->mStructs = array_merge(
                        $this->mStructs,
                        $this->struct($this->mParser->parse(file_get_contents($this->mFile)), TRUE)
                    );
                } catch(Exception $e) {
                    echo "PHPParser: {$e->getMessage()} - {$filename}".PHP_EOL;
                }
            }
        } else {
            try {
                $this->setMFile($file);
                $this->mStructs = array_merge(
                    $this->mStructs,
                    $this->struct($this->mParser->parse(file_get_contents($this->mFile)), TRUE)
                );
            } catch(Exception $e) {
                echo "PHPParser: {$e->getMessage()} - {$filename}".PHP_EOL;
            }
        }
    }
}

class PHPCtagsException extends Exception {
    public function __toString() {
        return "PHPCtags: {$this->message}\n";
    }
}
