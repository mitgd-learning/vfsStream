<?php
/**
 * Test for org::bovigo::vfs::vfsStreamFile.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 * @subpackage  test
 */
require_once 'org/bovigo/vfs/vfsStreamFile.php';
require_once 'PHPUnit/Framework.php';
/**
 * Test for org::bovigo::vfs::vfsStreamFile.
 *
 * @package     bovigo_vfs
 * @subpackage  test
 */
class vfsStreamFileTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @var  vfsStreamFile
     */
    protected $file;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->file = new vfsStreamFile('foo');
    }

    /**
     * test default values and methods
     *
     * @test
     */
    public function defaultValues()
    {
        $this->assertEquals(vfsStreamContent::TYPE_FILE, $this->file->getType());
        $this->assertEquals('foo', $this->file->getName());
        $this->assertTrue($this->file->appliesTo('foo'));
        $this->assertFalse($this->file->appliesTo('foo/bar'));
        $this->assertFalse($this->file->appliesTo('bar'));
        $this->assertFalse($this->file->hasChild('bar'));
    }

    /**
     * test setting and getting the content of a file
     *
     * @test
     */
    public function content()
    {
        $this->assertNull($this->file->getContent());
        $this->assertSame($this->file, $this->file->setContent('bar'));
        $this->assertEquals('bar', $this->file->getContent());
        $this->assertSame($this->file, $this->file->withContent('baz'));
        $this->assertEquals('baz', $this->file->getContent());
    }

    /**
     * test renaming the directory
     *
     * @test
     */
    public function rename()
    {
        $this->file->rename('bar');
        $this->assertEquals('bar', $this->file->getName());
        $this->assertFalse($this->file->appliesTo('foo'));
        $this->assertFalse($this->file->appliesTo('foo/bar'));
        $this->assertTrue($this->file->appliesTo('bar'));
    }

    /**
     * test reading contents from the file
     *
     * @test
     */
    public function readEmptyFile()
    {
        $this->assertTrue($this->file->eof());
        $this->assertEquals(0, $this->file->size());
        $this->assertEquals('', $this->file->read(5));
        $this->assertEquals(5, $this->file->getBytesRead());
        $this->assertTrue($this->file->eof());
    }

    /**
     * test reading contents from the file
     *
     * @test
     */
    public function read()
    {
        $this->file->setContent('foobarbaz');
        $this->assertFalse($this->file->eof());
        $this->assertEquals(9, $this->file->size());
        $this->assertEquals('foo', $this->file->read(3));
        $this->assertEquals(3, $this->file->getBytesRead());
        $this->assertFalse($this->file->eof());
        $this->assertEquals(9, $this->file->size());
        $this->assertEquals('bar', $this->file->read(3));
        $this->assertEquals(6, $this->file->getBytesRead());
        $this->assertFalse($this->file->eof());
        $this->assertEquals(9, $this->file->size());
        $this->assertEquals('baz', $this->file->read(3));
        $this->assertEquals(9, $this->file->getBytesRead());
        $this->assertEquals(9, $this->file->size());
        $this->assertTrue($this->file->eof());
        $this->assertEquals('', $this->file->read(3));
    }

    /**
     * test seeking to offset
     *
     * @test
     */
    public function seekEmptyFile()
    {
        $this->assertFalse($this->file->seek(0, 55));
        $this->assertTrue($this->file->seek(0, SEEK_SET));
        $this->assertEquals(0, $this->file->getBytesRead());
        $this->assertTrue($this->file->seek(5, SEEK_SET));
        $this->assertEquals(5, $this->file->getBytesRead());
        $this->assertTrue($this->file->seek(0, SEEK_CUR));
        $this->assertEquals(5, $this->file->getBytesRead());
        $this->assertTrue($this->file->seek(2, SEEK_CUR));
        $this->assertEquals(7, $this->file->getBytesRead());
        $this->assertTrue($this->file->seek(0, SEEK_END));
        $this->assertEquals(0, $this->file->getBytesRead());
        $this->assertTrue($this->file->seek(2, SEEK_END));
        $this->assertEquals(2, $this->file->getBytesRead());
    }

    /**
     * test seeking to offset
     *
     * @test
     */
    public function seekRead()
    {
        $this->file->setContent('foobarbaz');
        $this->assertFalse($this->file->seek(0, 55));
        $this->assertTrue($this->file->seek(0, SEEK_SET));
        $this->assertEquals('foobarbaz', $this->file->readUntilEnd());
        $this->assertEquals(0, $this->file->getBytesRead());
        $this->assertTrue($this->file->seek(5, SEEK_SET));
        $this->assertEquals('rbaz', $this->file->readUntilEnd());
        $this->assertEquals(5, $this->file->getBytesRead());
        $this->assertTrue($this->file->seek(0, SEEK_CUR));
        $this->assertEquals('rbaz', $this->file->readUntilEnd());
        $this->assertEquals(5, $this->file->getBytesRead(), 5);
        $this->assertTrue($this->file->seek(2, SEEK_CUR));
        $this->assertEquals('az', $this->file->readUntilEnd());
        $this->assertEquals(7, $this->file->getBytesRead());
        $this->assertTrue($this->file->seek(0, SEEK_END));
        $this->assertEquals('', $this->file->readUntilEnd());
        $this->assertEquals(9, $this->file->getBytesRead());
        $this->assertTrue($this->file->seek(2, SEEK_END));
        $this->assertEquals('', $this->file->readUntilEnd());
        $this->assertEquals(11, $this->file->getBytesRead());
    }

    /**
     * test writing data into the file
     *
     * @test
     */
    public function writeEmptyFile()
    {
        $this->assertEquals(3, $this->file->write('foo'));
        $this->assertEquals('foo', $this->file->getContent());
        $this->assertEquals(3, $this->file->size());
        $this->assertEquals(3, $this->file->write('bar'));
        $this->assertEquals('foobar', $this->file->getContent());
        $this->assertEquals(6, $this->file->size());
    }

    /**
     * test writing data into the file
     *
     * @test
     */
    public function write()
    {
        $this->file->setContent('foobarbaz');
        $this->assertTrue($this->file->seek(3, SEEK_SET));
        $this->assertEquals(3, $this->file->write('foo'));
        $this->assertEquals('foofoobaz', $this->file->getContent());
        $this->assertEquals(9, $this->file->size());
        $this->assertEquals(3, $this->file->write('bar'));
        $this->assertEquals('foofoobar', $this->file->getContent());
        $this->assertEquals(9, $this->file->size());
    }
}
?>