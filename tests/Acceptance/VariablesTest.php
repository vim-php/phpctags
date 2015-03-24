<?php

namespace tests\PHPCTags\Acceptance;

final class VariablesTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itCreatesTagTopLevelVariables()
    {
        $this->givenSourceFile('SingleVarExample.php', <<<'EOS'
<?php

$var = 'test value';
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);
        $this->assertTagsFileContainsTag(
            'SingleVarExample.php',
            'var',
            self::KIND_VARIABLE,
            3
        );
    }

    /**
     * @test
     */
    public function itCreatesTagForNamespacedVariables()
    {
        $this->givenSourceFile('NamespacedVariables.php', <<<'EOS'
<?php

namespace Level1\Level2;

$var = 'test value';
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(2);
        $this->assertTagsFileContainsTag(
            'NamespacedVariables.php',
            'var',
            self::KIND_VARIABLE,
            5,
            'namespace:Level1\Level2'
        );
    }

    /**
     * @test
     */
    public function itCreatesTagForLocalVariableInsideFunction()
    {
        $this->givenSourceFile('LocalVariable.php', <<<'EOS'
<?php

function testFunction()
{
    $var = 'test value';
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(2);
        $this->assertTagsFileContainsTag(
            'LocalVariable.php',
            'var',
            self::KIND_VARIABLE,
            5,
            'function:testFunction'
        );
    }

    /**
     * @test
     */
    public function itCreatesTagForLocalVariableInsideClassMethod()
    {
        $this->givenSourceFile('MethodVariable.php', <<<'EOS'
<?php

class TestClass
{
    function testMethod()
    {
        $var = 'test value';
    }
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(3);
        $this->assertTagsFileContainsTag(
            'MethodVariable.php',
            'var',
            self::KIND_VARIABLE,
            7,
            'method:TestClass::testMethod'
        );
    }

    /**
     * @test
     */
    public function itCreatesTagForVariablesInTryCatchBlocks()
    {
        $this->givenSourceFile('VariableInTryBlock.php', <<<'EOS'
<?php
try {
    $ctags = new PHPCtags();
    $result = $ctags->export($file, $options);
} catch (Exception $e) {
    die("phpctags: {$e->getMessage()}");
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(2);
        $this->assertTagsFileContainsTag(
            'VariableInTryBlock.php',
            'ctags',
            self::KIND_VARIABLE,
            3
        );
        $this->assertTagsFileContainsTag(
            'VariableInTryBlock.php',
            'result',
            self::KIND_VARIABLE,
            4
        );
    }
}
