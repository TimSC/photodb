<?php
require_once('photodb.php');

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');
CheckPhotoSchema($photoDb);

$sql = "SELECT * FROM photos;";
$sth = $photoDb->prepare($sql);
if($sth===false) {$err= $photoDb->errorInfo();throw new Exception($sql.",".$err[2]);}
$ret = $sth->execute();
if($ret===false) {$err= $photoDb->errorInfo();throw new Exception($sql.",".$err[2]);}
?>
<html>
<body>

<a href="photo.php">Upload Photo</a>

<table border="2">
<?php
while($row = $sth->fetch(PDO::FETCH_ASSOC))
{
	echo "<tr>";
	echo '<td><a href="photo.php?id='.$row['id'].'">'.$row['id']."</a></td>";
	echo "<td>".$row['url']."</td>";
	echo "<td>".$row['license']."</td>";
	echo "<td>".$row['comment']."</td>";
	echo "<td>".$row['width']."</td>";
	echo "<td>".$row['height']."</td>";
	echo "<td>".$row['colour']."</td>";
	echo "</tr>\n";
}


?>

</table>
</body>
</html>
