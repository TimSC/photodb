<?php

require_once('photodb.php');
require_once('roidb.php');
require_once('modelsdb.php');

//Prepare database connection
chdir(dirname(realpath (__FILE__)));
$photoDb = new PDO('sqlite:photodb.db');

CheckPhotoSchema($photoDb);
CheckRoiSchema($photoDb);
CheckModelsSchema($photoDb);

if(!isset($_GET['modelId'])) die("Model Id not specified");

$model = GetModelForStore($photoDb, (int)$_GET['modelId']);
if($model===Null) die("Error getting model data");
$bboxesInfo = GetRoiFromStore($photoDb, $model['roiId']);
if($bboxesInfo===Null) die("Error getting ROI data");
$photoData = GetPhotoData($photoDb, $bboxesInfo['photoId']);
if($photoData===Null) die("Error getting photo data");
$fina = $photoData['fina'];

?>

<html>
<body>
<h1>Model</h1>
<?php print_r($model); ?>
<?php print_r($bboxesInfo); ?>
<?php print_r($photoData); ?>




</body>
</html>
