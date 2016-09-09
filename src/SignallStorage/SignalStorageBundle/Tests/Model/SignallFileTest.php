<?php


namespace Signall\StorageBundle\Tests;


use Signall\StorageBundle\Model\SignallFile;

class SignallFileTest extends \PHPUnit_Framework_TestCase
{
    protected $SignallFile;

    public function __construct()
    {
        $contentHash = "some content hash";
        $mimeType = "application/pdf";
        $size = 524524230;
        $key = "some key";

        $this->SignallFile = new SignallFile($contentHash, $mimeType, $size, $key);;
    }
    public function testConstructor()
    {

        $contentHash = "some content hash";
        $mimeType = "application/pdf";
        $size = 524524230;
        $key = "some key";

        $mockFile = $this->createMock(SignallFile::class);

        $mockFile->expects($this->never())
            ->method($this->anything());
        
//        $file = new SignallFile($contentHash, $mimeType, $size, $key);

        $this->assertEquals($contentHash, $this-> SignallFile -> getContentHash());
        $this->assertEquals($mimeType, $this-> SignallFile -> getMimeType());
        $this->assertEquals($size, $this-> SignallFile -> getSize());
        $this->assertEquals($key, $this-> SignallFile -> getKey());
    }

    public function testGetSetName()
    {
        $name = "GeorgesAbitbol";

        $this->SignallFile -> setName($name);

        $this -> assertEquals($name,$this->SignallFile->getName());
    }
    public function testGetSetDescription()
    {
        $description = "l'homme le plus classe du monde";

        $this->SignallFile -> setDescription($description);

        $this -> assertEquals($description,$this->SignallFile->getDescription());
    }
    public function testGetSetContext()
    {
        $context = "ce flim n'est pas un flim sur le cyclimse";

        $this->SignallFile -> setContext($context);

        $this -> assertEquals($context,$this->SignallFile->getContext());
    }
}