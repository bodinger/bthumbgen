<?php
class mtmdImage {

    /** Format constants. */
    const PORTRAIT = 1;
    const LANDSCAPE = 2;

    /** EXIF orientation constants. */
    const ORIENTATION_ORIGINAL                        = 1;
    const ORIENTATION_FLIP_HORIZONTAL                 = 2;
    const ORIENTATION_ROTATE_180_LEFT                 = 3;
    const ORIENTATION_FLIP_VERTICAL                   = 4;
    const ORIENTATION_FLIP_VERTICAL_ROTATE_90_RIGHT   = 5;
    const ORIENTATION_ROTATE_90_RIGHT                 = 6;
    const ORIENTATION_FLIP_HORIZONTAL_ROTATE_90_RIGHT = 7;
    const ORIENTATION_ROTATE_90_LEFT                  = 8;

    /** File info properties. */
    protected $fileName      = '';
    protected $size          = 0;
    protected $width         = 0;
    protected $height        = 0;
    protected $imageType     = null;
    protected $mimeType      = 0;
    protected $bitDepth      = 0;
    protected $channels      = 0;
    protected $format        = null;
    protected $thumbFileName = '';
    protected $thumbWidth    = 0;
    protected $thumbHeight   = 0;
    protected $exifData      = array();
    protected $orientation   = self::ORIENTATION_ORIGINAL;


    /**
     * Constructor.
     *
     * @param string $file
     *
     * @throws Exception
     */
    public function __construct($file)
    {
        if (empty($file)) {
            throw new Exception('Please provide a file!');
        }
        if (!file_exists($file)) {
            throw new Exception('File does not exist!');
        }
        $this->fileName = $file;

        $this->initialize();

    }


    private function initialize()
    {
        $this->getFileInfo();

    }


    /**
     * Determine all file info.
     *
     * @return void
     */
    private function getFileInfo()
    {
        $info            = getimagesize($this->fileName);
        $this->size      = filesize($this->fileName);
        if ($info !== false) {
            $this->width     = $info[0];
            $this->height    = $info[1];
            $this->imageType = $info[2];
            $this->bitDepth  = $info['bits'];
            if (array_key_exists('channels', $info)) {
                $this->channels  = $info['channels'];
            }
        }
        $this->mimeType  = $info['mime'];
        $this->format    = $this->determineFormat();

        if ($this->getType() === IMAGETYPE_JPEG) {
            $this->exifData = $this->readExifData();
        }

    }


    /**
     * Rotates/Flips image resource if necessary using EXIF data. If no EXIF data present nothing will be done.
     *
     * @param resource $imageResource
     *
     * @return resource
     */
    private function rotateOrFlip($imageResource)
    {
        $retResource = $imageResource;
        if (!array_key_exists('Orientation', $this->exifData)) {
            return $retResource;
        }

        switch ($this->exifData['Orientation']) {
            case self::ORIENTATION_FLIP_HORIZONTAL:
                $this->orientation = self::ORIENTATION_FLIP_HORIZONTAL;
                $this->flip($imageResource);
                break;
            case self::ORIENTATION_ROTATE_180_LEFT:
                $this->orientation = self::ORIENTATION_ROTATE_180_LEFT;
                $retResource = imagerotate($imageResource, 180, -1);
                break;
            case self::ORIENTATION_FLIP_VERTICAL:
                $this->orientation = self::ORIENTATION_FLIP_VERTICAL;
                $this->flip($imageResource);
                break;
            case self::ORIENTATION_FLIP_VERTICAL_ROTATE_90_RIGHT:
                $this->orientation = self::ORIENTATION_FLIP_VERTICAL_ROTATE_90_RIGHT;
                $this->flip($imageResource);
                $retResource = imagerotate($imageResource, -90, -1);
                break;
            case self::ORIENTATION_ROTATE_90_RIGHT:
                $this->orientation = self::ORIENTATION_ROTATE_90_RIGHT;
                $retResource = imagerotate($imageResource, -90, -1);
                break;
            case self::ORIENTATION_FLIP_HORIZONTAL_ROTATE_90_RIGHT:
                $this->orientation = self::ORIENTATION_FLIP_HORIZONTAL_ROTATE_90_RIGHT;
                $this->flip($imageResource);
                $retResource = imagerotate($imageResource, -90, -1);
                break;
            case self::ORIENTATION_ROTATE_90_LEFT:
                $this->orientation = self::ORIENTATION_ROTATE_90_LEFT;
                $retResource = imagerotate($imageResource, 90, -1);
                break;
        }

        if ($retResource === false) {
            return $imageResource;
            #return $this->fixBrokenOrientation($imageResource);
        }
        return $retResource;

    }


