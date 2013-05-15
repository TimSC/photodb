<?php
require_once('photodb.php');
require_once('roidb.php');

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');
CheckPhotoSchema($photoDb);
CheckRoiSchema($photoDb);

$sql = "SELECT * FROM rois INNER JOIN photos ON rois.photoId = photos.id;";
$sth = $photoDb->prepare($sql);
if($sth===false) {$err= $photoDb->errorInfo();throw new Exception($sql.",".$err[2]);}
$ret = $sth->execute();
if($ret===false) {$err= $photoDb->errorInfo();throw new Exception($sql.",".$err[2]);}


$out = array();
while($row = $sth->fetch(PDO::FETCH_ASSOC))
{
	$roiWidth = $row['x2'] - $row['x1'];
	$roiHeight = $row['y2'] - $row['y1'];
	$row['roiWidth'] = $roiWidth;
	$row['roiHeight'] = $roiHeight;

	array_push($out, $row);
}
echo json_encode($out);
?>
