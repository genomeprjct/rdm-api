<?php

$servername = "";
$username = "";
$password = "";
$dbname = "";

$name = $_GET['name'];
$region = $_GET['region'];
$questMon = $_GET['encounter'];
$questItem = $_GET['item'];
$questItemCount = $_GET['itemCount'];
$quest = $_GET['quest'];

$ITEMS = ['revive' => 201, 'rare candy' => 1301];

$questItem = $ITEMS[$questItem];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if(!empty($name)) {
        $name = '%'.$_GET['name'].'%';
        if(empty($region)) {
            $stmt = $conn->prepare("select name,lat,lon,url,quest_template from pokestop p where p.name like :gymName ORDER BY name ASC;");
            $stmt->bindParam(':gymName', $name, PDO::PARAM_STR);
        } else {
            $stmt = $conn->prepare("select name,lat,lon,url,quest_template from pokestop p join (select min_lat,max_lat,min_lon,max_lon from regions where name = :regionName) r where p.lat >= r.min_lat AND p.lat <= r.max_lat AND p.lon >= r.min_lon AND p.lon <= r.max_lon and p.name like :gymName ORDER BY name ASC;");
            $stmt->bindParam(':gymName', $name, PDO::PARAM_STR);
            $stmt->bindParam(':regionName', $region, PDO::PARAM_STR);
        }
        
    } else if (!empty($questMon)) {
        $stmt = $conn->prepare("select p.name,lat,lon,url,quest_template from pokestop p inner join pokedex d where p.quest_pokemon_id = d.pokemon_id and d.name = :pokemon ORDER BY name ASC;");
        $stmt->bindParam(':pokemon', $questMon, PDO::PARAM_STR);
    } else if (!empty($questItem)) {
        $sql = "select name,lat,lon,url,quest_template from pokestop where JSON_EXTRACT(quest_rewards,'$[0].info.item_id') = :itemId";
        if(!empty($questItemCount)) {
            $sql .= " AND JSON_EXTRACT(quest_rewards,'$[0].info.amount') = :itemQty";
        }
        if(!empty($quest)) {
            $sql .= " AND quest_template = :quest";
        }
        $sql .= " ORDER BY name ASC;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':itemId', $questItem, PDO::PARAM_INT);
        if(!empty($questItemCount)) {
            $stmt->bindParam(':itemQty', $questItemCount, PDO::PARAM_INT);
        }
        if(!empty($quest)) {
            $stmt->bindParam(':quest', $quest, PDO::PARAM_STR);
        }
    }

    if($stmt != null) {
        $stmt->execute();
        // set the resulting array to associative
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        header('Content-type: application/json');
        echo json_encode( $stmt->fetchAll() );
    }
    
}
catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;

?>
