<?php

namespace tests\PHPCTags\Acceptance;

use Exception;
use PHPUnit_Framework_TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

abstract class AcceptanceTestCase extends PHPUnit_Framework_TestCase
{
    const FORMAT = "<name>\t<file>\t/^<line content>$/;\"\t<short kind>\tline:<line number>\t<scope>\t<access>";

    const KIND_TRAIT     = 't';
    const KIND_CLASS     = 'c';
    const KIND_METHOD    = 'm';
    const KIND_FUNCTION  = 'f';
    const KIND_PROPERTY  = 'p';
    const KIND_CONSTANT  = 'd';
    const KIND_VARIABLE  = 'v';
    const KIND_INTERFACE = 'i';
    const KIND_NAMESPACE = 'n';

    /**
     * @var string
     */
    private $testDir;

    /**
     * @var string
     */
    private $tagsFileContent;

    protected function setUp()
    {
        $this->testDir = __DIR__ . '/../../.test_fs';

        if (!file_exists($this->testDir)) {
            mkdir($this->testDir);
        }

        $this->testDir = realpath($this->testDir);

        $this->emptyTestDir();
    }

    /**
     * @param string $filename
     * @param string $content
     *
     * @return void
     */
    protected function givenSourceFile($filename, $content)
    {
        $filename = $this->testDir . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($filename, $content);
    }

    protected function givenDirectory($dirname)
    {
        $dirname = $this->testDir . DIRECTORY_SEPARATOR . $dirname;

        mkdir($dirname);
    }

    protected function givenMode($file, $mode)
    {
        $file = $this->testDir . DIRECTORY_SEPARATOR . $file;

        chmod($file, $mode);
    }

    /**
     * @return void
     */
    protected function runPHPCtags(array $params = array())
    {
        $entryPoint = realpath(__DIR__ . '/../../bootstrap.php');

        $params = implode(' ', $params);

        exec("php \"$entryPoint\" --recurse=yes $params -f - {$this->testDir}", $output);

        $this->tagsFileContent = $output;
    }

    protected function runPHPCtagsWithKinds($kindString)
    {
        $this->runPHPCtags(array("--kinds=$kindString"));
    }

    /**
     * @param string $patterns
     *
     * @return void
     */
    protected function runPHPCtagsWithExcludes(array $patterns)
    {
        $entryPoint = realpath(__DIR__ . '/../../bootstrap.php');

        $excludes = implode(
            ' ',
            array_map(function ($pattern) {
                return '--exclude=\'' . $pattern . '\'';
            }, $patterns)
        );

        exec("php \"$entryPoint\" --recurse=yes -f - $excludes {$this->testDir}", $output);

        $this->tagsFileContent = $output;
    }

    /**
     * @return void
     */
    protected function assertTagsFileHeaderIsCorrect()
    {
        $expectedHeader = array(
            "!_TAG_FILE_FORMAT\t2\t/extended format; --format=1 will not append ;\" to lines/",
            "!_TAG_FILE_SORTED\t1\t/0=unsorted, 1=sorted, 2=foldcase/",
            "!_TAG_PROGRAM_AUTHOR\ttechlivezheng\t/techlivezheng@gmail.com/",
            "!_TAG_PROGRAM_NAME\tphpctags\t//",
            "!_TAG_PROGRAM_URL\thttps://github.com/techlivezheng/phpctags\t/official site/",
            "!_TAG_PROGRAM_VERSION\t0.6.0\t//",
        );

        $realHeader = array_splice($this->tagsFileContent, 0, count($expectedHeader));

        $this->assertEquals($expectedHeader, $realHeader, 'Tags file header is incorrect');
    }

    /**
     * @return void
     */
    protected function assertNumberOfTagsInTagsFileIs($count)
    {
        $this->assertCount(
            $count,
            $this->tagsFileContent,
            'Tags file contains the wrong number of tags'
        );
    }

    /**
     * @param string $filename
     * @param string $name
     * @param string $kind
     * @param int    $line
     * @param string $scope
     * @param string $access
     *
     * @return void
     */
    protected function assertTagsFileContainsTag(
        $filename,
        $name,
        $kind,
        $line,
        $scope = '',
        $access = ''
    ) {
        $this->assertContains(
            $this->createTagLine($filename, $name, $kind, $line, $scope, $access),
            $this->tagsFileContent,
            "Tag file content:\n" . print_r($this->tagsFileContent, true)
        );
    }


    /**
     * @param string $filename
     *
     * @return void
     */
    public function assertTagsFileContainsNoTagsFromFile($filename)
    {
        $filename = $this->testDir . DIRECTORY_SEPARATOR . $filename;

        $tags = array_filter(
            $this->tagsFileContent,
            function ($line) use ($filename) {
                $fields = explode("\t", $line);

                return $fields[1] === $filename;
            }
        );

        $this->assertEmpty($tags, "Tags for $filename were found in tag file.");
    }

    /**
     * @return void
     */
    private function emptyTestDir()
    {
        if (empty($this->testDir)) {
            throw \RuntimeException('Test directory does not exist');
        }

        foreach (glob($this->testDir . DIRECTORY_SEPARATOR . '*') as $file) {
            chmod($file, 0755);
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->testDir,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                rmdir($fileinfo->getRealPath());
            } else {
                unlink($fileinfo->getRealPath());
            }
        }
    }

    /**
     * @param string $filename
     * @param string $name
     * @param string $kind
     * @param int    $line
     * @param string $scope
     * @param string $access
     *
     * @return string
     */
    private function createTagLine($filename, $name, $kind, $line, $scope, $access)
    {
        $kinds = array(
            self::KIND_TRAIT     => 'trait',
            self::KIND_CLASS     => 'class',
            self::KIND_METHOD    => 'method',
            self::KIND_FUNCTION  => 'function',
            self::KIND_PROPERTY  => 'property',
            self::KIND_CONSTANT  => 'constant',
            self::KIND_VARIABLE  => 'variable',
            self::KIND_INTERFACE => 'interface',
            self::KIND_NAMESPACE => 'namespace',
        );

        if(!empty($access)) {
            $access = 'access:' . $access;
        }

        $patterns = array(
            '/<name>/',
            '/<file>/',
            '/<line content>/',
            '/<short kind>/',
            '/<full kind>/',
            '/<line number>/',
            '/<scope>/',
            '/<access>/'
        );

        $replacements = array(
            $name,
            $this->testDir . DIRECTORY_SEPARATOR . $filename,
            $this->getSourceFileLineContent($filename, $line),
            $kind,
            $kinds[$kind],
            $line,
            $scope,
            $access
        );

        $line = preg_replace($patterns, $replacements, self::FORMAT);

        return rtrim($line, "\t");
    }

    /**
     * @param string $filename
     * @param int    $line
     *
     * @return string
     */
    private function getSourceFileLineContent($filename, $line)
    {
        $filename = $this->testDir . DIRECTORY_SEPARATOR . $filename;
        $line--;
        $file = file($filename);
        return rtrim($file[$line], PHP_EOL);
    }
}
