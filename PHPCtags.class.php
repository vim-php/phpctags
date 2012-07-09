<?php
class PHPCtags
{
    private $mFile;

    private $mParser;

    public function __construct($file) {
        //@todo Check for existence
        $this->mFile = $file;
        $this->mParser = new PHPParser_Parser(new PHPParser_Lexer);
    }

    private function getAccess($node)
    {
        if ($node->isPrivate()) return 'private';
        if ($node->isProtected()) return 'protected';
        return 'public';
    }

    private function struct($node, $class_name = NULL, $function_name = NULL)
    {
        static $structs = array();

        $kind = $name = $line = $scope = $access = '';
        if (is_array($node)) {
            foreach ($node as $subNode) {
                $this->struct($subNode, $class_name, $function_name);
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Class) {
            $kind = 'c';
            $name = $node->name;
            $line = $node->getLine() - 1;
            foreach ($node as $subNode) {
                $this->struct($subNode, $name);
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Property) {
            $kind = 'v';
            $prop = $node->props[0];
            $name = $prop->name;
            $line = $prop->getLine() - 1;
            $scope = "class:" . $class_name;
            $access = $this->getAccess($node);
        } elseif ($node instanceof PHPParser_Node_Stmt_ClassConst) {
            $kind = 'd';
            $cons = $node->consts[0];
            $name = $cons->name;
            $line = $cons->getLine() - 1;
            $scope = "class:" . $class_name;
        } elseif ($node instanceof PHPParser_Node_Stmt_ClassMethod) {
            $kind = 'f';
            $name = $node->name;
            $line = $node->getLine() - 1;
            $scope = "class:" . $class_name;
            $access = $this->getAccess($node);
            foreach ($node as $subNode) {
                $this->struct($subNode, $class_name, $name);
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Const) {
            $kind = 'd';
            $cons = $node->consts[0];
            $name = $cons->name;
            $line = $node->getLine() - 1;
        } elseif ($node instanceof PHPParser_Node_Stmt_Global) {
            $kind = 'v';
            $prop = $node->vars[0];
            $name = $prop->name;
            $line = $node->getLine() - 1;
        } elseif ($node instanceof PHPParser_Node_Stmt_Static) {
            //@todo
        } elseif ($node instanceof PHPParser_Node_Stmt_Declare) {
            //@todo
        } elseif ($node instanceof PHPParser_Node_Stmt_Function) {
            $kind = 'f';
            $name = $node->name;
            $line = $node->getLine() - 1;
            foreach ($node as $subNode) {
                $this->struct($subNode, $class_name, $name);
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Trait) {
            //@todo
        } elseif ($node instanceof PHPParser_Node_Stmt_Interface) {
            $kind = 'i';
            $name = $node->name;
            $line = $node->getLine() - 1;
            foreach ($node as $subNode) {
                $this->struct($subNode, $name);
            }
        } elseif ($node instanceof PHPParser_Node_Stmt_Namespace) {
            //@todo
        } elseif ($node instanceof PHPParser_Node_Expr_Assign) {
            $kind = 'v';
            $node = $node->var;
            $name = $node->name;
            $line = $node->getLine() - 1;
            if (!empty($class_name) && !empty($function_name)) {
                $scope = "function:" . $class_name . '::' . $function_name;
            } elseif (!empty($function_name)) {
                $scope = "function:" . $function_name;
            }
        } elseif ($node instanceof PHPParser_Node_Expr_FuncCall) {
            switch ($node->name) {
                case 'define':
                    $kind = 'd';
                    $node = $node->args[0]->value;
                    $name = $node->value;
                    $line = $node->getLine() - 1;
                    break;
            }
        } else {
            // we don't care the rest of them.
        }

        if(!empty($kind) && !empty($name) && !empty($line)) {
            $structs[] = array(
                'kind' => $kind,
                'name' => $name,
                'line' => $line,
                'scope' => $scope,
                'access' => $access,
            );
        }

        return $structs;
    }

    private  function render($structs)
    {
        $str = '';
        $lines = file($this->mFile);
        foreach ($structs as $stuct) {
            if (empty($stuct['name']) || empty($stuct['line']) || empty($stuct['kind']))
                return;

            if ($stuct['kind'] == 'v') {
                $str .= "$" . $stuct['name'];
            } else {
                $str .= $stuct['name'];
            }
            $str .= "\t" . $this->mFile;
            $str .= "\t" . "/^" . rtrim($lines[$stuct['line']], "\n") . "$/;\"";
            $str .= "\t" . $stuct['kind'];
            $str .= "\t" . "line:" . $stuct['line'];
            !empty($stuct['scope']) && $str .= "\t" . $stuct['scope'];
            !empty($stuct['access']) && $str .= "\t" . "access:" . $stuct['access'];
            $str .= "\n";
        }
        return $str;
    }

    public function export()
    {
        $code =file_get_contents($this->mFile);
        $stmts = $this->mParser->parse($code);
        $structs = $this->struct($stmts);
        echo $this->render($structs);
    }
}
