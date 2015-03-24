<?php

namespace tests\PHPCTags\Acceptance;

final class NamespacesTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itAddsTagForMultiLevelNamespace()
    {
        $this->givenSourceFile('MultiLevelNamespace.php', <<<'EOS'
<?php

namespace Level1\Level2;
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(1);
        $this->assertTagsFileContainsTag(
            'MultiLevelNamespace.php',
            'Level1\Level2',
            self::KIND_NAMESPACE,
            3
        );
    }
}
