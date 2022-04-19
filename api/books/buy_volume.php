<?php    
    include_once "../../models/book/collection/collectionDb.php";
    include_once "../../models/book/collection/collection.php";
    include_once "../../models/book/volumes/volume.php";
    include_once "../../models/book/volumes/volumesDb.php";
    include_once "../users/is_loged.php";

    $volume = null; 
    $volumeDB = new VolumesDb();

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
     !isset($request->data->volume->id)
     ||
     !isset($request->data->volume->is_buyed)
     ||
     $request->data->volume->id < 0
     ||
     !is_bool($request->data->volume->is_buyed)
    ) {
        header('HTTP/1.1 400 Bad Request');
        exit;
    }

    $volume = new Volume(array(
        "id" => $request->data->volume->id,
        "is_buyed" => $request->data->volume->is_buyed
    ));

    if($volumeDB->update_is_buyed($volume, $current_user)) {
        $output["data"]["status"] = "ok";
    }
    else {
        $output["data"]["status"] = "buyed status of volume update fail";
    }
    print_r(json_encode($output));