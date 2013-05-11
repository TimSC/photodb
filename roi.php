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
var px = 100, py = 100;
var px2 = 200, py2 = 200;
var img, ctx;
var pressed = 0;

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

	px = mouseX;
	py = mouseY;
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
	px2 = mouseX;
	py2 = mouseY;
	ctx.drawImage(img,0,0);
	DrawOverlay(ctx)
}

function MouseUp(e)
{
	pressed = 0;
}

function DrawOverlay(ctx)
{
    ctx.beginPath();
    ctx.moveTo(px,py);
    ctx.lineTo(px2,py);
    ctx.lineTo(px2,py2);
    ctx.lineTo(px,py2);
    ctx.lineTo(px,py);
    ctx.stroke();
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

<?php
}
?>

<a href="list.php">List</a>
</body>
</html>


