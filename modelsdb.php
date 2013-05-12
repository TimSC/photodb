<?php
require_once('photodb.php');
require_once('roidb.php');


function CreateModelsTable(&$dbh)
{
	$sql ="CREATE TABLE models (id INTEGER PRIMARY KEY, roiId INT NOT NULL, modelName TEXT NOT NULL, model TEXT);";
	$ret = $dbh->exec($sql);
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}

	$sql = "CREATE INDEX rowIdInd ON models(roiId);";
	$ret = $dbh->exec($sql);
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
}

function DropModelsTable(&$dbh)
{
	$sql ="DROP TABLE models;";
	$ret = $dbh->exec($sql);
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
}

function CheckModelsSchema(&$dbh)
{
	$exists = SqliteCheckTableExists($dbh,"models");
	if(!$exists)
		CreateModelsTable($dbh);
}

function AddModelToRoi(&$dbh, $roiId, $modelName)
{
	$sql = "INSERT INTO models (roiId, modelName) VALUES (?,?);";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($roiId, $_POST['modelName']));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($query.",".$err[2]);}
}

function GetModelForRoi(&$dbh, $roiId)
{
	$sql = "SELECT * FROM models WHERE roiId=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($roiId));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($query.",".$err[2]);}

	return $sth->fetchAll(PDO::FETCH_ASSOC);
}

function RemoveModel($dbh, $modelId)
{
	$sql = "DELETE FROM models WHERE id=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($modelId));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($query.",".$err[2]);}

}

?>
