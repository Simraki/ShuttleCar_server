<?php

require_once 'DBOperations.php';

class Functions
{
    
    private $db;
    
    public function __construct()
    {
        
        $this->db = new DBOperations();
        
    }
    
    
    public function registerUser($name, $email, $password)
    {
        
        $db = $this->db;
        
        if (!empty($name) && !empty($email) && !empty($password)) {
            
            if ($db->checkUserExist($email)) {
                
                $response["result"]  = "fail";
                $response["message"] = "Такой пользователь уже есть";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            } else {
                
                $result = $db->register($name, $email, $password);
                
                if ($result) {
                    
                    $response["result"] = "success";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                } else {
                    
                    $response["result"]  = "fail";
                    $response["message"] = "Ошибка при регистрации";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                }
            }
        } else {
            
            return $this->getMsgParamNotEmpty();
            
        }
    }
    
    public function loginUser($email, $password)
    {
        $db = $this->db;
        
        if (!empty($email) && !empty($password)) {
            
            if ($db->checkUserExist($email)) {
                
                $result = $db->login($email, $password);
                
                if (!$result) {
                    
                    $response["result"]  = "fail";
                    $response["message"] = "Неверный логин или пароль";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                } else {
                    
                    $response["result"] = "success";
                    $response["user"]   = $result;
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                }
                
            } else {
                
                $response["result"]  = "fail";
                $response["message"] = "Неверный логин или пароль";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            }
        } else {
            
            return $this->getMsgParamNotEmpty();
        }
        
    }
    
    public function changePassword($email, $old_password, $new_password)
    {
        
        $db = $this->db;
        
        if (!empty($email) && !empty($old_password) && !empty($new_password)) {
            
            if (!$db->login($email, $old_password)) {
                
                $response["result"]  = "fail";
                $response["message"] = 'Неправильный старый пароль';
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            } else {
                
                
                $result = $db->changePassword($email, $new_password);
                
                if ($result) {
                    
                    $response["result"]  = "success";
                    $response["message"] = "Пароль изменён";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                } else {
                    
                    $response["result"]  = "fail";
                    $response["message"] = 'Ошибка замены пароля';
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                }
                
            }
        } else {
            
            return $this->getMsgParamNotEmpty();
        }
        
    }
        
