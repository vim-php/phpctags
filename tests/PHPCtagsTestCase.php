<?php

abstract class PHPCtagsTestCase {

    protected $mFormat;

    protected $mOptions;

    protected $mExample;

    public function __construct()
    {
        $this->mFormat = "<name>\t<file>\t/^<line content>$/;\"\t<short kind>\tline:<line number>\t<scope>\t<access>";
        $this->mOptions = array(
            'excmd' => 'pattern',
            'fields' => array('n','k','s','a'),
            'format' => 2,
        );
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

    public function getExpectResult()
    {
        $kinds = PHPCtags::getMKinds();
        $testcase_expect = '';
        $testcase_example_define = $this->getExampleDefine();
        $testcase_example_content = $this->getExampleContent();
        foreach ($testcase_example_define as $define) {
            $line = $this->mFormat;

            $line = preg_replace('/<name>/', $define['name'], $line);
            $line = preg_replace('/<file>/', $this->getExample(), $line);
            $line = preg_replace('/<line content>/', rtrim($testcase_example_content[$define['line'] - 1], "\n"), $line);
            $line = preg_replace('/<short kind>/', $define['kind'], $line);
            $line = preg_replace('/<full kind>/', $kinds[$define['kind']], $line);
            $line = preg_replace('/<line number>/', $define['line'], $line);
            if(!empty($define['scope'])) {
                $line = preg_replace('/<scope>/', $define['scope'], $line);
            } else {
                $line = preg_replace('/<scope>/', '', $line);
            }
            if(!empty($define['access'])) {
                $line = preg_replace('/<access>/', 'access:' . $define['access'], $line);
            } else {
                $line = preg_replace('/<access>/', '', $line);
            }
            $line = rtrim($line, "\t");
            $line .= "\n";

            $testcase_expect .= $line;
        }

        // remove the last line ending
        $testcase_expect = trim($testcase_expect);

        return $testcase_expect;

    }
}
