<?php

function SqliteCheckTableExists(&$dbh,$name)
{
	//Check if table exists
	$sql = "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name=".$dbh->quote($name).";";
	$ret = $dbh->query($sql);
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$tableExists = 0;
	foreach($ret as $row)
	{
		$tableExists = ($row[0] > 0);
	}
	return $tableExists;
}

function GetPhotoData(&$dbh,$id)
{
	$sql = "SELECT * FROM photos WHERE id=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($id));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($query.",".$err[2]);}
	foreach($sth->fetchAll(PDO::FETCH_ASSOC) as $row)
	{
		//print_r($row);
		return $row;
	}
	return NULL;
}

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
	{
		$sql ="CREATE TABLE photos (id INTEGER PRIMARY KEY, url TEXT, license TEXT, comment TEXT);";
		$ret = $photoDb->exec($sql);
		if($ret===false) {$err= $photoDb->errorInfo();throw new Exception($sql.",".$err[2]);}
	}

	$sql = "INSERT INTO photos (url, license, comment) VALUES (?,?,?);";
	$sth = $photoDb->prepare($sql);
	if($sth===false) {$err= $photoDb->errorInfo();throw new Exception($sql.",".$err[2]);}
	$sth->execute(array($_POST['url'], $_POST['license'], $_POST['comment']));

	$viewPhotoId = $photoDb->lastInsertId();
}

$photoData = GetPhotoData($photoDb, $viewPhotoId);
?>

<html>

<?php
if($viewPhotoId==NULL)
{
?>
<h1>Photo Upload</h1>

<form name="upload" method="post">
URL <input type="text" name="url"><br>
Upload File <br>
License <input type="text" name="license"><br>
Comment <input type="text" name="comment"><br>
<input type="submit" name="form-action" value="Upload">
</form>

<?php
}

if($viewPhotoId!=NULL)
{



?>
<h1>Photo <?php echo $viewPhotoId; ?></h1>

<form name="upload" method="post">
URL <input type="text" name="url" value="<?php echo $photoData['url']; ?>"><br>
License <input type="text" name="license" value="<?php echo $photoData['license']; ?>"><br>
Comment <input type="text" name="comment" value="<?php echo $photoData['comment']; ?>"><br>
<input type="submit" name="form-action" value="Edit">
</form>

<?php
}
?>

</html>