    public function changeImage($email, $un_id, $type, $image)
    {        
        $db = $this->db;
        
        if (!empty($email) && !empty($un_id) && !empty($image)) {
            
            if (!$db->checkLoginUnID($email, $un_id)) {
                
                $response["result"]  = "fail";
                $response["message"] = 'Ошибка';
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            } else {
                $id = $db->getID($email, $un_id);
                
                if (!$id) {
                    $response["result"]  = "fail";
                    $response["message"] = "Ошибка";
                    
                } else {
                    
                    $this->getImage($image, "$id.png");
                    $this->imageResize("$id.png", "$id.png", 1024, 1024);
                    
                    if ($type) {

                        if (rename("$id.png", "image_car/$id.png")) {
                            $response["result"] = "success";
                            $path               = 'http://192.168.1.60' . "/shuttlecar/" . "image_car/$id.png";
                            $user["image_car"]        = $path;
                            $response["user"]   = $user;
                            $response["message"] = "Фото машины загружено";

                        } else {
                            $response["result"]  = "fail";
                            $response["message"] = "Ошибка";
                        }
                        
                    } else {

                        if (rename("$id.png", "image_person/$id.png")) {
                            $response["result"] = "success";
                            $path               = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id.png";
                            $user["image_person"]        = $path;
                            $response["user"]   = $user;
                            $response["message"] = "Фото профиля загружено";

                        } else {
                            $response["result"]  = "fail";
                            $response["message"] = "Ошибка";
                        }
                        
                    }
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            }
            
        } else {
            
            return $this->getMsgParamNotEmpty();
        }
        
    }    
    
    public function changeProfile($email, $un_id, $name, $tel, $car_brand, $car_model)
    {        
        $db = $this->db;
        
        if (!empty($email) && !empty($un_id) && !empty($name)) {
            
            if (!$db->checkLoginUnID($email, $un_id)) {
                
                $response["result"]  = "fail";
                $response["message"] = 'Ошибка';
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            } else {
                
                
                $result = $db->changeProfile($email, $un_id, $name, $tel, $car_brand, $car_model);
                
                if ($result) {
                    
                    $response["result"] = "success";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                } else {                    
                    $response["result"]  = "fail";
                    $response["message"] = 'Ошибка изменения профиля';
                    return json_encode($response, JSON_UNESCAPED_UNICODE);                    
                }
                
            }
        } else {
            
            return $this->getMsgParamNotEmpty();
        }
        
    }
    
    public function getRating($email, $un_id)
    {
        $db = $this->db;
        
        if (!empty($email) && !empty($un_id)) {
            
            if (!$db->checkLoginUnID($email, $un_id)) {
                
                $response["result"] = "fail";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            } else {
                $rating = $db->getRating($email, $un_id);
                
                if (!$rating || $rating > 5) {
                    $response["result"] = "fail";
                } else {
                    $response["result"] = "success";
                    $user["rating"]     = $rating;
                    $response["user"]   = $user;
                }
            }
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            return $this->getMsgParamNotEmpty();
        }
    }
    
    public function addRating($tel, $email, $un_id, $rating)
    {
        $db = $this->db;
        
        if (!empty($tel)) {
            
            if (!$db->addRating($tel, $email, $un_id, $rating)) {
                
                $response["result"]  = "fail";
                $response["message"] = "Вы уже оценивали этого пользователя";
                
            } else {            
                $response["result"] = "success";
            }
            
            return json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } else {
            return $this->getMsgParamNotEmpty();
        }
    }  
    
    
    public function addOrder($places, $time, $date, $place, $lugg, $email, $un_id)
    {
        $db = $this->db;
        
        if (!empty($places) && !empty($date) && !empty($place) && !empty($email) && !empty($un_id)) {
            
            $id = $db->getID($email, $un_id);
            
            if (!$id) {
                $response["result"] = "fail";
            } else {
                
                if (!empty($lugg)) {
                    $lugg = $db->lugg_sizeToID($lugg);
                }
                
                $places = $places[0];

                $pdis = $places[0];
                $count_max = count($places) - 1;
                $pdel = $places[$count_max];
                
                $temp_array = array($pdis, $pdel);
                $places = array_diff($places, $temp_array);
                
                $pdis = $db->placeToID($pdis);
                $pdel = $db->placeToID($pdel);
                
                if (!$db->checkOrder($pdis, $pdel, $time, $date, $id)) {
                    $response["result"]  = "fail";
                    $response["message"] = "Такая поездка у вас уже имеется";
                } else if (!$pdis || !$pdel) {
                    $response["result"]  = "fail";
                    $response["message"] = "Ошибка2";    
                } else {
                    
                    if (!$db->addOrder($pdis, $pdel, $places, $time, $date, $place, $lugg, $id)) {
                        $response["result"] = "fail";
                        $response["message"] = "Ошибка1";    
                    } else {
                        $response["result"] = "success";
                    }
                    
                }
            }
            return json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } else {
            return $this->getMsgParamNotEmpty();
        }
    }
    
    public function findOrder($pdis, $pdel, $time, $date, $place, $tel)
    {
        $db = $this->db;
        
        if (!empty($pdis) && !empty($pdel) && !empty($time) && !empty($date) && !empty($place)) {
            
            $pdis = $db->placeToID($pdis);
            $pdel = $db->placeToID($pdel);
            
            $orders = $db->findOrder($pdis, $pdel, $time, $date, $place, $tel);
            
            if (!$orders) {
                $response["result"]  = "fail";
                $response["message"] = "Такой поездки не найдено";
            } else {
                $response["result"] = "success";
                $response["orders"] = $orders;
            }
            
            return json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } else {
            return $this->getMsgParamNotEmpty();
        }
    }
    
    public function findMyOrders($email, $un_id)
    {
        $db = $this->db;
        
        if (!empty($email) && !empty($un_id)) {
            
            $id = $db->getID($email, $un_id);
            
            if (!$id) {
                $response["result"] = "fail";
            } else {
                $orders = $db->findMyOrders($id);
                
                if (!$orders) {
                    $response["result"] = "fail";
                    $response["message"] = "Ваших поездок не найдено";
                } else {
                    $response["result"] = "success";
                    $response["orders"] = $orders;
                }
            }
            
            return json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } else {
            return $this->getMsgParamNotEmpty();
        }
    }
    
    public function deleteOrder($id, $email, $un_id)
    {
        $db = $this->db;
        
        if (!empty($id) && !empty($email) && !empty($un_id)) {
            if (!$db->checkLoginUnID($email, $un_id)){
                $response["result"] = "fail";                
            } else {  
            if (!$db -> deleteOrder($id)) {
                $response["result"] = "fail";
            } else {
                $response["result"] = "success";
            }}   
            return json_encode($response, JSON_UNESCAPED_UNICODE);            
        } else {
            return $this->getMsgParamNotEmpty();
        }
        
    }
    
    public function reserveOrder($id, $email, $un_id)
    {
        $db = $this->db;
        
        if (!empty($email) && !empty($un_id)) {
            
            $id_user = $db -> getID($email, $un_id);
            
            if (!$id_user){
                $response["result"] = "fail";
            } else {
            
            if (!$db -> reserveOrder($id, $id_user)) {
                $response["result"] = "fail";
            } else {
                $response["result"] = "success";
            }
            
            return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            }
            
        } else {
            return $this->getMsgParamNotEmpty();
        }
    }
    
    public function findReserveOrders($email, $un_id)
    {
        $db = $this->db;
        
        if (!empty($email) && !empty($un_id)) {
            
            $id = $db->getID($email, $un_id);
            
            if (!$id) {
                $response["result"] = "fail";
            } else {
                $orders = $db->findReserveOrders($id);
                
                if (!$orders) {
                    $response["result"] = "fail";
                    $response["message"] = "Забронированных поездок не найдено";
                } else {
                    $response["result"] = "success";
                    $response["orders"] = $orders;
                }
            }
            
            return json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } else {
            return $this->getMsgParamNotEmpty();
        }
    }
    
    public function deleteReserveOrder($id, $email, $un_id)
    {
        $db = $this->db;
        
        if (!empty($id) && !empty($email) && !empty($un_id)) {
            $id_user = $db->getID($email, $un_id);            
            if (!$id_user){
                $response["result"] = "fail";                
            } else {  
            if (!$db -> deleteReserveOrder($id, $id_user)) {
                $response["result"] = "fail";
            } else {
                $response["result"] = "success";
            }}   
            return json_encode($response, JSON_UNESCAPED_UNICODE);            
        } else {
            return $this->getMsgParamNotEmpty();
        }
        
    }
    
    public function sendMessage($message)
    {
        $to = 'Simraki@mail.ru';
        $theme = 'Shuttle Car Users';
        $message = wordwrap($message, 70, "\r\n");
        $headers = 'From: Shuttle Car APP';
        
        $mail = mail($to, $theme, $message, $headers);
        if (!$mail) {
            $response["result"] = "fail";
        } else {
            $response["result"] = "success";
        }        
        return json_encode($response, JSON_UNESCAPED_UNICODE); 
    }
    
    
    
    
    
    public function findUserTel($tel)
    {
        $db = $this->db;
        
        if (!empty($tel)) {
            
            $user = $db->findUserTel($tel);
            
            if (!$user) {
                
                $response["result"]  = "fail";
                $response["message"] = "Пользователь не найден или не является водителем";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            } else {
                $response["result"] = "success";
                $response["user"]   = $user;
            }
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            return $this->getMsgParamNotEmpty();
        }
    } 
    
    public function isEmailValid($email)
    {        
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public function getMsgParamNotEmpty()
    {
        $response["result"]  = "fail";
        $response["message"] = "Поля пусты";
        return json_encode($response, JSON_UNESCAPED_UNICODE);
        
    }
    
    public function getMsgInvalidParam()
    {
        
        $response["result"]  = "fail";
        $response["message"] = "Неверные параметры";
        return json_encode($response, JSON_UNESCAPED_UNICODE);
        
    }
    
    public function getMsgInvalidEmail()
    {
        
        $response["result"]  = "fail";
        $response["message"] = "Неверный Email";
        return json_encode($response, JSON_UNESCAPED_UNICODE);
        
    }    
    
    private function getImage($image, $file)
    {
        $ifp = fopen($file, "wb");
        
        $data = explode(',', $image);
        
        fwrite($ifp, base64_decode($data[0]));
        fclose($ifp);
        
        return $file;
    }
    
    function imageResize($src, $dst, $width, $height, $crop=0){
 
    if(!($info = @getimagesize($src)))
        return false;
 
    $w = $info[0];
    $h = $info[1];
    $type = substr($info['mime'], 6);
 
    $func = 'imagecreatefrom' . $type;
 
    if(!function_exists($func))
        return false;
    $img = $func($src);
 
        if($w < $width && $h < $height)
            return false; 
        $ratio = min($width/$w, $height/$h);
        $width = $w * $ratio;
        $height = $h * $ratio;
        $x = 0;
    
 
    $new = imagecreatetruecolor($width, $height);
        imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
        imagealphablending($new, false);
        imagesavealpha($new, true);
    imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);
 
    $save = 'image' . $type;
 
    return $save($new, $dst);
}

}
