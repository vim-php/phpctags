<?php
class PHPCtags
{
    private $mFile;

    private static $mKinds = array(
        'c' => 'class',
        'm' => 'method',
        'f' => 'function',
        'p' => 'property',
        'd' => 'constant',
        'v' => 'variable',
        'i' => 'interface',
    );

    private $mParser;

    public function __construct()
    {
        $this->mParser = new PHPParser_Parser(new PHPParser_Lexer);
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

        $kind = $name = $line = $access = '';

        if (!empty($parent)) array_push($scope, $parent);

        if (is_array($node)) {
            foreach ($node as $subNode) {
                $this->struct($subNode);
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Class) {
            $kind = 'c';
            $name = $node->name;
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
        } elseif ($node instanceof PHPParser_Node_Stmt_Namespace) {
            //@todo
            foreach ($node as $subNode) {
                $this->struct($subNode);
            }
        } elseif ($node instanceof PHPParser_Node_Expr_Assign) {
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
                'line' => $line,
                'scope' => $scope,
                'access' => $access,
            );
        }

        if (!empty($parent)) array_pop($scope);

        // if no --sort is given, sort by occurrence
        if (!isset($options['sort']) || $options['sort'] == 'no') {
            usort($structs, 'self::helperSortByLine');
        }

        return $structs;
    }

    private function render($structs, $options)
    {
        $str = '';
        foreach ($structs as $struct) {
            $file = $struct['file'];

            if (!isset($files[$file]))
                $files[$file] = file($file);

            $lines = $files[$file];

            if (empty($struct['name']) || empty($struct['line']) || empty($struct['kind']))
                return;

            $str .= $struct['name'];

            $str .= "\t" . $file;

            if ($options['excmd'] == 'number') {
                $str .= "\t" . $struct['line'];
            } else { //excmd == 'mixed' or 'pattern', default behavior
                $str .= "\t" . "/^" . rtrim($lines[$struct['line'] - 1], "\n") . "$/";
            }

            if ($options['format'] == 1) {
                $str .= "\n";
                continue;
            }

            $str .= ";\"";

            #field=k, kind of tag as single letter
            if (in_array('k', $options['fields'])) {
                in_array('z', $options['fields']) && $str .= "kind:";
                $str .= "\t" . $struct['kind'];
            } else
            #field=K, kind of tag as fullname
            if (in_array('K', $options['fields'])) {
                in_array('z', $options['fields']) && $str .= "kind:";
                $str .= "\t" . self::$mKinds[$struct['kind']];
            }

            #field=n
            if (in_array('n', $options['fields'])) {
                $str .= "\t" . "line:" . $struct['line'];
            }

            #field=s
            if (in_array('s', $options['fields']) && !empty($struct['scope'])) {
                $scope = array_pop($struct['scope']);
                list($type, $name) = each($scope);
                switch ($type) {
                    case 'method':
                        $scope = array_pop($struct['scope']);
                        list($p_type, $p_name) = each($scope);
                        $scope = 'method:' . $p_name . '::' . $name;
                        break;
                    default:
                        $scope = $type . ':' . $name;
                        break;
                }
                $str .= "\t" . $scope;
            }

            #field=a
            if (in_array('a', $options['fields']) && !empty($struct['access'])) {
                $str .= "\t" . "access:" . $struct['access'];
            }

            $str .= "\n";
        }

        // remove the last line ending
        $str = trim($str);

        // sort the result as instructed
        if (isset($options['sort']) && ($options['sort'] == 'yes' || $options['sort'] == 'foldcase')) {
            $str = self::stringSortByLine($str, $options['sort'] == 'foldcase');
        }

        return $str;
    }

    public function export($file, $options)
    {
        $structs = array();
        if (is_dir($file) && isset($options['R'])) {
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

                if (isset($options['exclude']) && false !== strpos($filename, $options['exclude'])) {
                    continue;
                }

                $this->setMFile((string) $filename);
                $structs += $this->struct($this->mParser->parse(file_get_contents($this->mFile)), TRUE);
            }
        } else {
            $this->setMFile($file);
            $structs += $this->struct($this->mParser->parse(file_get_contents($this->mFile)), TRUE);
        }
        return $this->render($structs, $options);
    }
}

class PHPCtagsException extends Exception {
    public function __toString() {
        return "PHPCtags: {$this->message}\n";
    }
}
