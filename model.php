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

if(!isset($_GET['modelId'])) die("Model Id not specified");

if(isset($_POST['form-action']) && $_POST['form-action'] == "Update Model")
{
	UpdateModelInStore($photoDb, (int)$_GET['modelId'], $_POST['model']);
}

$model = GetModelFromStore($photoDb, (int)$_GET['modelId']);
if($model===Null) die("Error getting model data");
$bboxesInfo = GetRoiFromStore($photoDb, (int)$model['roiId']);
if($bboxesInfo===Null) die("Error getting ROI data");
$photoData = GetPhotoData($photoDb, (int)$bboxesInfo['photoId']);
if($photoData===Null) die("Error getting photo data");
$fina = $photoData['fina'];

$nativeWidth = $bboxesInfo['pos'][2]-$bboxesInfo['pos'][0];
$nativeHeight = $bboxesInfo['pos'][3]-$bboxesInfo['pos'][1];

?>

<html>
<head>
<script language="javascript" type="text/javascript">

<?php
if($model['model'] !== Null)
{
?>
var pts = JSON.parse("<?php echo $model['model'];?>");
<?php
}
else
{
?>
var pts = new Array();
pts[0] = new Array(10, 10);
pts[1] = new Array(20, 20);
pts[2] = new Array(30, 30);
pts[3] = new Array(40, 40);
pts[4] = new Array(50, 50);
<?php
}
?>

var img, ctx;
var pressed = 0, selectedBbox = -1;
var displaywidth = 500, displayheight = 500;
var nativeWidth = <?php echo $nativeWidth;?>, nativeHeight = <?php echo $nativeHeight;?>;
var displayratio = <?php echo 500. / $nativeWidth;?>;

window.onload = function() {	
	var canvas = document.getElementById('canv');
	ctx = canvas.getContext('2d');

	img = new Image();   // Create new img element
	img.onload = function(){
		displaywidth = img.width;
		displayheight = img.height;
		displayratio = displaywidth / nativeWidth;
		ctx.canvas.height = img.height;
		ctx.canvas.width = img.width;
		ctx.drawImage(img, 0, 0, displaywidth, displayheight);
		DrawOverlay(ctx);
	};
	img.src = 'roiimg.php?roiId=<?php echo (int)$model['roiId'];?>&target-size=600'; // Set source path

	canvas.addEventListener("mousedown", MouseDown, false);
	canvas.addEventListener("mousemove", MouseMove, false);
	canvas.addEventListener("mouseup", MouseUp, false);

	//DrawOverlay(ctx)
	//var numRoisEl = document.getElementById('num-rois');
	//numRoisEl.value = pts.length;
	//numRoisEl.addEventListener("onchange", NumRoisChanged, false);

	var ptsFormEl = document.getElementById('form-model');
	ptsFormEl.value = JSON.stringify(pts)

}

function MouseDown(e)
{
	pressed = 1;

    if(e.offsetX) {
        mouseX = e.offsetX;
        mouseY = e.offsetY;
    }
    else if(e.layerX) {
        mouseX = e.layerX;
        mouseY = e.layerY;
    }

	//Determine closest bounding box
	var closestInd = -1;
	var closestDist = -1.;
	for (i=0;i<pts.length;i++)
	{
		dist = Math.sqrt(Math.pow(pts[i][0]*displayratio - mouseX, 2) + Math.pow(pts[i][1]*displayratio - mouseY, 2));
		if(closestInd == -1 || dist < closestDist)
		{
			closestInd = i;
			closestDist = dist;
		}
	}

	//Update bounding box location
	selectedBbox = closestInd;
	pts[selectedBbox][0] = mouseX/displayratio;
	pts[selectedBbox][1] = mouseY/displayratio;
	ctx.drawImage(img, 0, 0, displaywidth, displayheight);
	DrawOverlay(ctx)
	var ptsFormEl = document.getElementById('form-model');
	ptsFormEl.value = JSON.stringify(pts)
	//window.alert(px)
	//window.alert("MouseDown");

	var selectedPtEl = document.getElementById('point-selected');
	selectedPtEl.value = closestInd + 1;
}

function MouseMove(e)
{
	if(!pressed)
		return;
	//window.alert("MouseMove");

    if(e.offsetX) {
        mouseX = e.offsetX;
        mouseY = e.offsetY;
    }
    else if(e.layerX) {
        mouseX = e.layerX;
        mouseY = e.layerY;
    }
	pts[selectedBbox][0] = mouseX/displayratio;
	pts[selectedBbox][1] = mouseY/displayratio;
	ctx.drawImage(img, 0, 0, displaywidth, displayheight);
	DrawOverlay(ctx)
	var ptsFormEl = document.getElementById('form-model');
	ptsFormEl.value = JSON.stringify(pts)
}

function MouseUp(e)
{
	pressed = 0;
	selectedBbox = -1;
}

function DrawOverlay(ctx)
{
	for (i=0;i<pts.length;i++)
	{
		ctx.beginPath();
		ctx.moveTo(pts[i][0]*displayratio-10,pts[i][1]*displayratio);
		ctx.lineTo(pts[i][0]*displayratio+10,pts[i][1]*displayratio);
		ctx.moveTo(pts[i][0]*displayratio,pts[i][1]*displayratio-10);
		ctx.lineTo(pts[i][0]*displayratio,pts[i][1]*displayratio+10);
		ctx.strokeStyle = '#0000ff';
		ctx.stroke();
	}
}

</script>
</head>
<body>
<h1>Model</h1>
<?php //print_r($model); ?>
<?php //print_r($bboxesInfo); ?>
<?php //print_r($photoData); ?>

<canvas id="canv" style="position: relative;" width="<?php echo $displaywidth;?>" height="<?php echo $displayheight;?>">Canvas not supported</canvas><br/>

<p>Point Selected <input type="text" id="point-selected"/><br/>
Model name: <?php echo $model['modelName'];?>
</p>

<form name="upload" method="post" action="model.php?modelId=<?php echo (int)$_GET['modelId'];?>">
<input id="form-model" name="model" type="hidden" value="[]">
<input type="submit" name="form-action" value="Update Model">
</form>

<a href="photo.php?id=<?php echo $bboxesInfo['photoId'];?>">Photo Details</a> 
<a href="roi.php?id=<?php echo $bboxesInfo['photoId'];?>">Edit ROIs</a> 
<a href="managerois.php?id=<?php echo $bboxesInfo['photoId'];?>">Manage ROIs</a> 
<a href="list.php">List Photos</a>

</body>
</html>
