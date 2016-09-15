<?php
/**
 * Created by PhpStorm.
 * User: adactive
 * Date: 29/08/16
 * Time: 11:59
 */

namespace Signall\StorageBundle\Tests;


use Gaufrette\Filesystem;
use Signall\StorageBundle\FileStorage;
use Signall\StorageBundle\Model\SignallFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * A TestCase defines the fixture to run multiple tests.
 *
 * To define a TestCase
 *
 *   1) Implement a subclass of PHPUnit_Framework_TestCase.
 *   2) Define instance variables that store the state of the fixture.
 *   3) Initialize the fixture state by overriding setUp().
 *   4) Clean-up after a test by overriding tearDown().
 *
 * Each test runs in its own fixture so there can be no side effects
 * among test runs.
 *
 * Here is an example:
 *
 * <code>
 * <?php
 * class MathTest extends PHPUnit_Framework_TestCase
 * {
 *     public $value1;
 *     public $value2;
 *
 *     protected function setUp()
 *     {
 *         $this->value1 = 2;
 *         $this->value2 = 3;
 *     }
 * }
 * ?>
 * </code>
 *
 * For each test implement a method which interacts with the fixture.
 * Verify the expected results with assertions specified by calling
 * assert with a boolean.
 *
 * <code>
 * <?php
 * public function testPass()
 * {
 *     $this->assertTrue($this->value1 + $this->value2 == 5);
 * }
 * ?>
 * </code>
 *
 * @since Class available since Release 2.0.0
 */
class FileStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testConstructor()
    {
        $fs = $this->createMock(Filesystem::class);

        // Constructor shouldn't do nothing at all on constructor !
        $fs->expects($this->never())
            ->method($this->anything());

        $fileStorage = new FileStorage($fs);

        $this->assertEquals($fs, $fileStorage->getFilesystem());
    }

    public function testCreateFile()
    {
        $binary = $this->createMock(File::class);
        $fixedBinary = $this->createMock(File::class);
        $signallFile = $this->createMock(SignallFile::class);

        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileStorage */
        $fileStorage = $this->createPartialMock(
            FileStorage::class,
            ["getFixedSymfonyFile", "createSignallFileEntity", "write"]
        );

        $fileStorage->expects($this->once())
            ->method("getFixedSymfonyFile")
            ->with($binary)
            ->willReturn($fixedBinary);

        $fileStorage->expects($this->once())
            ->method("createSignallFileEntity")
            ->with($fixedBinary)
            ->willReturn($signallFile);

        $fileStorage->expects($this->once())
            ->method("write")
            ->with($fixedBinary, $signallFile);

        $this->assertEquals($signallFile, $fileStorage->createFile($binary));
    }

    public function testCreateSignallFileEntity()
    {
        $binary = $this->createMock(File::class);
        $expectedMd5 = md5("This is a random string that will help generating an awesome md5");
        $expectedMimeType = "application/pdf";
        $expectedSize = 254136;
        $extension = "pdf";
        $expectedKey = "directory/wtf/$expectedMd5.$extension";

        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileSystem */
        $fileSystem = $this->createPartialMock(
            FileStorage::class,
            ["getSymfonyFileMd5", "generateKey"]
        );

        $fileSystem->expects($this->once())
            ->method("getSymfonyFileMd5")
            ->with($binary)
            ->willReturn($expectedMd5);

        $fileSystem->expects($this->once())
            ->method("generateKey")
            ->with($expectedMd5, $extension)
            ->willReturn($expectedKey);

        $binary->expects($this->once())
            ->method("getExtension")
            ->with()
            ->willReturn($extension);

        $binary->expects($this->once())
            ->method("getMimeType")
            ->with()
            ->willReturn($expectedMimeType);

        $binary->expects($this->once())
            ->method("getSize")
            ->with()
            ->willReturn($expectedSize);

        $file = $this->invokeMethod($fileSystem, "createSignallFileEntity", [$binary]);

        $this->assertInstanceOf(SignallFile::class, $file);
        $this->assertEquals($expectedMd5, $file->getContentHash());
        $this->assertEquals($expectedMimeType, $file->getMimeType());
        $this->assertEquals($expectedSize, $file->getSize());
        $this->assertEquals($expectedKey, $file->getKey());
    }

    public function testWriteAlreadyExistingFile()
    {
        $binary = $this->createMock(File::class);
        $signallFile = $this->createMock(SignallFile::class);
        $key = "a random key";
        $fs = $this->createMock(Filesystem::class);

        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileStorage */
        $fileStorage = $this->createPartialMock(FileStorage::class, ["getFilesystem"]);

        $fileStorage->method("getFilesystem")
            ->with()
            ->willReturn($fs);

        $signallFile->expects($this->once())
            ->method("getKey")
            ->with()
            ->willReturn($key);

        $fs->expects($this->once())
            ->method("has")
            ->with($key)
            ->willReturn(true);

        $this->invokeMethod($fileStorage, "write", [$binary, $signallFile]);
    }

    public function testWrite()
    {
        $binary = $this->createMock(File::class);
        $signallFile = $this->createMock(SignallFile::class);
        $key = "a random key";
        $content = "The content is those binary !!!!";
        $fs = $this->createMock(Filesystem::class);

        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileStorage */
        $fileStorage = $this->createPartialMock(FileStorage::class, ["getFilesystem", "getBinaryContent"]);

        $fileStorage->method("getFilesystem")
            ->with()
            ->willReturn($fs);

        $fileStorage->method("getBinaryContent")
            ->with($binary)
            ->willReturn($content);

        $signallFile->expects($this->once())
            ->method("getKey")
            ->with()
            ->willReturn($key);

        $fs->expects($this->once())
            ->method("has")
            ->with($key)
            ->willReturn(false);

        $fs->expects($this->once())
            ->method("write")
            ->with($key, $content, false);

        $this->invokeMethod($fileStorage, "write", [$binary, $signallFile]);
    }

    /**
     * @dataProvider providerTestGenerateKey
     * 
     * @param string $md5
     * @param string $extension
     * @param string $expectedKey
     */
    public function testGenerateKey($md5, $extension, $expectedKey)
    {
        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileStorage */
        $fileStorage = $this->createPartialMock(FileStorage::class,[]);
        $key = $this->invokeMethod($fileStorage, "generateKey", [$md5, $extension]);

        // We cannot use assertEquals as we there is an error it will display full variables content ! That's silly !!
        $this->assertEquals($expectedKey, $key);
    }

    public function providerTestGenerateKey()
    {
        return [
            ["9a6eeb987cb9d5e5e8f1fec838f2f9ba", "png", "9a6".DIRECTORY_SEPARATOR."eeb".DIRECTORY_SEPARATOR."987".DIRECTORY_SEPARATOR."cb9".DIRECTORY_SEPARATOR."d5e".DIRECTORY_SEPARATOR."5e8".DIRECTORY_SEPARATOR."f1f".DIRECTORY_SEPARATOR."ec8".DIRECTORY_SEPARATOR."38f".DIRECTORY_SEPARATOR."2f9".DIRECTORY_SEPARATOR."9a6eeb987cb9d5e5e8f1fec838f2f9ba.png"],
            ["9a6eeb987cb9d5e5e8f1fec838f2f9ba", "jpeg", "9a6".DIRECTORY_SEPARATOR."eeb".DIRECTORY_SEPARATOR."987".DIRECTORY_SEPARATOR."cb9".DIRECTORY_SEPARATOR."d5e".DIRECTORY_SEPARATOR."5e8".DIRECTORY_SEPARATOR."f1f".DIRECTORY_SEPARATOR."ec8".DIRECTORY_SEPARATOR."38f".DIRECTORY_SEPARATOR."2f9".DIRECTORY_SEPARATOR."9a6eeb987cb9d5e5e8f1fec838f2f9ba.jpeg"],
        ];
    }

    /**
     * @dataProvider providerBinaryFixtures
     * @param string $binaryPath
     */
    public function testGetSymfonyFileMd5($binaryPath)
    {
        $binary = new File($binaryPath);
        $expectedMd5 = md5_file($binaryPath);

        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileStorage */
        $fileStorage = $this->createPartialMock(FileStorage::class, []);
        $md5 = $this->invokeMethod($fileStorage, "getSymfonyFileMd5", [$binary]);

        // We cannot use assertEquals as we there is an error it will display full variables content ! That's silly !!
        $this->assertEquals($expectedMd5, $md5);
    }

    /**
     * @dataProvider providerBinaryFixtures
     * @param string $binaryPath
     */
    public function testGetBinaryContent($binaryPath)
    {
        $binary = new File($binaryPath);
        $expectedContent = file_get_contents($binaryPath);

        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileStorage */
        $fileStorage = $this->createPartialMock(FileStorage::class, []);
        $content = $this->invokeMethod($fileStorage, "getBinaryContent", [$binary]);

        // We cannot use assertEquals as we there is an error it will display full variables content ! That's silly !!
        $this->assertTrue($expectedContent === $content, "Binary content mismatch");
    }


    public function providerBinaryFixtures()
    {
        return [
            [join(DIRECTORY_SEPARATOR, [__DIR__, "Fixtures", "Files", "img.jpg"])],
            [join(DIRECTORY_SEPARATOR, [__DIR__, "Fixtures", "Files", "music.mp3"])],
            [join(DIRECTORY_SEPARATOR, [__DIR__, "Fixtures", "Files", "pdfFile.pdf"])],
        ];
    }

    public function testGetFixedSymfonyFileWhichIsValid()
    {
        $binary = $this->createMock(File::class);

        $extension = "pdf";
        
        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileStorage */
        $fileStorage = $this->createPartialMock(FileStorage::class, []);
        
        $binary->expects($this->once())
            ->method("guessExtension")
            ->with()
            ->willReturn($extension);
        
        $binary->expects($this->once())
            ->method("getExtension")
            ->with()
            ->willReturn($extension);
        
        $this->assertEquals($binary, $this->invokeMethod($fileStorage, "getFixedSymfonyFile", [$binary]));
    }

    public function testGetFixedSymfonyFileWhichIsInvalid()
    {
        $binary = $this->createMock(File::class);
        $fixedBinary = $this->createMock(File::class);

        $directory = "directory/stored";
        $baseName = "an.image.in_pdf";

        $guessed = "png";
        $extension = "pdf";
        
        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileStorage */
        $fileStorage = $this->createPartialMock(FileStorage::class, []);
        
        $binary->expects($this->once())
            ->method("guessExtension")
            ->with()
            ->willReturn($guessed);
        
        $binary->expects($this->once())
            ->method("getExtension")
            ->with()
            ->willReturn($extension);
        
        $binary->expects($this->once())
            ->method("getPath")
            ->with()
            ->willReturn($directory);
        
        $binary->expects($this->once())
            ->method("getBasename")
            ->with(".$extension")
            ->willReturn($baseName);
        
        $binary->expects($this->once())
            ->method("move")
            ->with()
            ->willReturn($fixedBinary, "$baseName.$guessed");
        
        $this->assertEquals($fixedBinary, $this->invokeMethod($fileStorage, "getFixedSymfonyFile", [$binary]));
    }

    public function testGetFixedSymfonyFileWhichIsInvalidNoExtensionCase()
    {
        $binary = $this->createMock(File::class);
        $fixedBinary = $this->createMock(File::class);

        $directory = "directory/stored";
        $baseName = "an.image.in_pdf";

        $guessed = "png";
        
        /** @var FileStorage|\PHPUnit_Framework_MockObject_MockObject $fileStorage */
        $fileStorage = $this->createPartialMock(FileStorage::class, []);
        
        $binary->expects($this->once())
            ->method("guessExtension")
            ->with()
            ->willReturn($guessed);
        
        $binary->expects($this->once())
            ->method("getExtension")
            ->with()
            ->willReturn("");
        
        $binary->expects($this->once())
            ->method("getPath")
            ->with()
            ->willReturn($directory);
        
        $binary->expects($this->once())
            ->method("getBasename")
            ->with("")
            ->willReturn($baseName);
        
        $binary->expects($this->once())
            ->method("move")
            ->with()
            ->willReturn($fixedBinary, "$baseName.$guessed");
        
        $this->assertEquals($fixedBinary, $this->invokeMethod($fileStorage, "getFixedSymfonyFile", [$binary]));
    }

    public function testGeneratePublicUrl(){
        $request = $this->createMock(Request::class);
        $file = $this->createMock(SignallFile::class);
        $fileStorage = $this->createPartialMock(
            FileStorage::class,
            ["getHost","getAbsolutePath"]
        );

        $host = "www.signall-ouf.com";
        $absPath = "/abs/path/ouf";

        $request->expects($this->once())
            ->method("getHost")
            ->with()
            ->willReturn($host);

        $fileStorage -> expects($this->once())
            ->method('getAbsolutePath')
            ->with($file)
            ->willReturn($absPath);

        $this->assertEquals($absPath, $fileStorage->generatePublicUrl($request,$file));

    }
}
