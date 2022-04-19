<?php    
    include_once "../../models/book/collection/collectionDb.php";
    include_once "../../models/book/collection/collection.php";
    include_once "../../models/book/volumes/volume.php";
    include_once "../../models/book/volumes/volumesDb.php";

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        return http_response_code(200);
    }

    


    $collectionDb = new CollectionDb();
    $c = $collectionDb->select_collections_of_user();//On recupere les collections de l'utilisateur 
    $output["data"]["collections"] = array();
    for($i = 0; $i < sizeof($c); $i++) {
        array_push($output["data"]["collections"], $c[$i]->to_array());
    }
    
    
    print_r(json_encode($output))
    //On recupere les auteurs de chaque volume
?>