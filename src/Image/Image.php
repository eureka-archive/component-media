<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eureka\Component\Media\Image;

use Eureka\Component\Media\Image\Exception\ImageException;

/**
 * Class to manipulate image with gd extension.
 *
 * @author Romain Cottard
 */
class Image
{
    const EXTENSION_BY_TYPE = [
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
    ];

    /** @var string $filePathname File Pathname of the image. */
    protected $filePathname = '';

    /** @var int $width image width */
    protected $width = 0;

    /** @var int $height image height */
    protected $height = 0;

    /** @var int $type image type */
    protected $type = 0;

    /** @var string $mimeType image mime type */
    protected $mimeType = '';

    /** @var resource $image Resource of the image opened with gd functions. */
    protected $image = null;

    /** @var string $extension */
    protected $extension = '';

    /**
     * imagegd constructor.
     *
     * @param  string $filePathname
     * @throws \Eureka\Component\Media\Image\Exception\EmptyFileException
     * @throws \Eureka\Component\Media\Image\Exception\FileNotExistsException
     * @throws \Eureka\Component\Media\Image\Exception\ImageException
     */
    public function __construct($filePathname)
    {
        $this->setFilePathname($filePathname)->init();
    }

    /**
     * imagegd destructor.
     */
    public function __destruct()
    {
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }
    }

    /**
     * Display image.
     *
     * @param  bool $withHeader
     * @return void
     */
    public function display($withHeader = true)
    {
        if (!is_resource($this->image)) {
            $this->open();
        }

        //~ Set header jpeg
        if ($withHeader) {
            header('Content-Type: image/jpeg');
        }

        //~ Display the image
        imagejpeg($this->image);
    }

    /**
     * Set file pathname
     *
     * @param  string $filePathname
     * @return $this
     * @throws \Eureka\Component\Media\Image\Exception\EmptyFileException
     * @throws \Eureka\Component\Media\Image\Exception\FileNotExistsException
     */
    protected function setFilePathname($filePathname)
    {
        $this->filePathname = (string) $filePathname;

        if (empty($this->filePathname)) {
            throw new Exception\EmptyFileException(__METHOD__ . '|File cannot be empty !');
        }

        if (!file_exists($this->filePathname)) {
            throw new Exception\FileNotExistsException(__METHOD__ . '|File does not exist ! (file: "' . $this->filePathname . '")');
        }

        return $this;
    }

    /**
     * Return file pathname
     *
     * @return string
     */
    public function getFilePathname()
    {
        return $this->filePathname;
    }

    /**
     * Return file name
     *
     * @param  bool $withExtension
     * @return string
     */
    public function getFilename($withExtension = true)
    {
        if (!$withExtension) {
            return basename($this->getFilePathname(), '.' . $this->getExtension());
        }

        return basename($this->getFilePathname());
    }

    /**
     * Return file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return !empty(self::EXTENSION_BY_TYPE[$this->type]) ? self::EXTENSION_BY_TYPE[$this->type] : '';
    }

    /**
     * Get MD5 of the file content.
     *
     * @return string
     * @throws \Eureka\Component\Media\Image\Exception\ImageException
     */
    public function getFileMd5()
    {
        $md5 = md5_file($this->filePathname);

        if ($md5 === false) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to calculate md5 on the image file (file: "' . $this->filePathname . '")');
        }

        return $md5;
    }

    /**
     * Get image width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get image height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Get image radio.
     *
     * @return float
     */
    public function getRatio()
    {
        return (float) ($this->width / $this->height);
    }

    /**
     * Is landscape.
     *
     * @return bool
     */
    public function isLandscape()
    {
        return ($this->getRatio() > 1);
    }

    /**
     * Is portrait
     *
     * @return bool
     */
    public function isPortrait()
    {
        return ($this->getRatio() < 1);
    }

    /**
     * Is square
     *
     * @return bool
     */
    public function isSquare()
    {
        return ($this->getRatio() == 1);
    }

    /**
     * Initialise image information.
     *
     * @return $this
     * @throws \Eureka\Component\Media\Image\Exception\ImageException
     */
    protected function init()
    {
        if (empty($this->filePathname)) {
            throw new Exception\ImageException(__METHOD__ . '|Current file pathname is empty !');
        }

        if (!is_readable($this->filePathname)) {
            throw new Exception\ImageException(__METHOD__ . '|File cannot be read ! (file: "' . $this->filePathname . '")');
        }

        $info = getimagesize($this->filePathname);

        if (!is_array($info)) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to read image information !');
        }

        $this->width    = (int) $info[0];
        $this->height   = (int) $info[1];
        $this->type     = (int) $info[2];
        $this->mimeType = (string) $info['mime'];

        return $this;
    }

    /**
     * Open image resource
     *
     * @return $this
     * @throws ImageException
     * @throws \Eureka\Component\Media\Image\Exception\ImageException
     */
    protected function open()
    {
        switch ($this->type) {
            //
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($this->filePathname);
                break;
            //
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($this->filePathname);
                break;
            //
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($this->filePathname);
                break;
            //
            case IMAGETYPE_WBMP:
                $this->image = imagecreatefromwbmp($this->filePathname);
                break;
            //
            case IMAGETYPE_XBM:
                $this->image = imagecreatefromxbm($this->filePathname);
                break;
            //
            default:
                throw new Exception\ImageException(__METHOD__ . '|The format of the image is not currently supported by the library !');
        }

        if (!is_resource($this->image)) {
            throw new Exception\ImageException(__METHOD__ . '|Cannot create a resource for the current file !');
        }

        return $this;
    }

    /**
     * Crop image if necessary.
     *
     * @param  int $maxWidth
     * @param  int $maxHeight
     * @return $this
     * @throws \Eureka\Component\Media\Image\Exception\ImageException
     */
    public function crop($maxWidth, $maxHeight)
    {
        if (!is_resource($this->image)) {
            $this->open();
        }

        //~ Width & height are already under bound, don't crop the original image
        if ($this->width <= $maxWidth && $this->height <= $maxHeight) {
            return $this;
        }

        //~ Get max width/height for the cropped image
        $data         = new \stdClass();
        $data->width  = min($this->width, $maxWidth);
        $data->height = min($this->height, $maxHeight);

        //~ Get origin x/y from the original image where the crop start
        $data->y = $data->x = 0;

        if ($this->width  > $maxWidth) {
            $data->x = floor(abs($this->width - $maxWidth) / 2);
        }

        if ($this->height > $maxHeight) {
            $data->y = floor(abs($this->height - $maxHeight) / 2);
        }

        //~ Create new resource image
        $image = imagecreatetruecolor($data->width, $data->height);

        if (!is_resource($image)) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to create new resource image !');
        }

        //~ Copy
        if (!imagecopy($image, $this->image, 0, 0, $data->x, $data->y, $data->width, $data->height)) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to copy cropped image resource into new image resource !');
        }

        //~ Remove original image resource & use cropped image resource as original image resource
        imagedestroy($this->image);
        $this->image = $image;

        $this->width  = $data->width;
        $this->height = $data->height;

        return $this;
    }

    /**
     * Set image into square format if necessary
     *
     * @return $this
     * @throws ImageException
     */
    public function cropSquare()
    {
        if (!is_resource($this->image)) {
            $this->open();
        }

        //~ Already a square, don't crop the original image
        if ($this->width === $this->height) {
            return $this;
        }

        //~ Get max width/height for the cropped image
        $data        = new \stdClass();
        $data->width = $data->height = min($this->width, $this->height);

        //~ Get origin x/y from the original image where the crop start
        $data->x = $data->y = 0;
        $diff    = floor(abs($this->width - $this->height) / 2);
        if ($this->width > $this->height) {
            $data->x = $diff;
        } else {
            $data->y = $diff;
        }

        //~ Create new resource image
        $image = imagecreatetruecolor($data->width, $data->height);

        if (!is_resource($image)) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to create new resource image !');
        }

        //~ Copy
        if (!imagecopy($image, $this->image, 0, 0, $data->x, $data->y, $data->width, $data->height)) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to copy cropped image resource into new image resource !');
        }

        //~ Remove original image resource & use cropped image resource as original image resource
        imagedestroy($this->image);
        $this->image = $image;

        $this->width  = $data->width;
        $this->height = $data->height;

        return $this;
    }

    /**
     * Set image into square format if necessary
     *
     * @param  int $width
     * @param  int $height
     * @param  bool $keepRatio
     * @return $this
     * @throws ImageException
     */
    public function resize($width, $height, $keepRatio = true)
    {
        $width  = (int) $width;
        $height = (int) $height;

        if (!is_resource($this->image)) {
            $this->open();
        }

        //~ Already a square, don't crop the original image
        if ($this->width === $width && $this->height === $height) {
            return $this;
        }

        if ($keepRatio && $this->isLandscape()) {
            list($width, $height) = $this->calculateLandscapeWidthHeightWithSameRatio($width, $height);
        } elseif ($keepRatio) {
            list($width, $height) = $this->calculatePortraitWidthHeightWithSameRatio($width, $height);
        }

        //~ Create new resource
        $image = imagecreatetruecolor($width, $height);
        if ($this->type === IMAGETYPE_PNG) {
            imagesavealpha($image, true);
            imagealphablending($image, false);
        }

        if (!is_resource($image)) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to create new resource image !');
        }

        //~ Re-sampled in new resource
        if (!imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height)) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to resize the image !');
        }

        //~ Remove original image resource & use cropped image resource as original image resource
        imagedestroy($this->image);
        $this->image = $image;

        $this->width  = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Resize image based on new width (keep ratio)
     *
     * @param  int $width
     * @return $this
     * @throws ImageException
     */
    public function resizeOnWidth($width)
    {
        $width  = (int) $width;
        $height = (int) $this->height / ($this->width / $width);

        $this->resize($width, $height, true);

        return $this;
    }

    /**
     * Resize image base on new height (keep ratio).
     *
     * @param  int $height
     * @return $this
     * @throws ImageException
     */
    public function resizeOnHeight($height)
    {
        $width  = (int) $this->width / ($this->height / $height);
        $height = (int) $height;

        $this->resize($width, $height, true);

        return $this;
    }

    /**
     * Save image into jpeg format to the specified path.
     *
     * @param  string  $filePathname
     * @param  int $quality
     * @return $this New instance for the saved image.
     * @throws Exception\ImageException
     */
    public function saveAsJpeg($filePathname, $quality = 100)
    {
        if (!is_resource($this->image)) {
            $this->open();
        }

        if (!imagejpeg($this->image, $filePathname, $quality)) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to save the image into jpeg format !');
        }

        return new Image($filePathname);
    }

    /**
     * Save image into png format to the specified path.
     *
     * @param  string  $filePathname
     * @param  int $quality 0 to 100 (in percent)
     * @return $this New instance for the saved image.
     * @throws Exception\ImageException
     */
    public function saveAsPng($filePathname, $quality = 100)
    {
        //~ Convert percent quality to 0-9 compression value
        $compression = abs(ceil($quality / 10) - 10);

        if (!is_resource($this->image)) {
            $this->open();
        }

        // Required to save alpha canal
        imagealphablending($this->image, false);
        // Required to save transparency
        imagesavealpha($this->image, true);

        if (!imagepng($this->image, $filePathname, $compression)) {
            throw new Exception\ImageException(__METHOD__ . '|Unable to save the image into png format !');
        }

        return new Image($filePathname);
    }

    /**
     * Save image into specified format on into the path for cdn content.
     *
     * @param  string  $path
     * @param  int $format (Use IMAGETYPE_XXX constant from PHP)
     * @param  int $quality
     * @param  string $fileSuffix
     * @param  string $filePrefix
     * @return $this
     * @throws \Eureka\Component\Media\Image\Exception\ImageException
     */
    public function saveForCdn($path, $format = IMAGETYPE_JPEG, $quality = 100, $fileSuffix = '', $filePrefix = '')
    {
        $path = rtrim($path, '/');
        //~ Tmp file
        $filePathnameTmp = tempnam(sys_get_temp_dir(), 'IMAGEGD_');

        switch ($format) {
            //
            case IMAGETYPE_JPEG:
                $image     = $this->saveAsJpeg($filePathnameTmp, $quality);
                break;
            //
            case IMAGETYPE_PNG:
                $image     = $this->saveAsPng($filePathnameTmp, $quality);
                break;
            //
            default:
                throw new Exception\ImageException(__METHOD__ . '|Output format is not supported !');

        }

        //~ Generate final file pathname
        $extension       = '.' . self::EXTENSION_BY_TYPE[$format];
        $md5             = $image->getFileMd5();
        $subPath         = '/' . $md5{0} . '/' . $md5{1} . '/' . $md5{2};
        $filePathnameNew = $path . $subPath . '/' . $filePrefix . $md5 . $fileSuffix . $extension;

        //~ Move file into final place
        if (!rename($image->getFilePathname(), $filePathnameNew)) {
            unset($image);
            throw new Exception\ImageException(__METHOD__ . '|Unable to move tmp file final destination (destination: "' . $filePathnameNew . '")');
        }

        //~ Destroy tmp image
        unset($image);

        //~ Create new imagegd with the final image file
        return new Image($filePathnameNew);
    }

    /**
     * @param  int $width
     * @param  int $height
     * @return int[]
     */
    private function calculateLandscapeWidthHeightWithSameRatio($width, $height)
    {
        $calcHeight = $this->height / ($this->width / $width);
        $calcWidth  = $this->width / ($this->height / $height);

        if ($calcHeight >= $height) {
            $width = $calcWidth;
        } else {
            $height = $calcHeight;
        }

        return [$width, $height];
    }

    /**
     * @param  int $width
     * @param  int $height
     * @return int[]
     */
    private function calculatePortraitWidthHeightWithSameRatio($width, $height)
    {
        $calcHeight = $this->height / ($this->width / $width);
        $calcWidth  = $this->width / ($this->height / $height);

        if ($calcWidth < $width) {
            $width = $calcWidth;
        } else {
            $height = $calcHeight;
        }

        return [$width, $height];
    }
}
