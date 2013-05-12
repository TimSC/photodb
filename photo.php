<?php
#https://farm9.staticflickr.com/8471/8135759889_763413738b_b.jpg
require_once('photodb.php');

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');

if(isset($_GET['id']))
	$viewPhotoId = $_GET['id'];
else
	$viewPhotoId = NULL;
if(isset($_POST['form-action']) and $_POST['form-action']=="Upload" and strlen($_POST['url'])>0)
{
	$exists = SqliteCheckTableExists($photoDb,"photos");
	if(!$exists)
		CreatePhotoTable($photoDb);

	$sql = "INSERT INTO photos (url, license, comment) VALUES (?,?,?);";
	$sth = $photoDb->prepare($sql);
	if($sth===false) {$err= $photoDb->errorInfo();throw new Exception($sql.",".$err[2]);}
	$sth->execute(array($_POST['url'], $_POST['license'], $_POST['comment']));

	$viewPhotoId = $photoDb->lastInsertId();
}

if(isset($_GET['delete-confirmed']))
{
	DeletePhoto($photoDb, (int)($_GET['delete-confirmed']));
}

$photoData = GetPhotoData($photoDb, $viewPhotoId);
?>

<html>
<body>
<?php

if(isset($_GET['delete']))
{
?>
<h1>Are you sure?</h1>

<a href="?delete-confirmed=<?php echo $_GET['delete']?>">Yes</a> 
<a href="?id=<?php echo $_GET['delete']?>">No</a><br/>

<?php
}

if($viewPhotoId==NULL && !isset($_GET['delete']))
{
?>
<h1>Photo Upload</h1>

<form name="upload" method="post" action="photo.php">
URL <input type="text" name="url"><br>
Upload File <br>
License <input type="text" name="license"><br>
Comment <input type="text" name="comment"><br>
<input type="submit" name="form-action" value="Upload">
</form>

<?php
}

if($viewPhotoId!=NULL && !isset($_GET['delete']))
{



?>
<h1>Photo <?php echo $viewPhotoId; ?></h1>

<?php
if(PhotoInStore($photoDb, $viewPhotoId)===0)
{
	StorePhotoUrl($photoData['url'], $viewPhotoId, $photoDb);
}
$fina = PhotoInStore($photoDb, $viewPhotoId);
if($fina!==0)
{
if($photoData['width']==0)
	list($photoData['width'], $photoData['height']) = SetPhotoDimensions($viewPhotoId, $photoDb);
$maxDim = $photoData['width'];
if($photoData['height']>$maxDim)
	$maxDim = $photoData['height'];
$width = $photoData['width'];
$height = $photoData['height'];
if($maxDim > 640)
{
	$width *= 640. / $maxDim;
	$height *= 640. / $maxDim;
}
?>

<img src="<?php echo $fina;?>" alt="Photo" width="<?php echo $width;?>" height="<?php echo $height;?>"/><br/>
<?php
}
echo 'Size: '.$photoData['width']." by ".$photoData['height']."<br/>";
?>

<form name="upload" method="post" action="photo.php">
URL <input type="text" name="url" value="<?php echo $photoData['url']; ?>"><br>
License <input type="text" name="license" value="<?php echo $photoData['license']; ?>"><br>
Comment <input type="text" name="comment" value="<?php echo $photoData['comment']; ?>"><br>
<input type="submit" name="form-action" value="Edit">
</form>

<a href="photo.php?delete=<?php echo $viewPhotoId;?>">Delete</a> 
<a href="roi.php?id=<?php echo $viewPhotoId;?>">Edit ROIs</a> 
<a href="managerois.php?id=<?php echo $viewPhotoId;?>">Manage ROIs</a> 
<?php
}
?>
<a href="list.php">List Photos</a>
</body>
</html>

