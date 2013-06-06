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
$bboxesInfo = GetRoisForPhoto($photoDb, $viewPhotoId);

//Calculate image sizing
$maxDimension = $photoData['width'];
if($photoData['height']>$maxDimension)
	$maxDimension = $photoData['height'];
$displayRatio = 1.;
if($maxDimension > 800)
	$displayRatio = 800. / $maxDimension;
$displaywidth = $photoData['width'] * $displayRatio;
$displayheight = $photoData['height'] * $displayRatio;

if(isset($_POST['form-action']) && $_POST['form-action'] == "Add")
{
	//print_r($_POST);
	foreach($_POST as $k => $v)
	{
		if(substr($k, 0, 4)!= "roi-") continue;
		$roiId = (int)substr($k, 4);
		AddModelToRoi($photoDb, $roiId, $_POST['modelName']);
	}
}

if(isset($_POST['form-action']) && $_POST['form-action'] == "Delete Selected Models")
{
	foreach($_POST as $k => $v)
	{
		if(substr($k, 0, 6)!= "model-") continue;
		$modelId = (int)substr($k, 6);
		RemoveModel($photoDb, $modelId);
	}

}

?>

<html>

<body>

<h1>Manage ROIs</h1>

<form method="post" action="managerois.php?id=<?php echo $viewPhotoId;?>">
<table border="2">
<?php for($i=0;$i<count($bboxesInfo);$i++)
{
$box = $bboxesInfo[$i];
$models = GetModelForRoi($photoDb, $box['id']);
?>
<tr>
<td><input type="checkbox" name="roi-<?php echo $box['id'];?>" value="c"> <?php echo $box['roiNum']+1;?></td>
<td><img src="roiimg.php?roiId=<?php echo $box['id'];?>&target-size=100" alt="ROI thumbnail"/></td>
<td>
<?php 
foreach($models as $model)
{
	echo "<input type=\"checkbox\" name=\"model-".$model['id']."\" value=\"c\"><a href=\"model.php?modelId=".$model['id']."\">".$model['modelName']."</a><br/>\n";
}
?>
<a href="attribs.php?roiId=<?php echo $box['id'];?>">Attribs</a>
</td>
</tr>
<?php
}
?>
</table>
Add Model to ROI <input type="text" name="modelName" value="<?php echo $photoData['comment']; ?>"> <input type="submit" name="form-action" value="Add"><br/>
<input type="submit" name="form-action" value="Delete Selected Models">
</form>

<a href="photo.php?id=<?php echo $viewPhotoId;?>">Photo Details</a>
<a href="roi.php?id=<?php echo $viewPhotoId;?>">Edit ROIs</a>
<a href="list.php">List Photos</a>
</body>
</html>


