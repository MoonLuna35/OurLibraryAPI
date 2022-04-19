<?php 
    include_once "../../includes/books/Iadd.php";

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // The request is using the POST method
        return http_response_code(200);
    
    }
    
   
    
    // Get the posted data.
    $postdata = file_get_contents("php://input"); 

    $collectionDb = new CollectionDb();
    $volumesDb = new VolumesDb();
    $output["data"] = [];

    $collection  = null;

    if(!isset($postdata) || empty($postdata)) { 
        
        header('HTTP/1.1 400 Bad Request');
        exit;   
    }
    else {
       
        $request = json_decode($postdata);
        $colgen = new BookCollectionGenerator($request); //On genere la collection
        $collection = $colgen->get_collection();
        $collection->authors_with_id_are_existing();//On regarde que tout les ids des auteurs existent
        $add_status = $collectionDb->add($collection); 
        if($add_status === "collection already registred") {
            $output["data"]["error"] = "collection already registred";
            print_r(json_encode($output));
            
        }
        else if($add_status) {
            $output["data"]["collection"] = $collection->to_array();
            print_r(json_encode($output));
        }
        else {
            header('HTTP/1.1 500 Internal Server Error');
            exit;   
        }
    }


?>