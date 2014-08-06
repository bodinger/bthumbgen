<?php
class mtmdImageApi {

    const TARGET_SOURCE = 1;
    const TARGET_CACHE  = 2;

    protected $supportedTypes = array(
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_GIF
    );

    /** @var string Source folder. */
    protected $folderSrc = '';
    protected $folderRoot = '';

    /** @var string Cache folder */
    protected $folderCache = '';

    protected $dirhandleSrc = null;
    protected $dirhandleCache = null;

    protected $fileList = array();

    protected $thumbWidth = 400;
    protected $thumbHeight = 300;


    /**
     * Creates instance.
     *
     * @param string $srcFolder   Source folder
     * @param string $cacheFolder Cache folder
     */
    public function __construct($srcFolder, $cacheFolder, $root)
    {
        $this->folderSrc = $srcFolder;
        $this->folderRoot = $root;
        $this->folderCache = $cacheFolder;
        $this->initialize();

    }


    /**
     * Initialize instance.
     *
     * @throws Exception
     */
    private function initialize()
    {
        if (empty($this->folderSrc) || empty($this->folderCache)) {
            throw new Exception('Please provide a source and a destination folder!');
        }

        if ( !is_dir($this->folderSrc) || !is_dir($this->folderCache) ) {
            mtmdUtils::output($this->folderCache);
            mtmdUtils::mkDir($this->folderCache);
        }

    }


    /**
     * Gets a list of image objects and writes it to class property for later usage.
     *
     * @param int $source Source type (images/cache)
     *
     * @return mtmdImage[] Array of mtmdImage instances.
     */
    public function getNewList($source = self::TARGET_SOURCE)
    {
        $folder = $this->folderSrc;
        if ($source != self::TARGET_SOURCE) {
            $folder = $this->folderCache;
        }

        // Get file list and determine image info.
        $files = mtmdUtils::listDir($folder, true);
        $this->fileList = array();

        $processedFolders = array();

        foreach ($files as $file) {
            $relFolderPath = $this->extractRelativeFolderPath($folder, $file);
            if (in_array($relFolderPath, $processedFolders)) {
                continue;
            }
            if ($this->isInSubFolder($folder, $file) === true) {
                $folderItem = new mtmdFolder($relFolderPath, $folder, '', $this->getFolderRoot());
                array_push($this->fileList, $folderItem);
                array_push($processedFolders, $relFolderPath);
                continue;
            }

            $image = new mtmdImage($file);

            // Remove invalid/unsupported images here.
            if (!in_array($image->getType(), $this->supportedTypes)) {
                mtmdUtils::output(
                    sprintf(
                        '"%s": Image type "%s" is not supported. Skipped.',
                        basename($image->getFileName()),
                        image_type_to_mime_type($image->getType())
                    )
                );
                continue;
            }


            array_push($this->fileList, $image);
        }

        return $this->fileList;

    }


    public function extractRelativeFolderPath($folder, $fileName)
    {
        return dirname(str_replace($folder.DIRECTORY_SEPARATOR, '', $fileName));

    }


    public function isInSubFolder($folder, $fileName)
    {
        $dirName = $this->extractRelativeFolderPath($folder, $fileName);
        if ($dirName == '.') {
            return false;
        }
        return true;

    }


    public function getList()
    {
        return $this->fileList;
    }


    public function setThumbWidth($width)
    {
        $this->thumbWidth = $width;
        return $this;
    }


    public function setThumbHeight($height)
    {
        $this->thumbHeight = $height;
        return $this;
    }


    public function getThumbWidth()
    {
        return $this->thumbWidth;
    }


    public function getThumbHeight()
    {
        return $this->thumbHeight;
    }


    public function getFolderRoot()
    {
        return $this->folderRoot;
    }


    public function getFolderSource()
    {
        return $this->folderSrc;
    }


    public function getFolderCache()
    {
        return $this->folderCache;
    }


    public function getSourceFullPath()
    {
        return $this->getFolderRoot().DIRECTORY_SEPARATOR.$this->getFolderSource();
    }


    /**
     * Returns the path to cached file.
     *
     * @param string $fileName
     *
     * @return string
     */
    public function getCachedFilePath($fileName)
    {
        $targetPath = dirname($fileName).DIRECTORY_SEPARATOR.basename($fileName);
        $targetPath = str_replace($this->getFolderRoot(), $this->getFolderCache(), $targetPath);
        return $targetPath;
    }


    /**
     * Resizes a list of images.
     *
     * @param mtmdImage[] $images Array of mtmdImage instances.
     *
     * @return void
     */
    public function resize(array $images)
    {
        foreach ($images as $image) {
            if (!$image instanceof mtmdImage) {
                continue;
            }

            mtmdUtils::output(
                sprintf(
                    '"%s": Resizing to %dx%d (was %dx%d)...',
                    $image->getFileName(),
                    $this->getThumbWidth(),
                    $this->getThumbHeight(),
                    $image->getWidth(),
                    $image->getHeight()
                )
            );

            $targetPath = $this->getCachedFilePath(dirname($image->getFileName()));

            // Prepare target dirs.
            mtmdUtils::mkDir($targetPath, 0755, true);
            // Resize image.
            $image->resizeImage($targetPath, $this->thumbWidth, $this->thumbHeight);

        }

    }


}
