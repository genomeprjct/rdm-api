<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rdm";

$name = $_GET['name'];
$region = $_GET['region'];
$questMon = $_GET['encounter'];
$questItem = $_GET['item'];
$questItemCount = $_GET['itemCount'];
$type = $_GET['type'];
$quest = $_GET['quest'];

$ITEMS = ['revive' => 201, 'rare candy' => 1301];
$TYPES = ['stardust' => 3];

$questItem = $ITEMS[$questItem];
$questType = $TYPES[$type];

try {
    echo ($questType);
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
    } else if (!empty($questType)) {
        $sql = "select name,lat,lon,url,quest_template from pokestop where JSON_EXTRACT(quest_rewards,'$[0].type') = :typeId";
        if(!empty($questItemCount)) {
            $sql .= " AND JSON_EXTRACT(quest_rewards,'$[0].info.amount') = :itemQty";
        }
        if(!empty($quest)) {
            $sql .= " AND quest_template = :quest";
        }
        $sql .= " ORDER BY name ASC;";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':typeId', $questType, PDO::PARAM_INT);
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

/**
    if (target == 'dragon-candy') {
      filter = e => e.quest_template == 'challenge_catch_dragon_veryhard' && e.quest_rewards[0].info.item_id == 1301 && e.quest_rewards[0].info.amount == 3;
    } else if (target == '3 rare candy') {
      filter = e => e.quest_rewards[0].info.item_id == 1301 && e.quest_rewards[0].info.amount == 3;
    } else if (target == '3 great throws 3 rare candy') {
      filter = e => e.quest_template == 'challenge_land_great_curve_inarow_veryhard' && e.quest_rewards[0].info.item_id == 1301 && e.quest_rewards[0].info.amount == 3;
    } else if (target == '4 revives') {
      filter = e => e.quest_rewards[0].info.item_id == 201 && e.quest_rewards[0].info.amount == 4;
    } else if (target == 'silver-pinaps') {
      filter = e => e.quest_template == 't1_2019_spin_medium';
    } else if (target == 'eggs-candy-5') {
      filter = e => e.quest_template == 't1_2019_quest_hatch_egg_plural' && e.quest_rewards[0].info.item_id == 1301 && e.quest_rewards[0].info.amount == 3;
    } else if(inverseTerms[target.toLowerCase()]) {
      var pokemonId = parseInt(inverseTerms[target.toLowerCase()].replace("poke_",""))
      console.log(pokemonId);
      filter = e => e.quest_rewards && e.quest_rewards[0].info.pokemon_id == pokemonId
    } else {
      message.reply(`invalid parameters ${target}`)
    }

**/

?>
