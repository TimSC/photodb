<?php
require_once('photodb.php');
require_once('roidb.php');

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');
CheckPhotoSchema($photoDb);
CheckRoiSchema($photoDb);

$sql = "SELECT models.id as modelId, * FROM models INNER JOIN rois ON models.roiId = rois.id INNER JOIN photos ON rois.photoId = photos.id;";
$sth = $photoDb->prepare($sql);
if($sth===false) {$err= $photoDb->errorInfo();throw new Exception($sql.",".$err[2]);}
$ret = $sth->execute();
if($ret===false) {$err= $photoDb->errorInfo();throw new Exception($sql.",".$err[2]);}


$out = array();
while($row = $sth->fetch(PDO::FETCH_ASSOC))
{
	$row['x1'] = (float)$row['x1'];
	$row['y1'] = (float)$row['y1'];
	$row['x2'] = (float)$row['x2'];
	$row['y2'] = (float)$row['y2'];

	$row['width'] = (float)$row['width'];
	$row['height'] = (float)$row['height'];

	$row['photoId'] = (int)$row['photoId'];
	$row['roiId'] = (int)$row['roiId'];
	$row['modelId'] = (int)$row['modelId'];
	$row['roiNum'] = (int)$row['roiNum'];
	unset($row['id']);

	$roiWidth = $row['x2'] - $row['x1'];
	$roiHeight = $row['y2'] - $row['y1'];
	$row['model'] = json_decode($row['model']);
	$row['roiWidth'] = $roiWidth;
	$row['roiHeight'] = $roiHeight;

	array_push($out, $row);
}
echo json_encode($out);
?>
