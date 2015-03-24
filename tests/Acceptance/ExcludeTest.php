<?php

namespace tests\PHPCTags\Acceptance;

final class ExcludeTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itExcludesFileByName()
    {
        $this->givenSourceFile('File1.php', <<<'EOS'
<?php

$test = 1;
EOS
        );

        $this->givenSourceFile('File2.php', <<<'EOS'
<?php

$test = 1;
EOS
        );

        $this->runPHPCtagsWithExcludes(array('File2.php'));

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertTagsFileContainsNoTagsFromFile('File2.php');
    }

    /**
     * @test
     */
    public function itExcludesFileByPatter()
    {
        $this->givenSourceFile('File.php', <<<'EOS'
<?php

$test = 1;
EOS
        );

        $this->givenSourceFile('File.skip.php', <<<'EOS'
<?php

$test = 1;
EOS
        );

        $this->markTestIncomplete('Pattern matching currently doesn\'t appear to work');
        $this->runPHPCtagsWithExcludes(array('*.skip.php'));

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertTagsFileContainsNoTagsFromFile('File.skip.php');
    }
}
