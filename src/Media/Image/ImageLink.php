<?php

/**
 * Copyright (c) 2010-2017 Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Component\Media\Image;

/**
 * Class ImageLink
 *
 * @author  Romain Cottard
 */
class ImageLink
{
    /**
     * @var string EXT_JPEG jpeg extension.
     */
    const EXT_JPEG = 'jpeg';

    /**
     * @var string EXT_JPG jpg extension (without 'e')
     */
    const EXT_JPG = 'jpg';

    /**
     * @var string EXT_GIF gif extension
     */
    const EXT_GIF = 'gif';

    /**
     * @var string EXT_PNG png extension
     */
    const EXT_PNG = 'png';

    /**
     * @var string $baseUri Base uri of the image
     */
    private $baseUri = '';

    /**
     * @var string $path
     */
    private $path = '';

    /**
     * @var string $md5 MD5 hash of the image.
     */
    private $md5 = '';

    /**
     * @var string $extension Extension file for the image.
     */
    private $extension = 'jpg';

    /**
     * Class constructor.
     *
     * @param string $md5
     * @param string $baseUri
     * @param string $extension
     */
    public function __construct($md5, $baseUri = '', $extension = 'jpg')
    {
        $this->setBaseUri($baseUri);
        $this->setMd5($md5);
        $this->setExtension($extension);
    }

    /**
     * Get uri for the given image.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->baseUri . $this->getSubpath() . $this->getFilename();
    }

    /**
     * Get uri for the given image.
     *
     * @return string
     */
    public function getUriThumbnail($width, $height)
    {
        return $this->baseUri . '/' . $width . '/' . $height . '/' . $this->getFilename();
    }

    /**
     * Get filename given image.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->md5 . '.' . $this->extension;
    }

    /**
     * Get subpath for the given image.
     *
     * @return string
     */
    public function getSubpath()
    {
        return '/' . $this->md5{0} . '/' . $this->md5{1} . '/' . $this->md5{2} . '/';
    }

    /**
     * Set extension.
     *
     * @param  string $extension
     * @return self
     */
    public function setExtension($extension)
    {
        if (!is_string($extension)) {
            throw new \InvalidArgumentException('Extension argument must be a valid string!');
        }

        $extension = trim($extension);

        switch ($extension) {
            case self::EXT_JPG:
            case self::EXT_JPEG:
            case self::EXT_GIF:
            case self::EXT_PNG:
                $this->extension = $extension;
                break;
            default:
                throw new \DomainException('Invalid extension given!');
        }

        return $this;
    }

    /**
     * Set MD5 hash.
     *
     * @param  string $md5
     * @return self
     */
    public function setMd5($md5)
    {
        if (!is_string($md5)) {
            throw new \InvalidArgumentException('MD5 hash argument must be a valid string!');
        }

        $md5 = trim($md5);
        if (!(bool) preg_match('`^[A-Fa-f0-9]{32}$`', $md5)) {
            throw new \InvalidArgumentException('Argument is not a valid MD5 hash!');
        }

        $this->md5 = $md5;

        return $this;
    }

    /**
     * Set base uri.
     *
     * @param  string $baseUri
     * @return self
     */
    public function setBaseUri($baseUri)
    {
        if (!is_string($baseUri)) {
            throw new \InvalidArgumentException('Base Uri argument must be a valid string!');
        }

        $this->baseUri = rtrim(trim($baseUri), '/');

        return $this;
    }
}
