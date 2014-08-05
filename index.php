<?php
require 'lib/mtmd/classes/utils.php';
require 'lib/mtmd/models/image.php';
require 'lib/mtmd/classes/imageApi.php';
require 'lib/mtmd/classes/viewApi.php';
require 'lib/mtmd/classes/imageView.php';

$srcFolder = './images';
$cacheFolder = './cache';

$imageApi = new mtmdImageApi($srcFolder, $cacheFolder);
$images = $imageApi->getNewList();
$imageApi
    ->setThumbWidth(200)
    ->setThumbHeight(150)
    ->resize($images);

$tpl = new mtmdImageView();
foreach ($imageApi->getList() as $image) {
    $tpl->addImage($image);
}
$imageTable = $tpl->renderImages();
echo $tpl->render('<title>Images in '.$srcFolder.': '.count($images).'</title>', $imageTable);