    /**
     * Fixes the orientation of some broken images. Those seem to be landscape but are portrait in reality.
     *
     * @param resource $imageResource
     *
     * @return resource
     */
    private function fixBrokenOrientation($imageResource) {
        $oldWidth = $this->getWidth();
        $oldHeight = $this->getHeight();
        $oldThumbWidth = $this->getThumbWidth();
        $oldThumbHeight = $this->getThumbHeight();
        if ($oldWidth > $oldHeight) {
            $this->width = $oldHeight;
            $this->height = $oldWidth;
            $this->orientation = self::ORIENTATION_ORIGINAL;
            $this->format = self::PORTRAIT;
            $this->thumbWidth = $oldThumbHeight;
            $this->thumbHeight = $oldThumbWidth;
        }
        return $imageResource;
    }


    /**
     * Flip image (UNTESTED)!!!
     *
     * @param resource $imageResource
     * @param int      $x
     * @param int      $y
     * @param null     $width
     * @param null     $height
     *
     * @return bool
     */
    private function flip(&$imageResource, $x = 0, $y = 0, $width = null, $height = null)
    {
        mtmdUtils::output('FLIPPING!');
        if ($width  < 1) {
            $width  = imagesx($imageResource);
        }
        if ($height < 1) {
            $height = imagesy($imageResource);
        }

        // Truecolor provides better results, if possible.
        if (function_exists('imageistruecolor') && imageistruecolor($imageResource)) {
            $tmp = imagecreatetruecolor(1, $height);
        } else {
            $tmp = imagecreate(1, $height);
        }

        $x2 = $x + $width - 1;

        for ($i = (int)floor(($width - 1) / 2); $i >= 0; $i--)
        {
            // Backup right stripe.
            imagecopy($tmp, $imageResource, 0, 0, $x2 - $i, $y, 1, $height);

            // Copy left stripe to the right.
            imagecopy($imageResource, $imageResource, $x2 - $i, $y, $x + $i, $y, 1, $height);

            // Copy backuped right stripe to the left.
            imagecopy($imageResource, $tmp, $x + $i,  $y, 0, 0, 1, $height);
        }

        imagedestroy($tmp);

        return true;
    }


    /**
     * Return exif data array.
     *
     * @return array
     */
    private function readExifData()
    {
        return exif_read_data($this->getFileName());
    }


    public function getFileName()
    {
        return $this->fileName;
    }


    public function getSize()
    {
        return $this->size;
    }


    public function getWidth()
    {
        return $this->width;
    }


    public function getHeight()
    {
        return $this->height;
    }


    public function getType()
    {
        return $this->imageType;
    }


    public function getMimeType()
    {
        return $this->mimeType;
    }


    public function getChannels()
    {
        return $this->channels;
    }


    public function getBitDepth()
    {
        return $this->bitDepth;
    }


    public function getFormat()
    {
        return $this->format;
    }


    public function getThumbFileName()
    {
        return $this->thumbFileName;
    }


    public function getThumbWidth()
    {
        return $this->thumbWidth;
    }


    public function getThumbHeight()
    {
        return $this->thumbHeight;
    }


    /**
     * Determine format landscape/portrait.
     *
     * @return int
     */
    public function determineFormat()
    {
        if ( $this->getWidth() > $this->getHeight() ) {
            return self::LANDSCAPE;
        }
        return self::PORTRAIT;

    }


