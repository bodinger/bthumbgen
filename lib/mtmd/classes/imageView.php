<?php
class mtmdImageView extends mtmdView {

    protected $contents = array(
        'images' => array()
    );

    protected $count = 0;

    public function addItem($item)
    {
        $this->count++;
        array_push($this->contents['images'], $this->renderItem($item));

    }


    public function renderOverview()
    {
        return $this->renderTable(4, $this->contents['images']);

    }


    public function renderItem($item)
    {
        if ($item instanceof mtmdImage) {
            return $this->renderThumb($item);
        }

        if ($item instanceof mtmdFolder) {
            return $this->renderFolder($item);
        }

    }


    public function renderBreadcrumbItem($link, $title, $active)
    {
        $tpl = file_get_contents('lib/mtmd/templates/breadcrumbitem.html.tpl');
        return sprintf(
            $tpl,
            rtrim($link, DIRECTORY_SEPARATOR),
            ($active === true) ? 'active' : '',
            rtrim($title, DIRECTORY_SEPARATOR)
        );

    }


    public function renderBreadCrumb($path, $rootTitle = 'root')
    {
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $breadcrumb = '';
        $path = '';
        $breadcrumb .= $this->renderBreadcrumbItem('', $rootTitle, false);
        $amount = count($parts);
        foreach ($parts as $k => $link) {
            if ($k < 1) {
                continue;
            }
            $active = false;
            if ($k == $amount-1) {
                $active = true;
            }
            $link = rtrim($link, DIRECTORY_SEPARATOR);
            $path .= $link.DIRECTORY_SEPARATOR;
            $breadcrumb .= $this->renderBreadcrumbItem($path, $link, $active).'-&gt;';
        }
        $tpl = file_get_contents('lib/mtmd/templates/breadcrumb.html.tpl');
        $breadcrumb = sprintf(
            $tpl,
            $breadcrumb
        );
        return $breadcrumb;

    }


    public function renderFolder(mtmdFolder $folder)
    {
        $tpl = file_get_contents('lib/mtmd/templates/folder.html.tpl');
        return sprintf(
            $tpl,
            $folder->getPathFromRoot(),
            $folder->getTitle(),
            $folder->getFileCount(),
            $folder->getTitle()
        );

    }


    /**
     * Renders thumb template.
     *
     * @param mtmdImage $image
     * @return string
     */
    public function renderThumb(mtmdImage $image)
    {
        $tpl = file_get_contents('lib/mtmd/templates/thumb.html.tpl');
        return sprintf(
            $tpl,
            $this->count,
            $image->getFileName(),
            $image->getThumbWidth(),
            $image->getThumbHeight(),
            $image->getThumbFileName(),
            basename($image->getFileName()),
            $image->getFileName(),
            mtmdUtils::formatBytes($image->getSize()),
            basename($image->getFileName())
        );

    }


}
