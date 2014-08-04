<?php
class mtmdImageView extends mtmdView {

    protected $contents = array(
        'images' => array()
    );

    protected $count = 0;

    public function addImage(mtmdImage $image)
    {
        $this->count++;
        array_push($this->contents['images'], $this->renderImage($image));

    }


    public function renderImages()
    {
        return $this->renderTable(4, $this->contents['images']);

    }


    public function renderImage(mtmdImage $image)
    {
        $tpl = file_get_contents('lib/mtmd/templates/thumb.html.tpl');
        return sprintf(
            $tpl,
            $this->count,
            $image->getFileName(),
            $image->getWidth(),
            $image->getHeight(),
            $image->getThumbFileName(),
            basename($image->getFileName()),
            $image->getFileName(),
            basename($image->getFileName())
        );

    }


}