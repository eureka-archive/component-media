<?php

/**
 * Copyright (c) 2010-2017 Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Component\Media\Video;

/**
 * Class ImageLink
 *
 * @author  Romain Cottard
 */
class YoutubeLink
{
    /**
     * @var string $baseUri Base uri of the image
     */
    private $baseUri = 'https://www.youtube.com/watch?v=';

    /**
     * @var string $md5 MD5 hash of the image.
     */
    private $id = '';

    /**
     * Class constructor.
     *
     * @param string $id
     * @param string $baseUri
     */
    public function __construct($id, $baseUri = 'http://www.youtube.com/watch?v=')
    {
        $this->setBaseUri($baseUri);
        $this->setId($id);
    }

    /**
     * Get uri for the given image.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->baseUri . $this->getId();
    }

    /**
     * Set ID.
     *
     * @param  string $id
     * @return self
     */
    public function setId($id)
    {
        if (!is_string($id)) {
            throw new \InvalidArgumentException('ID argument must be a valid string!');
        }

        $id = trim($id);
        if (!(bool) preg_match('`^[A-Za-z0-9]+$`', $id)) {
            throw new \InvalidArgumentException('Argument is not a valid ID hash!');
        }

        $this->id = $id;

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
