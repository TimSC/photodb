<?php

require_once('photodb.php');
require_once('roidb.php');
require_once('modelsdb.php');

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');

CheckPhotoSchema($photoDb);
CheckRoiSchema($photoDb);
CheckModelsSchema($photoDb);

if(!isset($_GET['roiId'])) die("Roi Id not specified");

$bbox = GetRoiFromStore($photoDb, (int)$_GET['roiId']);
if($bbox===Null) die("Error getting ROI data");
$photoData = GetPhotoData($photoDb, $bbox['photoId']);
if($photoData===Null) die("Error getting photo data");
$fina = $photoData['fina'];

//print_r($bbox);
$img = imagecreatefromjpeg($fina);
$pos = $bbox['pos'];
$outWidth = $pos[2]-$pos[0];
$outHeight = $pos[3]-$pos[1];

if(isset($_GET["target-size"]))
{
	$avDim = 0.5 * ($outWidth + $outHeight);
	$outWidth = $outWidth * (int)($_GET["target-size"]) / $avDim;
	$outHeight = $outHeight * (int)($_GET["target-size"]) / $avDim;
}

$my_img = imagecreatetruecolor($outWidth, $outHeight);
imagecopyresampled($my_img, $img, 0, 0, $pos[0], $pos[1], $outWidth, $outHeight, $pos[2]-$pos[0], $pos[3]-$pos[1]);


if(1)
{
	header( "Content-type: image/png" );
	imagepng( $my_img );
	imagedestroy( $my_img );
}
?>
