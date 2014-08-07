<?php
require 'lib/mtmd/classes/utils.php';
require 'lib/mtmd/models/image.php';
require 'lib/mtmd/models/folder.php';
require 'lib/mtmd/classes/imageApi.php';
require 'lib/mtmd/classes/viewApi.php';
require 'lib/mtmd/classes/imageView.php';

$srcFolder = '';
$srcDefault = './images';
$cacheFolder = './cache';

$options = getopt('d:');
if (isset($_GET['d']) || isset($options['d'])) {
    if (!empty($options['d'])) {
        $srcFolder = $srcDefault.DIRECTORY_SEPARATOR.$options['d'];
    }
    if (!empty($_GET['d'])) {
        $srcFolder = $srcDefault.DIRECTORY_SEPARATOR.$_GET['d'];
    }
}

if (empty($srcFolder)) {
    $srcFolder = $srcDefault;
}

$srcFolder = rtrim($srcFolder, DIRECTORY_SEPARATOR);

$imageApi = new mtmdImageApi($srcFolder, $cacheFolder, $srcDefault);
$images = $imageApi->getNewList();
$imageApi
    ->setThumbWidth(320)
    ->setThumbHeight(260)
    ->resize();

$tpl = new mtmdImageView();

if ($srcFolder != $srcDefault) {
    $root = new mtmdFolder('', $srcDefault, 'Overview', $srcDefault);
    $tpl->addItem($root);
}

foreach ($imageApi->getList() as $item) {
    $tpl->addItem($item);
}
$imageTable = $tpl->renderOverview();
$showFolder = str_replace($srcDefault.DIRECTORY_SEPARATOR, '', $srcFolder);
if ($showFolder == $srcDefault) {
    $showFolder = 'Overview';
}
$headline = 'Showing "'.$showFolder.'" ('.count($images).' items)';

$breadcrumb = $tpl->renderBreadCrumb(
    substr($imageApi->getFolderSource(), 2),
    'Overview'
);
echo $tpl->render('<title>'.$headline.'</title>', '<h1>'.$headline.'</h1>'."\n".$breadcrumb."\n".$imageTable);
