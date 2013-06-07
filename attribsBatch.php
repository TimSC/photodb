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

$attrib = "age";
//$attrib = "gender";

if(isset($_SESSION['annot']))
	$annot = $_SESSION['annot'];
else
	$annot = "TimSC";

if(isset($_POST['form-action']) && $_POST['form-action'] == "Set Attributes")
{
	//AddAttribToRoi($photoDb, $roiId, $_POST['annot'], $_POST['key'], $_POST['value']);
	//print_r($_POST);
	foreach($_POST as $k => $value)
	{
		if(strlen($value)==0) continue;
		if(substr($k,0,3)!="roi") continue;
		$roiId = (int)substr($k,3);

		AddAttribToRoi($photoDb, $roiId, $annot, $attrib, $value);
	}
}

$rois = GetAllRois($photoDb);

?>

<html>
<head>
</head>
<body>
<h1>Attibutes: <?php echo $attrib; ?></h1>

<p>Annotator ID: <?php echo $annot; ?></p>

<form name="upload" method="post" action="attribsBatch.php?roiId=<?php echo (int)$_GET['roiId'];?>">
<table border="2">
<?php
foreach($rois as $k => $roi)
{
	$roiAttribs = GetAttribsForRoi($photoDb, $roi['id']);

	//Check if attribute is already done
	$done = 0;
	foreach($roiAttribs as $attribk => $attribv)
	{
		if($attribv['key'] == $attrib and $annot == $attribv['annot'])
		{
			$done = 1;
		}
	}
	if ($done) continue;

	?>

<tr><td>
<?php //print_r($roi);?>
<img src="roiimg.php?roiId=<?php echo $roi['id'];?>&target-size=200" alt="ROI thumbnail"/>
<input type="text" name="roi<?php echo $roi['id'];?>" />
</td></tr>

	<?php
}
?>
</table>
<input type="submit" name="form-action" value="Set Attributes">
</form>

<a href="list.php">List Photos</a>

</body>
</html>

