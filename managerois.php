<?php

require_once('photodb.php');
require_once('roidb.php');

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');

$exists = SqliteCheckTableExists($photoDb,"rois");
if(!$exists)
	CreateRoiTable($photoDb);

if(isset($_GET['id']))
	$viewPhotoId = $_GET['id'];
else
	$viewPhotoId = NULL;
if($viewPhotoId===NULL) die("Photo ID needs to be specified");

if(PhotoInStore($photoDb, $viewPhotoId)===0)
{
	StorePhotoUrl($photoData['url'], $viewPhotoId, $photoDb);
}
$fina = PhotoInStore($photoDb, $viewPhotoId);
$photoData = GetPhotoData($photoDb, $viewPhotoId);
$bboxes = GetRois($photoDb, $viewPhotoId);
$bboxesJson = json_encode($bboxes);

//Calculate image sizing
$maxDimension = $photoData['width'];
if($photoData['height']>$maxDimension)
	$maxDimension = $photoData['height'];
$displayRatio = 1.;
if($maxDimension > 800)
	$displayRatio = 800. / $maxDimension;
$displaywidth = $photoData['width'] * $displayRatio;
$displayheight = $photoData['height'] * $displayRatio;

?>

<html>

<body>

<h1>Manage ROIs</h1>

<table border="2">
<?php for($i=0;$i<count($bboxes);$i++)
{
$box = $bboxes[$i];
?>
<tr>
<td><?php echo $i+1;?></td>



</tr>
<?php
}
?>

</table>

<a href="photo.php?id=<?php echo $viewPhotoId;?>">Photo Details</a>
<a href="roi.php?id=<?php echo $viewPhotoId;?>">Edit ROIs</a>
<a href="list.php">List Photos</a>
</body>
</html>


