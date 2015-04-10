<?php

namespace tests\PHPCTags\Acceptance;

final class FunctionsTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itCreatesTagForFunction()
    {
        $this->givenSourceFile('FunctionExample.php', <<<'EOS'
<?php

function testFunction($a, $b)
{
}
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);
        $this->assertTagsFileContainsTag(
            'FunctionExample.php',
            'testFunction',
            self::KIND_FUNCTION,
            3
        );
    }
}
