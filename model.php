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

$model = GetModelForStore($photoDb, (int)$_GET['modelId']);
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


var bboxes = new Array();
bboxes[0] = new Array(100, 100, 200, 200);

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
	//numRoisEl.value = bboxes.length;
	//numRoisEl.addEventListener("onchange", NumRoisChanged, false);

	//var bboxFormEl = document.getElementById('form-bbox');
	//bboxFormEl.value = JSON.stringify(bboxes)

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
	for (i=0;i<bboxes.length;i++)
	{
		distA = Math.sqrt(Math.pow(bboxes[i][0]*displayratio - mouseX, 2) + Math.pow(bboxes[i][1]*displayratio - mouseY, 2));
		if(closestInd == -1 || distA < closestDist)
		{
			closestInd = i;
			closestDist = distA;
		}
		distB = Math.sqrt(Math.pow(bboxes[i][2]*displayratio - mouseX, 2) + Math.pow(bboxes[i][3]*displayratio - mouseY, 2));
		if(closestInd == -1 || distB < closestDist)
		{
			closestInd = i;
			closestDist = distB;
		}
		distC = Math.sqrt(Math.pow(bboxes[i][0]*displayratio - mouseX, 2) + Math.pow(bboxes[i][3]*displayratio - mouseY, 2));
		if(closestInd == -1 || distC < closestDist)
		{
			closestInd = i;
			closestDist = distC;
		}
		distD = Math.sqrt(Math.pow(bboxes[i][2]*displayratio - mouseX, 2) + Math.pow(bboxes[i][1]*displayratio - mouseY, 2));
		if(closestInd == -1 || distD < closestDist)
		{
			closestInd = i;
			closestDist = distD;
		}
	}

	//Update bounding box location
	selectedBbox = closestInd;
	bboxes[selectedBbox][0] = mouseX/displayratio;
	bboxes[selectedBbox][1] = mouseY/displayratio;
	ctx.drawImage(img, 0, 0, displaywidth, displayheight);
	DrawOverlay(ctx)
	var bboxFormEl = document.getElementById('form-bbox');
	bboxFormEl.value = JSON.stringify(bboxes)
	//window.alert(px)
	//window.alert("MouseDown");
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
	bboxes[selectedBbox][2] = mouseX/displayratio;
	bboxes[selectedBbox][3] = mouseY/displayratio;
	ctx.drawImage(img, 0, 0, displaywidth, displayheight);
	DrawOverlay(ctx)
	var bboxFormEl = document.getElementById('form-bbox');
	bboxFormEl.value = JSON.stringify(bboxes)
}

function MouseUp(e)
{
	pressed = 0;
	selectedBbox = -1;
}

function DrawOverlay(ctx)
{
	for (i=0;i<bboxes.length;i++)
	{
		ctx.beginPath();
		ctx.moveTo(bboxes[i][0]*displayratio,bboxes[i][1]*displayratio);
		ctx.lineTo(bboxes[i][2]*displayratio,bboxes[i][1]*displayratio);
		ctx.lineTo(bboxes[i][2]*displayratio,bboxes[i][3]*displayratio);
		ctx.lineTo(bboxes[i][0]*displayratio,bboxes[i][3]*displayratio);
		ctx.lineTo(bboxes[i][0]*displayratio,bboxes[i][1]*displayratio);
		ctx.stroke();
	}
}

function NumRoisChanged()
{
	var width = <?php echo $photoData['width'];?>;
	var height = <?php echo $photoData['height'];?>;
	var numRoisEl = document.getElementById('num-rois');
	numRois = Math.round(numRoisEl.value);
	while(bboxes.length > numRois)
		bboxes.pop();
	while(bboxes.length < numRois)
		bboxes.push(new Array(200,200,300,300));

	ctx.drawImage(img, 0, 0, displaywidth, displayheight);
	DrawOverlay(ctx)
	var bboxFormEl = document.getElementById('form-bbox');
	bboxFormEl.value = JSON.stringify(bboxes)
}

</script>
</head>
<body>
<h1>Model</h1>
<?php //print_r($model); ?>
<?php print_r($bboxesInfo); ?>
<?php //print_r($photoData); ?>

<canvas id="canv" style="position: relative;" width="<?php echo $displaywidth;?>" height="<?php echo $displayheight;?>">Canvas not supported</canvas><br/>
<!--<img src="roiimg.php?roiId=<?php echo (int)$model['roiId'];?>&target-size=600" alt="ROI"/>-->


</body>
</html>
