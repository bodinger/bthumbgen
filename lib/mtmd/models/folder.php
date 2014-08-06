<?php
class mtmdFolder {

    protected $path = '';
    protected $title = '';
    protected $fullPath = '';
    protected $pathFromRoot = '';
    protected $folderContainer = '';
    protected $folderRoot = '';
    protected $fileCount = 0;

    public function __construct($path, $containerFolder = '', $title = '', $rootFolder = '')
    {
        $this->path            = $path;
        $this->folderContainer = $containerFolder;
        $this->folderRoot      = $rootFolder;
        $this->fullPath        = $this->getFolderContainer().DIRECTORY_SEPARATOR.$this->getPath();
        $this->pathFromRoot    = $this->getRelativePathFromRoot();

        if (!file_exists($this->fullPath)) {
            throw new Exception(
                sprintf(
                    '%s: Does not exist!',
                    $this->fullPath
                )
            );
        }

        if (!is_dir($this->fullPath)) {
            throw new Exception(
                sprintf(
                    '%s: Is not a folder!',
                    $this->fullPath
                )
            );
        }

        $this->fileCount = count(mtmdUtils::listDir($this->fullPath, false));

        $this->title = $this->getPath();
        if (!empty($title)) {
            $this->title = $title;
        }

    }


    private function cleanPath($string)
    {
        return rtrim($string, '.'.DIRECTORY_SEPARATOR);
    }


    public function getTitle()
    {
        return $this->title;
    }


    public function getPath()
    {
        return $this->cleanPath($this->path);
    }


    public function getFolderContainer()
    {
        return $this->cleanPath($this->folderContainer);
    }


    public function getFullPath()
    {
        return $this->cleanPath($this->fullPath);
    }


    private function getRelativePathFromRoot()
    {
        return $this->cleanPath(trim(str_replace($this->folderRoot, '', $this->getFullPath()), DIRECTORY_SEPARATOR));
    }


    public function getPathFromRoot()
    {
        return $this->cleanPath($this->pathFromRoot);
    }


    public function getFileCount()
    {
        return $this->fileCount;
    }


}