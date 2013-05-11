<?php

function CreateRoiTable(&$dbh)
{
	$sql ="CREATE TABLE rois (id INTEGER PRIMARY KEY, photoId INT, roiNum INT, x1 INT, y1 INT, x2 INT, y2 INT, metadata TEXT);";
	$ret = $dbh->exec($sql);
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
}

function UpdateRois($dbh, $id, $bboxes)
{
	for($i=0;$i<count($bboxes);$i++)
	{
		$box = $bboxes[$i];
		$sql = "SELECT * FROM rois WHERE photoId=? AND roiNum=?;";
		$sth = $dbh->prepare($sql);
		if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
		$ret = $sth->execute(array($id, $i));
		if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}

		$found = count($sth->fetchAll());
		if($found)
		{
			$sql = "UPDATE rois SET x1 = ?, y1 = ?, x2 = ?, y2 = ? WHERE photoId=? AND roiNum=?;";
			$sth = $dbh->prepare($sql);
			if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
			$ret = $sth->execute(array($box[0], $box[1], $box[2], $box[3], $id, $i));
			if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
		}
		else
		{
			$sql = "INSERT INTO rois (photoId, roiNum, x1, y1, x2, y2) VALUES (?,?,?,?,?,?);";
			$sth = $dbh->prepare($sql);
			if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
			$ret = $sth->execute(array($id, $i, $box[0], $box[1], $box[2], $box[3]));
			if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}	
		}
	}

	$sql = "DELETE FROM rois WHERE photoId=? AND roiNum>=?;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($id, count($bboxes)));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}	

}

function GetRois($dbh, $id)
{
	$sql = "SELECT * FROM rois WHERE photoId=? ORDER BY roiNum ASC;";
	$sth = $dbh->prepare($sql);
	if($sth===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}
	$ret = $sth->execute(array($id));
	if($ret===false) {$err= $dbh->errorInfo();throw new Exception($sql.",".$err[2]);}

	$bboxes = array();
	foreach($sth->fetchAll(PDO::FETCH_ASSOC) as $row)
	{
		array_push($bboxes, array((float)$row['x1'], (float)$row['y1'], (float)$row['x2'], (float)$row['y2']));
	}
	return $bboxes;
}

?>
