<?php
/**
 * Created by PhpStorm.
 * User: adactive
 * Date: 29/08/16
 * Time: 10:54
 */

namespace Signall\StorageBundle;


use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use Signall\StorageBundle\Model\SignallFile;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

class FileStorage
{

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * FileStorage constructor.
     *
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->fs = $fileSystem;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->fs;
    }

    /**
     * @param File $binary
     *
     * @return SignallFile
     */
    public function createFile(File $binary)
    {
        $file = $this->getFixedSymfonyFile($binary);
        $signallEntity = $this->createSignallFileEntity($file);

        $this->write($binary, $signallEntity);

        return $signallEntity;

    }

    /**
     * @param File $binary
     *
     * @return SignallFile
     */
    protected function createSignallFileEntity(File $binary)
    {

        // May throw FileNotFoundException
        // TODO: Create the SignallFile
        // Key
        // ContentHash
        // Extension
        // MimeType
        // Store the file !
        // Handle if file already exists !

        $ext = $binary->getExtension();

        $contentHash = $this->getSymfonyFileMd5($binary);
        $mimeType = $binary->getMimeType();
        $size = $binary->getSize();
        $key = $this->generateKey($contentHash, $ext);

        $signallFileEntity = new SignallFile($contentHash, $mimeType, $size, $key);

        return $signallFileEntity;

    }

    /**
     * @param File $file
     *
     * @return string
     */
    protected function getSymfonyFileMd5(File $file)
    {
        return md5_file($file);
    }

    /**
     * @param string $md5   md5 hash of the file
     * @param string $ext   extension of the file
     * @param int    $index index of current recursion
     * @param string $key   current generated key
     *
     * @return string
     */
    protected function generateKey(
        string $md5,
        string $ext,
        int $index = 0,
        string $key = ''
    ) {
        $segmentedMd5Path = str_split($md5, 3);
        array_pop($segmentedMd5Path);
        $directory = join(DIRECTORY_SEPARATOR, $segmentedMd5Path);
        $filename = $directory.DIRECTORY_SEPARATOR.$md5.'.'.$ext;

        return $filename;

    }

    /**
     * @param File $binary
     *
     * @return string
     */
    protected function getBinaryContent(File $binary)
    {
        return file_get_contents($binary->getRealPath());
    }

    /**
     * @param File $file
     *
     * @return File $file
     */
    protected function getFixedSymfonyFile(File $file)
    {
        $guessedExtension = $file->guessExtension();
        $extension = $file->getExtension();
        if ($extension !== $guessedExtension) {
            return $file->move(
                $file->getPath(),
                $file->getBasename(empty($extension) ? "" : ".$extension").".$guessedExtension"
            );
        }

        return $file;
    }

    /**
     * @param SignallFile $file
     * @param File        $binary
     */
    public function update(SignallFile $file, File $binary)
    {
        // May throw FileNotFoundException
        // TODO: Update the given signallFile
        // Key
        // ContentHash
        // Extension
        // MimeType
        // Store the file ! (do not remove old one)
        // Handle if file already exists !


    }

    /**
     * @param SignallFile $file
     *
     * @return \Gaufrette\File
     */
    public function read(SignallFile $file)
    {
        // May throw FileNotFoundException
        // TODO: Returns the content file

        if ($this->getFilesystem()->get($file->getKey())) {
            return $this->getFilesystem()->get($file->getKey());
        } else {
            throw new FileNotFoundException("File not found");
        }

    }

    /**
     * @param File        $binary
     * @param SignallFile $file
     */
    public function write(File $binary, SignallFile $file)
    {
        $key = $file->getKey();
        $fs = $this->getFilesystem();
        $isFile = $fs->has($key);

        if (!$isFile) {
            $fs->write($key, $this->getBinaryContent($binary));
        }
    }

    /**
     * @param string $path
     *
     * @return File
     */
    public function getBinaryFromPath($path)
    {
        // May throw FileNotFoundException
        // TODO: try download or create a SymfonyFile
    }


    /**
     *
     * @param Request $request
     * @param SignallFile $file
     * @return string
     */
    public function generatePublicUrl(Request $request, SignallFile $file){
        $hostName = $request->getHost();
        $absPath = $this->getAbsolutePath($file);
        $path = str_replace("/web/",$hostName,$absPath);

        return $path;
    }

    /**
     * @param SignallFile $file
     * @return string
     */
    public function getAbsolutePath(SignallFile $file){
        $fs = $this->fs;
        $adapter = $fs->getAdapter();
        if($adapter instanceof Local){
            $absolutePath = $this->getWebDirectory($file).$file->getKey();
            return $absolutePath;
        }
    }

    /**
     * TODO externaliser le dossier d'uploads dans la config
     * @return string
     */
    public function getWebDirectory(){
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        return $rootDir."/web/";
    }

    /**
     * @param SignallFile $file
     *
     * @return SignallFile
     */
    public function copy(SignallFile $file)
    {
        return new SignallFile(
            $file->getContentHash(),
            $file->getMimeType(),
            $file->getSize(),
            $file->getKey()
            );
    }
}
