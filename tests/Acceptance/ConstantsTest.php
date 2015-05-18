<?php

namespace tests\PHPCTags\Acceptance;

final class ConstantsTest extends AcceptanceTestCase
{
    /**
     * @test
     */
    public function itCreatesTagFileForGlobalConstants()
    {
        $this->givenSourceFile('Constants.php', <<<'EOS'
<?php

define('CONSTANT_1', 1);

const CONSTANT_2 = 2;
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(2);
        $this->assertTagsFileContainsTag(
            'Constants.php',
            'CONSTANT_1',
            self::KIND_CONSTANT,
            3
        );
        $this->assertTagsFileContainsTag(
            'Constants.php',
            'CONSTANT_2',
            self::KIND_CONSTANT,
            5
        );
    }

    /**
     * @test
     */
    public function itCreatesTagFileForNamespacedConstants()
    {
        $this->givenSourceFile('Constants.php', <<<'EOS'
<?php

namespace Level1\Level2;

define('CONSTANT_1', 1);

const CONSTANT_2 = 2;
EOS
        );

        $this->runPHPCtags();

        $this->assertTagsFileHeaderIsCorrect();
        $this->assertNumberOfTagsInTagsFileIs(3);
        $this->assertTagsFileContainsTag(
            'Constants.php',
            'CONSTANT_1',
            self::KIND_CONSTANT,
            5,
            'namespace:Level1\Level2'
        );
        $this->assertTagsFileContainsTag(
            'Constants.php',
            'CONSTANT_2',
            self::KIND_CONSTANT,
            7,
            'namespace:Level1\Level2'
        );
    }
}
