<?php
/**
 * Created by PhpStorm.
 * User: adactive
 * Date: 29/08/16
 * Time: 11:01
 */

namespace Signall\StorageBundle\Model;

use Signall\DataBundle\Model\SignallEntityInterface;

interface SignallFileInterface extends SignallEntityInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * @return ?string
     */
    public function getDescription();

    /**
     * @param ?string $description
     *
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return ?string
     */
    public function getContext();

    /**
     * @param ?string $context
     *
     * @return $this
     */
    public function setContext($context);

    /**
     * @return string
     */
    public function getContentHash();

    /**
     * @return string
     */
    public function getMimeType();

    /**
     * @return string
     */
    public function getKey();
}
