<?php

abstract class PHPCtagsTestCase {

    protected $mFormat;

    protected $mOptions;

    protected $mExample;

    protected $mExampleDefine;

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
