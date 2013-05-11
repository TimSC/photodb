<html>
<head>
<script language="javascript" type="text/javascript">

window.onload = function() {
	document.images[0].addEventListener("mousedown", MouseDown, false);
	document.images[0].addEventListener("mousemove", MouseMove, false);
}

function MouseDown()
{
	//window.alert("MouseDown");
}

function MouseMove()
{
	//window.alert("MouseMove");
}


</script>
</head>
<body>
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
if($fina!==0)
{
?>

<h1>Edit ROI</h1>

<img id="foo" src="<?php echo $fina;?>" alt="Photo"/><br/>

<?php
}
?>

<a href="list.php">List</a>
</body>
</html>


