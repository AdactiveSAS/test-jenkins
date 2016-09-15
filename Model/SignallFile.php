<?php
/**
 * Created by PhpStorm.
 * User: adactive
 * Date: 29/08/16
 * Time: 10:56
 */

namespace Signall\StorageBundle\Model;


use Signall\DataBundle\Model\AbstractSignallEntity;

/**
 * Class AbstractSignallEntity
 * @package Signall\DataBundle\Model
 *
 * @ORM\MappedSuperclass()
 */
class SignallFile extends AbstractSignallEntity implements SignallFileInterface
{
    /**
     * @var string
     * 
     * @Assert\Type("string")
     * @Assert\NotNull()
     */
    protected $name;

    /**
     * @var ?string
     * @Assert\Type("string")
     */
    protected $description;

    /**
     * @var ?string
     * @Assert\Type("string")
     */
    protected $context;

    /**
     * @var string
     * 
     * @Assert\Type("string")
     * @Assert\Length(min=32, max=32)
     */
    protected $contentHash;

    /**
     * @var string
     * 
     * @Assert\Type("string")
     * @Assert\NotNull()
     */
    protected $mimeType;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     * 
     * @Assert\Type("string")
     * @Assert\NotNull()
     */
    protected $key;

    /**
     * Constructs a new file from the given path.
     *
     * @param string $contentHash
     * @param string $mimeType
     * @param int $size
     * @param string $key
     */
    public function __construct($contentHash, $mimeType, $size, $key)
    {
        $this->contentHash = $contentHash;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }

    /**
     * @return ?string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param ?string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }

    /**
     * @return ?string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param ?string $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getContentHash()
    {
        return $this->contentHash;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}
