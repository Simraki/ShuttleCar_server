<?php
require_once 'Functions.php';

$functions = new Functions();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->operation)) {
        $operation = $data->operation;
        if (!empty($operation)) {
            if ($operation == 'register') {
                if (isset($data->user) && !empty($data->user) && isset($data->user->name) && isset($data->user->email) && isset($data->user->password)) {
                    
                    $user     = $data->user;
                    
                    $name     = $user->name;
                    $email    = $user->email;
                    $password = $user->password;
                    
                    if ($functions->isEmailValid($email)) {
                        echo $functions->registerUser($name, $email, $password);
                    } else {
                        echo $functions->getMsgInvalidEmail();
                    }
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            } 
            else if ($operation == 'login') {
                if (isset($data->user) && !empty($data->user) && isset($data->user->email) && isset($data->user->password)) {
                    $user     = $data->user;
                    $email    = $user->email;
                    $password = $user->password;
                    echo $functions->loginUser($email, $password);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }
            else if ($operation == 'chgPass') {
                if (isset($data->user) && !empty($data->user) && isset($data->user->email) && isset($data->user->old_password) && isset($data->user->new_password)) {
                    $user         = $data->user;
                    $email        = $user->email;
                    $old_password = $user->old_password;
                    $new_password = $user->new_password;
                    echo $functions->changePassword($email, $old_password, $new_password);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }
            else if ($operation == 'chgImg') {
                
                if (isset($data->user) && !empty($data->user) && isset($data->type) && isset($data->user->email) && isset($data->user->un_id) && (isset($data->user->image_person) || isset($data->user->image_car))) {
                    
                    $user         = $data->user;
                    $type         = $data->type;
                    
                    $email        = $user->email;
                    $un_id        = $user->un_id;
                    
                    if($type){
                        $image = $user->image_car;                        
                    } else {
                        $image = $user->image_person;                           
                    }
                    
                    echo $functions->changeImage($email, $un_id, $type, $image);
                } else {
                    echo $functions->getMsgInvalidParam();
                }    
                
            }
            else if ($operation == 'chgProf') {
                
                if (isset($data->user) && !empty($data->user) && isset($data->user->email) && isset($data->user->un_id) && isset($data->user->name) && isset($data->user->tel) && isset($data->user->car_brand) && isset($data->user->car_model)) {
                    
                    $user         = $data->user;
                    
                    $email        = $user->email;
                    $un_id        = $user->un_id;
                    $name        = $user->name;
                    $tel        = $user->tel;
                    $car_brand        = $user->car_brand;
                    $car_model        = $user->car_model;
                    
                    echo $functions->changeProfile($email, $un_id, $name, $tel, $car_brand, $car_model);
                } else {
                    echo $functions->getMsgInvalidParam();
                }    
                
            }
            else if ($operation == 'getRat') {
                if (isset($data->user) && !empty($data->user) && isset($data->user->email) && isset($data->user->un_id)) {
                    
                    $user  = $data->user;
                    $email = $user->email;
                    $un_id = $user->un_id;
                    
                    echo $functions->getRating($email, $un_id);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }
            else if ($operation == 'addRat') {
                
                if (isset($data->user) && !empty($data->user) && isset($data->type) && isset($data->user->tel)) {
                    
                    $user         = $data->user;
                    $type         = $data->type;
                    
                    $tel        = $user->tel;
                    if ($type){
                        $email = $user->email;
                        $un_id = $user->un_id;
                        $rating = $user->rating;
                        echo $functions->addRating($tel, $email, $un_id, $rating);
                    } else {
                        echo $functions->findUserTel($tel);                        
                    }
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                }    
                
            }
            
            else if ($operation == 'addOrd') {
                if (isset($data->order) && !empty($data->order) && isset($data->user) && !empty($data->user) && isset($data->order->places)
                    && isset($data->order->time) && isset($data->order->date) && isset($data->order->count_place)
                    && isset($data->user->email) && isset($data->user->un_id)) {  
                    
                    $order  = $data->order;
                    $places[] = $order->places;
                    $time = $order->time;
                    $date = $order->date;
                    $place = $order->count_place;
                    
                    $user = $data -> user;
                    $email = $user -> email;
                    $un_id = $user -> un_id;
                    
                    if (!isset($order->lugg)){
                    $lugg = 0;
                    }else{
                    $lugg = $order->lugg;
                    }
                    
                    echo $functions -> addOrder($places, $time, $date, $place, $lugg, $email, $un_id);                        
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                }              
            }
            else if ($operation == 'findOrd') {
                if (isset($data->order) && !empty($data->order) && isset($data->order->pdis) && isset($data->order->pdel)
                    && isset($data->order->time) && isset($data->order->date) && isset($data->order->count_place)) {  
                    
                    $order  = $data->order;
                    
                    $pdis = $order->pdis;
                    $pdel = $order->pdel;
                    $time = $order->time;
                    $date = $order->date;
                    $place = $order->count_place;
                    
                    if (isset($data->user->tel)){
                        $tel = $data->user->tel;}
                    else{
                        $tel = null;
                    }
                    
                    echo $functions -> findOrder($pdis, $pdel, $time, $date, $place, $tel);                        
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                }  
            }
            else if ($operation == 'delOrd') {
                if (isset($data->user) && !empty($data->user) && isset($data->order) && !empty($data->order) && isset($data->user->email)
                    && isset($data->user->un_id) && isset($data->order->id)) {  
                    
                    $user  = $data->user;
                    
                    $id = $data->order->id;
                    $email = $user->email;           
                    $un_id = $user->un_id;
                    
                    echo $functions -> deleteOrder($id, $email, $un_id);                        
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                } 
            }
            
            else if ($operation == 'resOrd') {
                if (isset($data->user) && !empty($data->user) && isset($data->order) && !empty($data->order)
                    && isset($data->user->email) && isset($data->user->un_id) && isset($data->order->id)) {  
                    
                    $user  = $data->user;
                    
                    $id = $data->order->id;         
                    $email = $user->email;
                    $un_id = $user->un_id;
                    
                    echo $functions -> reserveOrder($id, $email, $un_id);                        
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                } 
            }
            else if ($operation == 'findMyOrd') {
                if (isset($data->user) && !empty($data->user) && isset($data->user->email) && isset($data->user->un_id)) {  
                    
                    $user  = $data->user;
                    $email = $user->email;
                    $un_id = $user->un_id;
                    
                    echo $functions -> findMyOrders($email, $un_id);                        
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                }                 
            }
            else if ($operation == 'findResOrd') {
                if (isset($data->user) && !empty($data->user) && isset($data->user->email) && isset($data->user->un_id)) {  
                    
                    $user  = $data->user;
                    $email = $user->email;
                    $un_id = $user->un_id;
                    
                    echo $functions -> findReserveOrders($email, $un_id);                        
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                }                 
            }
            else if ($operation == 'delResOrd') {
                if (isset($data->user) && !empty($data->user) && isset($data->order) && !empty($data->order) && isset($data->user->email)
                    && isset($data->user->un_id) && isset($data->order->id)) {  
                    
                    $user  = $data->user;
                    
                    $id = $data->order->id;
                    $email = $user->email;           
                    $un_id = $user->un_id;
                    
                    echo $functions -> deleteReserveOrder($id, $email, $un_id);                        
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                } 
            }
            
            else if ($operation == 'sendMessage') {
                if (isset($data->message) && !empty($data->message) && isset($data->message->message)) {  
                    
                    $message  = $data->message;
                    
                    $text = $message->message;           
                    
                    echo $functions -> sendMessage($text);                        
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                } 
            }
        } else {
            echo $functions->getMsgParamNotEmpty();
        }
    } else {
        echo $functions->getMsgInvalidParam();
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    echo "Shuttle Car Ready ^_^";
}