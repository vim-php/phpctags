<?php
/**
 * Test for full kind identifier
 **/
class t_0002 extends PHPCtagsTestCase {

    public function __construct()
    {
        parent::__construct();
        $this->mFormat = "<name>\t<file>\t/^<line content>$/;\"\t<full kind>\tline:<line number>\t<scope>\t<access>";
        $this->mExample = '0001';
        $this->mOptions['fields'] = array('n','K','s','a');
    }

}
