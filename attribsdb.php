<?php
require_once('photodb.php');
require_once('roidb.php');

function CreateAttribsTable(&$dbh)
{
	$sql ="CREATE TABLE attribs (id INTEGER PRIMARY KEY, roiId INT NOT NULL, annot TEXT, key TEXT, value TEXT);";
	$ret = $dbh->exec($sql);
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}

	$sql = "CREATE INDEX annotRowIdInd ON attribs(roiId);";
	$ret = $dbh->exec($sql);
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
}

function DropAttribsTable(&$dbh)
{
	$sql ="DROP TABLE attribs;";
	$ret = $dbh->exec($sql);
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
}

function CheckAttribsSchema(&$dbh)
{
	$exists = SqliteCheckTableExists($dbh,"attribs");
	if(!$exists)
		CreateAttribsTable($dbh);
}

function AddAttribToRoi(&$dbh, $roiId, $annot, $key, $value)
{
	$sql = "INSERT INTO attribs (roiId, annot, key, value) VALUES (?,?,?,?);";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($roiId, $annot, $key, $value));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($query.",".$err[2]);}
}

function GetAttribsForRoi(&$dbh, $roiId)
{
	$sql = "SELECT * FROM attribs WHERE roiId=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($roiId));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($query.",".$err[2]);}

	return $sth->fetchAll(PDO::FETCH_ASSOC);
}

function RemoveAttrib(&$dbh, $attribId)
{
	$sql = "DELETE FROM attribs WHERE id=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($attribId));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($query.",".$err[2]);}
}

function UpdateAttribInStore(&$dbh, $modelId, $model)
{

}

?>