    /**
     * Create a new image instance using proper imagecreatefrom method.
     *
     * @return resource
     */
    private function createImage()
    {
        switch($this->getType()) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($this->getFileName());
            case IMAGETYPE_GIF:
                return imagecreatefromgif($this->getFileName());
            case IMAGETYPE_PNG:
                return imagecreatefrompng($this->getFileName());
        }
    }


    /**
     * Resize image using width/height and orientation information and saves it to cache folder.
     *
     * @param string  $dstPath
     * @param integer $width
     * @param integer $height
     *
     * @return void
     */
    public function resizeImage($dstPath, $width, $height)
    {
        $cacheFile = $dstPath.DIRECTORY_SEPARATOR.basename($this->getFileName());
        $measures = $this->getNewMeasures($width, $height);

        $this->thumbFileName = $cacheFile;
        $this->thumbWidth    = $measures[0];
        $this->thumbHeight   = $measures[1];

        // Once file is there don't generate it again.
        if (file_exists($cacheFile)) {
            return;
        }

        $oldFile = $this->createImage();
        $oldFile = $this->rotateOrFlip($oldFile);

        // In case of orientation change determine correct measures.
        $this->setMeasuresByOrientation();
        $newFile = imagecreatetruecolor($this->getThumbWidth(), $this->getThumbHeight());
        
        imagecopyresampled(
            $newFile,
            $oldFile,
            0,
            0,
            0,
            0,
            $this->getThumbWidth(),
            $this->getThumbHeight(),
            $this->getWidth(),
            $this->getHeight()
        );

        $this->saveImage($newFile, $dstPath);

    }


    /**
     * Calculate new measures of thumbnail.
     *
     * @param integer $width
     * @param integer $height
     *
     * @return array Array containing width/height
     */
    private function getNewMeasures($width, $height)
    {
        $newWidth = $width;
        $newHeight = $height;
        if ($this->getWidth() > $this->getHeight() && $height < $this->getHeight()) {
            $newHeight = $this->getHeight() / ($this->getWidth() / $width);
        } else if ($this->getWidth() < $this->getHeight() && $width < $this->getWidth()) {
            $newWidth = $this->getWidth() / ($this->getHeight() / $height);
        } else if ($this->getWidth() == $this->getHeight()) {
            if ($width > $height) {
                $newHeight = $width;
            }
        } else {
            $newWidth = $this->getWidth();
            $newHeight = $this->getHeight();
        }

        // Take format type into account.
        if ($this->getFormat() === self::LANDSCAPE) {
            if ($newWidth < $newHeight) {
                return array(
                    $newHeight,
                    $newWidth
                );
            }
        } else if ($this->getFormat() === self::PORTRAIT) {
            if ($newHeight < $width) {
                $newHeight = $width;
                $newWidth = $this->getWidth() / ($this->getHeight() / $newHeight);
            }
        }

        return array(
            $newWidth,
            $newHeight
        );

    }


    /**
     * Sets measures internally dependant to image orientation.
     *
     * @return void
     */
    private function setMeasuresByOrientation()
    {
        // Set to default.
        $thumbWidth     = $this->getThumbWidth();
        $thumbHeight    = $this->getThumbHeight();
        $originalWidth  = $this->getWidth();
        $originalHeight = $this->getHeight();

        switch ($this->orientation) {
            // Orientations that do not need a measure change.
            case self::ORIENTATION_FLIP_HORIZONTAL:
            case self::ORIENTATION_ROTATE_180_LEFT:
            case self::ORIENTATION_FLIP_VERTICAL:
                break;
            // Orientations that need flipping of measures.
            case self::ORIENTATION_FLIP_VERTICAL_ROTATE_90_RIGHT:
            case self::ORIENTATION_ROTATE_90_RIGHT:
            case self::ORIENTATION_FLIP_HORIZONTAL_ROTATE_90_RIGHT:
            case self::ORIENTATION_ROTATE_90_LEFT:
                // Interchange measures.
                $thumbWidth     = $this->getThumbHeight();
                $thumbHeight    = $this->getThumbWidth();
                $originalWidth  = $this->getHeight();
                $originalHeight = $this->getWidth();
                break;
            // Original.
            default:
                break;
        }

        // Write to properties.
        $this->thumbWidth  = $thumbWidth;
        $this->thumbHeight = $thumbHeight;
        $this->width       = $originalWidth;
        $this->height      = $originalHeight;

    }


    /**
     * Saves image instance as new file to destination/cache folder.
     *
     * @param resource $newFile
     * @param string   $dstPath
     *
     * @return void
     */
    private function saveImage($newFile, $dstPath)
    {
        $dstPath = $dstPath.DIRECTORY_SEPARATOR.basename($this->getFileName());

        switch($this->getType()) {
            case IMAGETYPE_JPEG:
                imagejpeg($newFile, $dstPath);
                break;
            case IMAGETYPE_GIF:
                imagegif($newFile, $dstPath);
                break;
            case IMAGETYPE_PNG:
                imagepng($newFile, $dstPath);
                break;
        }
        return;

    }


}
