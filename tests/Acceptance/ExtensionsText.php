<?php

namespace tests\PHPCTags\Acceptance;

final class ExtensionsTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itUsesDefaultExtensions()
    {
        $this->givenSourceFile('new_extension.inc', <<<EOS
<?php

class TestClass 
{
	function publicMethod() {
        }
}
EOS
        );

        $this->runPHPCtags();
        $this->assertTagsFileHeaderIsCorrect();

        $this->assertTagsFileContainsNoTagsFromFile('new_extension.inc');
    }

    /**
     * @test
     */
    public function itUsesCustomExtensions()
    {
        $this->givenSourceFile('new_extension.inc', <<<EOS
<?php

class TestClass 
{
	public function publicMethod() {
        }
}
EOS
        );

        $this->runPHPCtags(array('--extensions=+.inc'));
        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(2);

        $this->assertTagsFileContainsTag(
            'new_extension.inc',
            'TestClass',
            self::KIND_CLASS,
            3
        );
        $this->assertTagsFileContainsTag(
            'new_extension.inc',
            'publicMethod',
            self::KIND_METHOD,
            5,
            'class:TestClass',
            'public'
        );
    }
}
