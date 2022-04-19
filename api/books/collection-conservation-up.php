<?php    
    include_once "../../models/book/collection/collectionDb.php";
    include_once "../../models/book/collection/collection.php";
    include_once "../users/is_loged.php";

    $collection = null; 
    $collectionDB = new collectionDb();

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        return http_response_code(200);
    }
    $postdata = file_get_contents("php://input");
    if(!isset($postdata) || empty($postdata)) { 
        
        header('HTTP/1.1 400 Bad Request');
        exit;   
    }
    $request = json_decode($postdata);
    if(
     !isset($request->data->collection->id)
     ||
     !isset($request->data->collection->is_conserved)
     ||
     $request->data->collection->id < 0
     ||
     !is_bool($request->data->collection->is_conserved)
    ) {
        header('HTTP/1.1 400 Bad Request');
        exit;
    }

    $collection = new Collection(array(
        "id" => $request->data->collection->id,
        "is_conserved" => $request->data->collection->is_conserved
    ));

    if($collectionDB->update_is_conserved($collection, $current_user)) {
        $output["data"]["status"] = "ok";
    }
    else {
        $output["data"]["status"] = "buyed status of volume update fail";
    }
    print_r(json_encode($output));