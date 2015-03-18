<?php

namespace tests\PHPCTags\Acceptance;

final class BasicTagKindsTest extends AcceptanceTestCase
{
    public function testItCreatesTagFileForSingleVariable()
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

    public function testItCreatesTagFileForSingleClass()
    {
        $this->givenSourceFile('SingleClassExample.php', <<<'EOS'
<?php

class TestClass
{
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);
        $this->assertTagsFileContainsTag(
            'SingleClassExample.php',
            'TestClass',
            self::KIND_CLASS,
            3
        );
    }
}
