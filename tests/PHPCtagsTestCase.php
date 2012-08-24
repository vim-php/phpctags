<?php

abstract class PHPCtagsTestCase {

    protected $mFormat;

    protected $mOptions;

    protected $mExample;

    public function __construct()
    {
        $this->mFormat = "<name>\t<file>\t/^<line content>$/;\"\t<kind>\tline:<line number>\t<scope>\t<access>";
        $this->mOptions = array(
            'excmd' => 'pattern',
            'fields' => array('n','k','s','a'),
            'format' => 2,
        );
    }

    public function getFormat()
    {
        return $this->mFormat;
    }

    public function getOptions()
    {
        return $this->mOptions;
    }

    public function getExample()
    {
        return realpath(__DIR__ . '/examples/' . $this->mExample . '.example.php');
    }

    public function getExampleDefine()
    {
        require_once __DIR__ . '/examples/' . $this->mExample . '.example.define.php';
        if (function_exists('e_' . $this->mExample . '_define')) {
            $define = call_user_func('e_' . $this->mExample . '_define');
        } else {
            die('example definition not exist');
        }
        return $define;
    }

    public function getExampleContent()
    {
        return file($this->getExample());
    }
}
