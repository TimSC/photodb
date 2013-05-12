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

$model = GetModelForStore($photoDb, (int)$_GET['modelId']);





?>

<html>
<body>
<h1>Model</h1>
<?php print_r($model); ?>

</body>
</html>
