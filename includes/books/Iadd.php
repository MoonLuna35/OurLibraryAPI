<?php
    require_once "../../models/book/collection/collectionDb.php";
    require_once "../../models/book/collection/collection.php";
    require_once "../../models/book/volumes/volume.php";
    require_once "../../models/book/volumes/volumesDb.php";
    require_once "../../models/book/author/author.php";
    require_once "../../models/book/author/authorDb.php";
    
    
    class BookCollectionGenerator {
        private $_collection = null;
        private $_authorDb;

        public function __construct($request) {
            $this->_authorDb = new AuthorDB();
            $volumes = array();
            if( //SI  
                !isset($request->data->collection->title) //il n'y a pas de titre 
                ||
                !isset($request->data->collection->resume) //il n'y a pas de resume 
                || 
                preg_match(regExp::CONTAIN_UNVALID_TITLE_CHAR, trim($request->data->collection->title)) //ou que le titre contiens des caracteres non authorises
                ||//OU 
                !isset($request->data->collection->is_conserved)//que l'etat n'existe pas (vive l'anarchie)
                ||//OU
                !is_bool($request->data->collection->is_conserved)
                ||
                !isset($request->data->collection->editor) 
                || 
                preg_match(regExp::CONTAIN_UNVALID_TITLE_CHAR, trim($request->data->collection->editor))
                ) { //ALORS
                header('HTTP/1.1 400 Bad Request'); //On renvoie 400
                exit; 
            }
            else { //SINON
                //On cree la collection
                $this->_collection = new Collection(array(
                    "title" => trim($request->data->collection->title),
                    "resume" => htmlentities(trim($request->data->collection->resume)),
                    "editor" =>trim($request->data->collection->editor),
                    "is_conserved" => $request->data->collection->is_conserved
                ));
                //On analyse les options
                for(
                    $i = 0; 
                    isset($request->data->collection->volumes) && is_array($request->data->collection->volumes) && $i < sizeof($request->data->collection->volumes); 
                    $i++
                ) { //POUR TOUT volume FAIRE
                    if( //SI 
                        !isset($request->data->collection->volumes[$i]->num) //volume n'existe pas 
                        || //OU
                        (int)$request->data->collection->volumes[$i]->num < 0 //que volume n'est egale au volume courrant
                        ||//OU
                        !isset($request->data->collection->volumes[$i]->is_buyed) //volume n'existe pas 
                        || //OU
                        !is_bool($request->data->collection->volumes[$i]->is_buyed) //que volume n'est egale au volume courrant
                        ||//OU
                            $this->vol_title_is_nok($request->data->collection->volumes[$i]) //que le titre n'est pas bon
                        ||//OU
                            $this->vol_buy_link_is_nok($request->data->collection->volumes[$i]) //que l'url n'est pas bon
                        || //OU
                            $this->vol_author_is_nok($request->data->collection->volumes[$i]) //que l'auteur n'est pas bon
                    ) { //ALORS 
                        
                        header('HTTP/1.1 400 Bad Request'); //On renvoie 400
                        exit; //renvoyer 400
                    }
                    else { //SINON
                        $vol = $request->data->collection->volumes[$i];
                        $this->_collection->add_volume(new Volume(array(
                            "volume" => $vol)));
                        for( //POUR TOUT auteur FAIRE
                            $j = 0 ; 
                            isset($vol->authors) && is_array($vol->authors) && $j < sizeof($vol->authors); 
                            $j++
                        ) { 
                            if(
                                !isset($vol ->authors[$j]->name) //le nom n'existe pas
                                || //OU
                                preg_match(regExp::CONTAIN_UNVALID_TITLE_CHAR, trim($vol ->authors[$j]->name)) // qu'il contiens un caractere interdit
                                || //OU
                                $this->author_surname_is_nok($vol ->authors[$j])
                                ||//OU 
                                $this->author_function_is_nok($vol ->authors[$j])
                            ) { //ALORS
                                
                                //On est possiblement avec un id (un auteur deja dans la base de donnee)
                                if( //SI Il n'y a en faite pas d'id ou qu'il a une valeur etrange
                                    !isset($vol ->authors[$j]->id)
                                    ||
                                    (int)$vol ->authors[$j]->id < 0
                                ) { //ALORS
                                    print_r($this->author_function_is_nok($vol ->authors[$j]));
                                    print_r("<>");
                                    //header('HTTP/1.1 489 ' );
                                    exit; //renvoyer 400
                                }
                                else {
                                    $this->_collection->add_author_at(new Author(array(
                                        "id" => $vol ->authors[$j]->id
                                    )), $i);//On insert l'id de l'auteur
                                }
                            }
                            else { //SINON (il est nome et le nom est bon, on l'insert)
                                $this->_collection->add_author_at(new Author(array(
                                    "name" => $vol ->authors[$j]->name,
                                    "surname" => $vol ->authors[$j]->surname,
                                    "function" => $vol ->authors[$j]->function,
                                )), $i);//On insert l'id de l'auteur
                            }
                        }
                    }
                } 
                
            }
            //On modifie les auteurs pour que ceux qui sont identiques soient en refferences
            $this->have_default_in_author_of_volume();
            $this->_collection->prepare_author_to_add();  
            return  $this->_collection;
        }
        
        /*
        * DANS CETTE FONCTION 
        *   On regarde si les auteurs sont les meme dans un volume
        *
        */ 
        private function have_default_in_author_of_volume() {
            $v = $this->_collection->get_volumes();
            for($i = 0; $i < sizeof($v); $i++) {
                for($j = 0; $j < sizeof($v[$i]->authors); $j++) {
                    for($k = $j+1; $k < sizeof($v[$i]->authors); $k++) {
                        if( //SI les auteurs dans un meme volume sont les memes
                            $v[$i]->authors[$j]->get_name() === $v[$i]->authors[$k]->get_name()
                            &&
                            $v[$i]->authors[$j]->get_surname() === $v[$i]->authors[$k]->get_surname()
                            &&
                            $v[$i]->authors[$j]->get_function() === $v[$i]->authors[$k]->get_function()
                            ||
                            $v[$i]->authors[$j]->get_id() === $v[$i]->authors[$k]->get_id()
                            &&
                            $v[$i]->authors[$j]->get_id() !== -1
                            
                        ) {
                            header('HTTP/1.1 400 Bad Request'); //On renvoie 400
                            exit; //renvoyer 400
                        }
                    }
                }
            }
        }

        private function author_surname_is_nok($author) {
            return(
                isset($author->surname)
                &&
                preg_match(regExp::CONTAIN_UNVALID_TITLE_CHAR, trim($author->surname))
            );
        }

        private function author_function_is_nok($author) {
            return(
                isset($author->function)
                &&
                !in_array($author->function, values_acceptable::AUTHOR_FUNCTION)
            );
        }
        private function vol_author_is_nok($vol) {
            return(
                isset($vol->authors) 
                &&
                !is_array($vol->authors)
            );
        }

        private function vol_buy_link_is_nok($vol) {
            return(
                isset($vol->buy_link)
                &&
                !preg_match(regExp::URL_PATERN, trim($vol->buy_link))
            );
        }

        private function vol_title_is_nok($vol) {
            return(
                isset($vol->title)
                &&
                preg_match(regExp::CONTAIN_UNVALID_TITLE_CHAR, trim($vol->title))
            );
        }




        public function get_collection() {
            return $this->_collection;
        }
    }