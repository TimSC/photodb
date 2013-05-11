<?php

require_once('photodb.php');

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');

if(isset($_GET['id']))
	$viewPhotoId = $_GET['id'];
else
	$viewPhotoId = NULL;

if(PhotoInStore($photoDb, $viewPhotoId)===0)
{
	StorePhotoUrl($photoData['url'], $viewPhotoId, $photoDb);
}
$fina = PhotoInStore($photoDb, $viewPhotoId);
$photoData = GetPhotoData($photoDb, $viewPhotoId);
?>

<html>
<head>
<script language="javascript" type="text/javascript">

var bboxes = new Array();
bboxes[0] = new Array(100, 100, 200, 200);

var img, ctx;
var pressed = 0, selectedBbox = -1;

window.onload = function() {	
	var canvas = document.getElementById('canv');
	ctx = canvas.getContext('2d');

	img = new Image();   // Create new img element
	img.onload = function(){
		ctx.drawImage(img,0,0);
		DrawOverlay(ctx)
	};
	img.src = '<?php echo $fina;?>'; // Set source path

	canvas.addEventListener("mousedown", MouseDown, false);
	canvas.addEventListener("mousemove", MouseMove, false);
	canvas.addEventListener("mouseup", MouseUp, false);

	//DrawOverlay(ctx)
	//var numRoisEl = document.getElementById('num-rois');
	//numRoisEl.addEventListener("onchange", NumRoisChanged, false);


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
		distA = Math.sqrt(Math.pow(bboxes[i][0] - mouseX, 2) + Math.pow(bboxes[i][1] - mouseY, 2));
		if(closestInd == -1 || distA < closestDist)
		{
			closestInd = i;
			closestDist = distA;
		}
		distB = Math.sqrt(Math.pow(bboxes[i][2] - mouseX, 2) + Math.pow(bboxes[i][3] - mouseY, 2));
		if(closestInd == -1 || distB < closestDist)
		{
			closestInd = i;
			closestDist = distB;
		}
	}

	//Update bounding box location
	selectedBbox = closestInd;
	bboxes[selectedBbox][0] = mouseX;
	bboxes[selectedBbox][1] = mouseY;
	ctx.drawImage(img,0,0);
	DrawOverlay(ctx)
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
	bboxes[selectedBbox][2] = mouseX;
	bboxes[selectedBbox][3] = mouseY;
	ctx.drawImage(img,0,0);
	DrawOverlay(ctx)
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
		ctx.moveTo(bboxes[i][0],bboxes[i][1]);
		ctx.lineTo(bboxes[i][2],bboxes[i][1]);
		ctx.lineTo(bboxes[i][2],bboxes[i][3]);
		ctx.lineTo(bboxes[i][0],bboxes[i][3]);
		ctx.lineTo(bboxes[i][0],bboxes[i][1]);
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

	ctx.drawImage(img,0,0);
	DrawOverlay(ctx)
}

</script>
</head>
<body>
<?php
if($fina!==0)
{
?>

<h1>Edit ROI</h1>

<canvas id="canv" style="position: relative;" width="<?php echo $photoData['width'];?>" height="<?php echo $photoData['height'];?>">Canvas not supported</canvas><br/>

<p>Number of ROIs <input id="num-rois" type="text" name="num-rois" value="1"><a href="#" onclick="NumRoisChanged()">Set</a></p>

<form>
<input type="submit" name="form-action" value="Update">
</form>

<?php
}
?>

<a href="list.php">List</a>
</body>
</html>


