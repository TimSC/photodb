<?php

function StorePhotoUrl($url, $id, $dbh)
{
	$ext = pathinfo($url, PATHINFO_EXTENSION);
	// create curl resource
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	// $output contains the output string
	$output = curl_exec($ch);

	// close curl resource to free up system resources
	curl_close($ch);

	$tmpFina = "files/".sprintf("%05d",$id).".".$ext;
	$tmpFi = fopen($tmpFina,"wb");
	fwrite($tmpFi, $output);
	fclose($tmpFi);
	
	//Update database with filename
	$sql = "UPDATE photos SET fina=? WHERE id=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($tmpFina, $id));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}

	return 1;
}

function PhotoInStore($dbh, $id)
{
	$sql = "SELECT fina FROM photos WHERE id=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($id));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($query.",".$err[2]);}
	foreach($sth->fetchAll(PDO::FETCH_ASSOC) as $row)
	{
		if($row['fina'] !== NULL)
			return $row['fina'];
	}

	return 0;
}

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

function CreatePhotoTable(&$dbh)
{
	$sql ="CREATE TABLE photos (id INTEGER PRIMARY KEY, url TEXT, license TEXT, comment TEXT, width INT, height INT, colour INT, fina TEXT);";
	$ret = $dbh->exec($sql);
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
}

function CheckPhotoSchema(&$dbh)
{
	$exists = SqliteCheckTableExists($dbh,"photos");
	if(!$exists)
		CreatePhotoTable($photoDb);
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

function SetPhotoDimensions($id, $dbh)
{
	$fina=PhotoInStore($dbh, $id);
	if(strlen($fina)==0) return Null;
	$si = getimagesize($fina);
	if(!is_array($si)) return Null;

	//Update database with dimensions
	$sql = "UPDATE photos SET width=?, height=? WHERE id=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($si[0], $si[1], $id));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	return array($si[0], $si[1]);
}

function UpdatePhotoData($dbh, $id, $data)
{
	//print_r($data);
	//Update database with data
	$sql = "UPDATE photos SET url=?, license=?, comment=? WHERE id=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($data['url'], $data['license'], $data['comment'], $id));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
}

function RemoveCachedPhoto(&$dbh, $id)
{
	$fina = PhotoInStore($dbh, $id);

	//Update database with filename
	$sql = "UPDATE photos SET fina=NULL, width=NULL, height=NULL WHERE id=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($id));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}

	if($fina!==0 && file_exists($fina))
	{
		unlink($fina);
	}
}

function DeletePhoto(&$dbh, $id)
{
	$fina = PhotoInStore($dbh, $id);
	if($fina!==0 && file_exists($fina))
	{
		unlink($fina);
	}

	$sql = "DELETE FROM photos WHERE id=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($id));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}

}

?>
