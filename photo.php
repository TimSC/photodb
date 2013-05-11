<?php

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');

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

echo $viewPhotoId;
?>



<?php
}
?>

</html>

