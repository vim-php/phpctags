<?php
class PHPCtags
{
    private $mFile;

    private $mFields;

    private $mParser;

    private $mStructs;

    private $mOptions;

    public function __construct($file, $options=array())
    {
        //@todo Check for existence
        $this->mFile = $file;
        $this->mFields = array(
            'c' => 'class',
            'm' => 'method',
            'f' => 'function',
            'p' => 'property',
            'd' => 'constant',
            'v' => 'variable',
            'i' => 'interface',
        );
        $this->mParser = new PHPParser_Parser(new PHPParser_Lexer);
        $this->mStructs = $this->mParser->parse(file_get_contents($this->mFile));
        $this->mOptions = $options;
    }

    private function getAccess($node)
    {
        if ($node->isPrivate()) return 'private';
        if ($node->isProtected()) return 'protected';
        return 'public';
    }

    private function struct($node, $parent=array())
    {
        static $scope = array();
        static $structs = array();

        $kind = $name = $line = $access = '';

        if(!empty($parent)) array_push($scope, $parent);

        if (is_array($node)) {
            foreach ($node as $subNode) {
                $this->struct($subNode);
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Class) {
            $kind = 'c';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, array('class' => $name));
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Property) {
            $kind = 'p';
            $prop = $node->props[0];
            $name = $prop->name;
            $line = $prop->getLine();
            $access = $this->getAccess($node);
        } elseif ($node instanceof PHPParser_Node_Stmt_ClassConst) {
            $kind = 'd';
            $cons = $node->consts[0];
            $name = $cons->name;
            $line = $cons->getLine();
        } elseif ($node instanceof PHPParser_Node_Stmt_ClassMethod) {
            $kind = 'm';
            $name = $node->name;
            $line = $node->getLine();
            $access = $this->getAccess($node);
            foreach ($node as $subNode) {
                $this->struct($subNode, array('method' => $name));
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
        } elseif ($node instanceof PHPParser_Node_Stmt_Function) {
            $kind = 'f';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, array('function' => $name));
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Trait) {
            //@todo
        } elseif ($node instanceof PHPParser_Node_Stmt_Interface) {
            $kind = 'i';
            $name = $node->name;
            $line = $node->getLine();
            foreach ($node as $subNode) {
                $this->struct($subNode, array('interface' => $name));
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Namespace) {
            //@todo
        } elseif ($node instanceof PHPParser_Node_Expr_Assign) {
            $kind = 'v';
            $node = $node->var;
            $name = $node->name;
            $line = $node->getLine();
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
                'kind' => $kind,
                'name' => $name,
                'line' => $line,
                'scope' => $scope,
                'access' => $access,
            );
        }

        if(!empty($parent)) array_pop($scope);

        return $structs;
    }

    private function render($structs)
    {
        $str = '';
        $lines = file($this->mFile);
        foreach ($structs as $struct) {
            if (empty($struct['name']) || empty($struct['line']) || empty($struct['kind']))
                return;

            if ($struct['kind'] == 'v') {
                $str .= "$" . $struct['name'];
            } else {
                $str .= $struct['name'];
            }

            $str .= "\t" . $this->mFile;

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
                $str .= "\t" . $this->mFields[$struct['kind']];
            }

            #field=n
            if (in_array('n', $this->mOptions['fields'])) {
                $str .= "\t" . "line:" . $struct['line'];
            }

            #field=s
            if (in_array('s', $this->mOptions['fields']) && !empty($struct['scope'])) {
                $scope = array_pop($struct['scope']);
                list($type,$name) = each($scope);
                switch ($type) {
                    case 'method':
                        $scope = array_pop($struct['scope']);
                        list($p_type,$p_name) = each($scope);
                        $scope = 'method:' . $p_name . '::' . $name;
                        break;
                    default:
                        $scope = $type . ':' . $name;
                        break;
                }
                $str .= "\t" . $scope;
            }

            #field=a
            if (in_array('a', $this->mOptions['fields']) && !empty($struct['access'])) {
                $str .= "\t" . "access:" . $struct['access'];
            }

            $str .= "\n";
        }
        return $str;
    }

    public function export()
    {
        echo $this->render($this->struct($this->mStructs));
    }
}
