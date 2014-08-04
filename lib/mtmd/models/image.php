<?php
class mtmdImage {

    const PORTRAIT = 1;
    const LANDSCAPE = 2;

    protected $fileName = '';

    protected $size = 0;
    protected $width = 0;
    protected $height = 0;
    protected $imageType = null;
    protected $mimeType = 0;
    protected $bitDepth = 0;
    protected $channels = 0;
    protected $format = null;
    protected $thumbFileName = '';

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


    public function determineFormat()
    {
        if ( $this->getWidth() > $this->getHeight() ) {
            return self::LANDSCAPE;
        }
        return self::PORTRAIT;

    }


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


    public function resizeImage($dstPath, $width, $height)
    {
        $measures = $this->getNewMeasures($width, $height);

        $newFile = imagecreatetruecolor($measures[0], $measures[1]);
        imagecopyresampled(
            $newFile,
            $this->createImage(),
            0,
            0,
            0,
            0,
            $measures[0],
            $measures[1],
            $this->getWidth(),
            $this->getHeight()
        );
        $this->saveImage($newFile, $dstPath);

    }


    private function getNewMeasures($width, $height)
    {
        if ($this->getWidth() > $this->getHeight() && $height < $this->getHeight()){
            $height = $this->getHeight() / ($this->getWidth() / $width);
        } else if ($this->getWidth() < $this->getHeight() && $width < $this->getWidth()) {
            $width = $this->getWidth() / ($this->getHeight() / $height);
        } else {
            $width = $this->getWidth();
            $height = $this->getHeight();
        }
        return array(
            $width,
            $height
        );

    }


    private function saveImage($newFile, $dstPath)
    {
        $dstPath = $dstPath.DIRECTORY_SEPARATOR.basename($this->getFileName());
        $this->thumbFileName = $dstPath;

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