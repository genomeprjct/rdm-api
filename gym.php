<?php

$servername = "";
$username = "";
$password = "";
$dbname = "";

$name = '%'.$_GET['name'].'%';
$region = $_GET['region'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(empty($region)) {
	$stmt = $conn->prepare("select name,lat,lon,url from gym g where g.name like :gymName ORDER BY `name` ASC;");
	$stmt->bindParam(':gymName', $name, PDO::PARAM_STR);
    } else {
    	$stmt = $conn->prepare("select name,lat,lon,url from gym g join (select min_lat,max_lat,min_lon,max_lon from regions where name = :regionName) r where g.lat >= r.min_lat AND g.lat <= r.max_lat AND g.lon >= r.min_lon AND g.lon <= r.max_lon and g.name like :gymName ORDER BY `name` ASC;");
    	$stmt->bindParam(':gymName', $name, PDO::PARAM_STR);
    	$stmt->bindParam(':regionName', $region, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    // set the resulting array to associative
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    
    header('Content-type: application/json');
	echo json_encode( $stmt->fetchAll() );
}
catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;

?>

