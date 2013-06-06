<?php
session_start();
require_once('photodb.php');
require_once('roidb.php');
require_once('modelsdb.php');
require_once('attribsdb.php');

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');

CheckPhotoSchema($photoDb);
CheckRoiSchema($photoDb);
CheckModelsSchema($photoDb);
CheckAttribsSchema($photoDb);

$roiId = (int)$_GET['roiId'];

if(isset($_POST['form-action']) && $_POST['form-action'] == "Update Attributes")
{
	if(strlen($_POST['key'])>0 && strlen($_POST['value'])>0)
		AddAttribToRoi($photoDb, $roiId, $_POST['annot'], $_POST['key'], $_POST['value']);
	if(strlen($_POST['annot'])>0)
		$_SESSION['annot'] = $_POST['annot'];
}

if(isset($_POST['form-action']) && $_POST['form-action'] == "Delete Selected")
{
	foreach($_POST as $k => $v)
	{
		if(strcmp(substr($k,0,6), "attrib")==0)
		{
			$attribNum = (int)substr($k,6);
			RemoveAttrib($photoDb, $attribNum);
		}
	}

}

$bboxesInfo = GetRoiFromStore($photoDb, $roiId);
if($bboxesInfo===Null) die("Error getting ROI data");
$photoData = GetPhotoData($photoDb, (int)$bboxesInfo['photoId']);
if($photoData===Null) die("Error getting photo data");
$fina = $photoData['fina'];
$annotData = GetAttribsForRoi($photoDb, $roiId);
if($annotData===Null) die("Error getting annot data");

$nativeWidth = $bboxesInfo['pos'][2]-$bboxesInfo['pos'][0];
$nativeHeight = $bboxesInfo['pos'][3]-$bboxesInfo['pos'][1];

if(isset($_SESSION['annot']))
	$annot = $_SESSION['annot'];
else
	$annot = "";
?>

<html>
<head>
</head>
<body>
<h1>Attibutes of ROI</h1>
<?php //print_r($model); ?>
<?php //print_r($bboxesInfo); ?>
<?php //print_r($photoData); ?>

<img src="roiimg.php?roiId=<?php echo $roiId;?>&amp;target-size=500"/>

<form name="upload" method="post" action="attribs.php?roiId=<?php echo (int)$_GET['roiId'];?>">

<table border="2">
<tr><td>Annot</td><td>Key</td><td>Value</td></tr>
<?php 
foreach($annotData as $k => $v)
{
?>
<tr><td><input type="checkbox" name="attrib<?php echo $v['id'];?>" value="delete"/><?php echo $v['annot'];?></td><td><?php echo $v['key'];?></td><td><?php echo $v['value'];?></td></tr>
<?php
}
?>
</table>
<input type="submit" name="form-action" value="Delete Selected">
</form>

<form name="upload2" method="post" action="attribs.php?roiId=<?php echo (int)$_GET['roiId'];?>">

<h3>Add New Entry</h3>

Annotator <input type="text" name="annot" value="<?php echo $annot;?>"/><br/>
Key <input type="text" name="key" /><br/>
Value <input type="text" name="value" /><br/>

<input type="submit" name="form-action" value="Update Attributes">
</form>

<a href="photo.php?id=<?php echo $bboxesInfo['photoId'];?>">Photo Details</a> 
<a href="roi.php?id=<?php echo $bboxesInfo['photoId'];?>">Edit ROIs</a> 
<a href="managerois.php?id=<?php echo $bboxesInfo['photoId'];?>">Manage ROIs</a> 
<a href="list.php">List Photos</a>

</body>
</html>
