<?php

abstract class PHPCtagsTestCase {

    protected $mFormat;

    protected $mOptions;

    protected $mExample;

    protected $mExampleDefine;

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
        return $this->mExample;
    }

    public function getExampleDefine()
    {
        return $this->mExampleDefine;
    }

    public function getExampleContent()
    {
        return file($this->getExample());
    }
}
