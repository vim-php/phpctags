<?php

namespace tests\PHPCTags\Acceptance;

final class FilesystemTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itSkipsUnreadableDirectories()
    {
        $this->givenDirectory('unreadable');
        $this->givenSourceFile('unreadable/UnreadableClass.php', <<<EOS
<?php

class UnreadableClass
{
}
EOS
        );
        $this->givenMode('unreadable', 0000);

        $this->runPHPCtags();
        $this->assertTagsFileHeaderIsCorrect();

        $this->assertTagsFileContainsNoTagsFromFile('unreadable/UnreadableClass.php');
    }
}
