<?php
class DBOperations
{    
    private $host = '127.0.0.1';
    private $user = 'root';
    private $db = 'shuttlecardb';
    private $pass = '';
    private $conn;
    
    public function __construct()
    {
        
        $this->conn = new PDO("mysql:dbname=" . $this->db . ";host=" . $this->host, $this->user, $this->pass);
        $this->conn->query("SET NAMES 'utf-8'");
        $this->conn->query("SET CHARACTER SET 'utf8'");
        
    }
    
    
    public function register($name, $email, $password)
    {
        
        $un_id              = uniqid('', true);
        $encrypted_password = $this->getHash($password);
        
        $uiu = mt_rand();
        
        while (strlen($uiu) > 128) {
            $uiu = mt_rand();
        }
        
        $sql = 'INSERT INTO users SET un_id =:un_id, name =:name, email =:email,en_p =:en_p, uiu =:uiu';
        
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            'un_id' => $un_id,
            ':name' => $name,
            ':email' => $email,
            ':en_p' => $encrypted_password,
            ':uiu' => $uiu
        ));
        
        if ($query) {
            
            return true;
            
        } else {
            
            return false;
            
        }
    }
    
    public function login($email, $password)
    {
        $sql   = 'SELECT * FROM users WHERE email = :email';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':email' => $email
        ));
        $data                  = $query->fetchObject();
        $db_encrypted_password = $data->en_p;
        
        if ($this->verifyHash($password . "621317", $db_encrypted_password)) {
            
            $id = $data->id_user;
            
            
            if (file_exists("image_person/$id.png")) {
                $image_person = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id.png";
            } else {
                $image_person = null;
                
            }if (file_exists("image_car/$id.png")) {
                $image_car = 'http://192.168.1.60' . "/shuttlecar/" . "image_car/$id.png";
            } else {
                $image_car = null;
            }
            
            $user["id"]   = $id;
            $user["un_id"]   = $data->un_id;
            $user["email"]   = $data->email;
            $user["name"]    = $data->name;
            $user["image_person"] = $image_person;
            $user["uiu"]     = $data->uiu;
            $user["image_car"] = $image_car;
            $user["car_brand"] = $data->car_brand;
            $user["car_model"] = $data->car_model;
            $user["tel"]     = $data->tel;
            return $user;
        } else {
            return false;
        }
        
    }
    
    public function changePassword($email, $password)
    {
        
        
        $encrypted_password = $this->getHash($password);
        
        $sql   = 'UPDATE users SET en_p = :encrypted_password WHERE email = :email';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':email' => $email,
            ':encrypted_password' => $encrypted_password
        ));
        
        if ($query) {
            
            return true;
            
        } else {
            
            return false;
            
        }
        
    }
    
    public function changeProfile($email, $un_id, $name, $tel, $car_brand, $car_model)
    {
        if (empty($car_brand)) {
            $car_brand = null;
        }
        if (empty($car_model)) {
            $car_model = null;
        }
        if (empty($tel)) {
            $tel = null;
        }
        
        $sql   = 'UPDATE users SET name = :name, car_brand = :car_brand, car_model= :car_model, tel = :tel WHERE email = :email AND un_id = :un_id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':name' => $name,
            ':tel' => $tel,
            ':car_brand' => $car_brand,
            ':car_model' => $car_model,
            ':email' => $email,
            ':un_id' => $un_id
        ));
        
        if ($query) {
            return true;
        } else {
            return false;
        }
        
    }  
    
    public function getRating($email, $un_id)
    {
        $sql   = 'SELECT rating FROM users WHERE email = :email AND un_id = :un_id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':email' => $email,
            ':un_id' => $un_id
        ));
        $data = $query->fetchObject();
        
        $sql_count   = 'SELECT COUNT(*) FROM users WHERE  email = :email AND un_id = :un_id';
        $query_count = $this->conn->prepare($sql_count);
        $query_count->execute(array(
            ':email' => $email,
            ':un_id' => $un_id
        ));
        $count = $query_count->fetchColumn();
        
        if ($count == 1) {
            return $data->rating;
        } else {
            return false;
            
        }
    }
    
    public function addRating($tel, $email, $un_id, $rating)
    {
        $sql   = 'SELECT rating FROM users WHERE tel = :tel';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':tel' => $tel
        ));
        $data = $query->fetchObject();
        
        $sql_count   = 'SELECT COUNT(*) FROM users WHERE tel = :tel';
        $query_count = $this->conn->prepare($sql_count);
        $query_count->execute(array(
            ':tel' => $tel
        ));
        $count = $query_count->fetchColumn();
        
        if ($count == 1) {
            
            $rat = $this->getRat_choose($email, $un_id);
            
            if (in_array($tel, $rat)) {
                return false;
            } else {
                $rating_old = $data->rating;
                
                $rating = ($rating_old + $rating) / 2;
                $rating = ceil($rating / 0.5) * 0.5;
                if ($rating > 5) {
                    $rating = 5;
                } else if ($rating < 0) {
                    $rating = 0;
                }
                
                $sql   = 'UPDATE users SET rating = :rating WHERE tel = :tel';
                $query = $this->conn->prepare($sql);
                $query->execute(array(
                    ':rating' => $rating,
                    ':tel' => $tel
                ));
                
                array_push($rat, $tel);
                $rat = serialize($rat);
                
                $sql   = 'UPDATE users SET rat_choose = :rat_choose WHERE email = :email AND un_id = :un_id';
                $query = $this->conn->prepare($sql);
                $query->execute(array(
                    ':rat_choose' => $rat,
                    ':email' => $email,
                    ':un_id' => $un_id
                ));
                if ($query) {
                    return true;
                } else {
                    return false;
                    
                }
            }
        } else {
            return false;
        }
        
    }
    
    
    public function addOrder($pdis, $pdel, $places, $time, $date, $place, $lugg, $id)
    {  
        
        if (count($places) != 0) {
            
            for ($i = 1; $i <= 3; $i++) {                
                if (!isset($places[$i])) {
                    $places[$i] = null;                    
                }        
            }
            $inter_one = $this->placeToID($places[1]);
            $inter_two = $this->placeToID($places[2]);
            $inter_three = $this->placeToID($places[3]);
        }
        if (isset($inter_one) || isset($inter_two) || isset($inter_three)) {
            if (!$inter_one && !is_int($inter_one) && $inter_one !== null) {
                return false;
            } else if (!$inter_two && !is_int($inter_two) &&  $inter_two !== null) {
                return false;            
            } else if (!$inter_three && !is_int($inter_three) &&  $inter_three !== null) {
                return false;            
            } else {

              $sql = 'INSERT INTO orders SET pdis =:pdis, inter_one =:inter_one, inter_two =:inter_two,
              inter_three =:inter_three, pdel =:pdel,time =:time, date =:date, count_place =:place, driver =:id, luggage =:luggage';

                $query = $this->conn->prepare($sql);
                $query->execute(array(
                    ':pdis' => $pdis,
                    ':inter_one' => $inter_one,
                    ':inter_two' => $inter_two,
                    ':inter_three' => $inter_three,
                    ':pdel' => $pdel,
                    ':time' => $time,
                    ':date' => $date,
                    ':place' => $place,
                    ':luggage' => $lugg,
                    ':id' => $id
                ));

                if ($query) {
                    return true;
                } else {
                    return false;
                }
            }
        }else{

          $sql = 'INSERT INTO orders SET pdis =:pdis, pdel =:pdel,time =:time, date =:date, count_place =:place, driver =:id, luggage =:luggage';

            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':pdis' => $pdis,
                ':pdel' => $pdel,
                ':time' => $time,
                ':date' => $date,
                ':place' => $place,
                ':luggage' => $lugg,
                ':id' => $id
            ));

            if ($query) {
                return true;
            } else {
                return false;
            }
            
        }
    }
    
    public function findOrder($pdis, $pdel, $time, $date, $place, $tel)
    {
        if (empty($tel)) {
            $sql   = 'SELECT * FROM orders AS o LEFT JOIN users AS u ON o.driver = u.id_user WHERE :pdis IN (o.pdis, o.inter_one, o.inter_two, o.inter_three) AND :pdel IN (o.inter_one, o.inter_two, o.inter_three, o.pdel) AND o.date = :date 
        AND DATE_ADD(STR_TO_DATE(:time, \'%T\'), INTERVAL 1 HOUR) >= o.time
        AND DATE_SUB(STR_TO_DATE(:time, \'%T\'), INTERVAL 1 HOUR) <= o.time
        AND o.count_place >= :place AND o.count_pass < o.count_place';
            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':pdis' => $pdis,
                ':pdel' => $pdel,
                ':time' => $time,
                ':date' => $date,
                ':place' => $place
            ));
            
        } else {
            $sql   = 'SELECT * FROM orders AS o LEFT JOIN users AS u ON o.driver = u.id_user WHERE :pdis IN (o.pdis, o.inter_one, o.inter_two, o.inter_three) AND :pdel IN (o.inter_one, o.inter_two, o.inter_three, o.pdel) AND o.date = :date 
        AND DATE_ADD(STR_TO_DATE(:time, \'%T\'), INTERVAL 1 HOUR) >= o.time
        AND DATE_SUB(STR_TO_DATE(:time, \'%T\'), INTERVAL 1 HOUR) <= o.time
        AND o.count_place >= :place AND u.tel != :tel AND o.count_pass < o.count_place';
            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':pdis' => $pdis,
                ':pdel' => $pdel,
                ':time' => $time,
                ':date' => $date,
                ':place' => $place,
                ':tel' => $tel
            ));
            
        }
        
        if ($query) {
            $i = 0;
            $pdis = $this->idToPlace($pdis);
            $pdel = $this->idToPlace($pdel);
            while ($data = $query->fetch()) {
                
                    if(!empty($data['id_user']) && !empty($data['name']) && !empty($data['tel']) && !empty($data['id_order']) && !empty($data['date']) && !empty($data['count_place'])) {

                    $id   = $data['id_user'];                                
                    $lugg = $this->idToLugg_size($data['luggage']);

                    if (file_exists("image_person/$id.png")) {
                        $image_person = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id.png";
                    } else {
                        $image_person = null;
                    }
                        
                    if (file_exists("image_car/$id.png")) {
                        $image_car = 'http://192.168.1.60' . "/shuttlecar/" . "image_car/$id.png";
                    } else {
                        $image_car = null;
                    }                

                    
                    $places = array($this->idToPlace($data['pdis']));
                        
                    if (!empty($data['inter_one'])) {
                        array_push($places, $this->idToPlace($data['inter_one']));
                    }
                        
                    if (!empty($data['inter_two'])) {
                        array_push($places, $this->idToPlace($data['inter_two']));
                    }
                        
                    if (!empty($data['inter_three'])) {
                        array_push($places, $this->idToPlace($data['inter_three']));
                    }
                        
                    array_push($places, $this->idToPlace($data['pdel']));
                        $a = 0;

                    while ($temp_array_name = current($places)) {
                        
                        if ($temp_array_name == $pdis) {
                            $key_pdis = key($places);
                        }
                        if ($temp_array_name == $pdel) {
                            $key_pdel = key($places);
                        }
                        next($places);
                    }
                        
                    if ($key_pdis < $key_pdel) {
                            $pass_array = array();
                        
                        if (!empty($data['passengers'])) {
                            $passengers = unserialize($data['passengers']);         

                            $sql_pass = 'SELECT id_user, name FROM users WHERE id_user IN (' . implode(',', $passengers) . ')';
                            $query_pass = $this->conn->prepare($sql_pass);
                            $query_pass->execute(array());   

                            while($data_pass = $query_pass->fetch()){
                                $id_user = $data_pass['id_user'];    
                                if (file_exists("image_person/$id_user.png")) {
                                    $image_pass = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id_user.png";
                                } else {
                                    $image_pass = null;
                                }
                                $pass['id'] = $id_user;
                                $pass['name'] = $data_pass['name'];   
                                $pass['image_person'] = $image_pass;   

                                $pass_array[$a] = $pass;
                                $a++;
                            }
                        }
                        
                        $user["name"]    = $data['name'];
                        $user["rating"]  = $data['rating'];
                        $user["image_person"]     = $image_person;
                        $user["image_car"]     = $image_car;
                        $user["car_brand"] = $data['car_brand'];
                        $user["car_model"] = $data['car_model'];
                        $user["tel"]     = $data['tel'];


                        $order["id"]     = $data['id_order'];
                        $order["places"] = $places;
                        $order["time"]   = $this->toTime($data['time']);
                        $order["count_place"]  = $data['count_place'];
                        $order["count_pass"]  = $data['count_pass'];
                        $order["lugg"]   = $lugg;
                        $order["driver"] = $user;
                        $order["passengers"] = $pass_array;

                        $orders[$i] = $order;
                        $i++;
                        }
                    }else{
                        return false;
                    }
            }
            
            if (empty($orders)) {
                return false;
            } else {
                return $orders;
            }
        } else {
            return false;
        }
    }
    
    public function findMyOrders($id)
    {
        $sql   = 'SELECT * FROM orders WHERE driver = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id
        ));
        
        if ($query) {
            $i = 0;
            while ($data = $query->fetch()) {                
                if(!empty($data['id_order']) && !empty($data['pdis']) && !empty($data['pdel'])
                   && !empty($data['date']) && !empty($data['count_place'])){
                
                $lugg = $this->idToLugg_size($data['luggage']);
                    
                    $places = array($this->idToPlace($data['pdis']));
                        
                    if (!empty($data['inter_one'])) {
                        array_push($places, $this->idToPlace($data['inter_one']));
                    }
                        
                    if (!empty($data['inter_two'])) {
                        array_push($places, $this->idToPlace($data['inter_two']));
                    }
                        
                    if (!empty($data['inter_three'])) {
                        array_push($places, $this->idToPlace($data['inter_three']));
                    }
                        
                    array_push($places, $this->idToPlace($data['pdel']));
                        $a = 0;
                    
                if (!empty($data['passengers'])) {
                            $passengers = unserialize($data['passengers']);         

                            $sql_pass = 'SELECT id_user, name FROM users WHERE id_user IN (' . implode(',', $passengers) . ')';
                            $query_pass = $this->conn->prepare($sql_pass);
                            $query_pass->execute(array());   

                            while($data_pass = $query_pass->fetch()){
                                $id_user = $data_pass['id_user'];    
                                if (file_exists("image_person/$id_user.png")) {
                                    $image_pass = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id_user.png";
                                } else {
                                    $image_pass = null;
                                }
                                $pass['id'] = $id_user;
                                $pass['name'] = $data_pass['name'];   
                                $pass['image_person'] = $image_pass;   

                                $pass_array[$a] = $pass;
                                $a++;
                            }
                        }
                    
                $order["id"]    = $data['id_order'];
                $order["places"]  = $places;
                $order["time"]  = $this->toTime($data['time']);
                $order["date"]  = $data['date'];
                $order["count_place"] = $data['count_place'];
                $order["count_pass"] = $data['count_pass'];
                $order["lugg"]  = $lugg;
                $order["passengers"] = $pass_array;
                
                $orders[$i] = $order;
                $i++;                
                } else {
                    return false;
                }
            }
            if (empty($orders)) {
                return null;
            } else {
                return $orders;
            }    
        } else {
            return false;
        }
    }
    
    public function deleteOrder($id)
    {
        $sql   = 'DELETE FROM orders WHERE id_order = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id
        ));
        
        if ($query) {
            return true;
        } else {
            return false;
        }
    }
    
    public function reserveOrder($id_order, $id_user)
    {
        $sql_user   = 'SELECT reserve_orders FROM users WHERE id_user = :id_user';
        $query_user = $this->conn->prepare($sql_user);
        $query_user->execute(array(
            ':id_user' => $id_user
        ));
        $data_user = $query_user->fetchObject();
        
        $sql_order   = 'SELECT count_pass, count_place, passengers FROM orders WHERE id_order = :id_order';
        $query_order = $this->conn->prepare($sql_order);
        $query_order->execute(array(
            ':id_order' => $id_order
        ));
        $data_order = $query_order->fetchObject();
        
        if ($query_user && $query_order) {
            if($data_order->count_pass < $data_order->count_place){
            $array_id_order = [$id_order];
            $array_id_user = [$id_user];
            
            $passengers = unserialize($data_order->passengers);            
            if (empty($passengers)) {
                $passengers = array();
            }
            if(!in_array($id_user,$passengers)) {
            $array_id_user = array_diff($array_id_user, [0]);
            $array_id_user = array_diff($array_id_user, $passengers);
            $passengers = array_merge($passengers, $array_id_user);
            
            $reserve_orders = unserialize($data_user->reserve_orders);            
            if (empty($reserve_orders)) {
                $reserve_orders = array();
            }
            $array_id_order = array_diff($array_id_order, [0]);
            $array_id_order = array_diff($array_id_order, $reserve_orders);
            $reserve_orders = array_merge($reserve_orders, $array_id_order);
            
            if ($passengers != null) {
                $passengers = serialize($passengers);
            } else {
                $passengers = null;
            }            
            if ($reserve_orders != null) {
                $reserve_orders = serialize($reserve_orders);
            } else {
                $reserve_orders = null;
            }
            
            $count = $data_order->count_pass;
            $count++;
            
            $sql_order   = 'UPDATE orders SET passengers =:passengers, count_pass = :count WHERE id_order = :id_order';
            $query_order = $this->conn->prepare($sql_order);
            $query_order->execute(array(
                ':passengers' => $passengers,
                ':count' => $count,
                ':id_order' => $id_order
            ));            
            $sql_user   = 'UPDATE users SET reserve_orders =:reserve_orders WHERE id_user = :id_user';
            $query_user = $this->conn->prepare($sql_user);
            $query_user->execute(array(
                ':reserve_orders' => $reserve_orders,
                ':id_user' => $id_user
            ));
            if ($query_user && $query_order) {
                return true;
            } else {
                return false;
            }
        } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
            return false;
        }
        
    }
    
    public function findReserveOrders($id)
    {
        $sql_user   = 'SELECT reserve_orders FROM users WHERE id_user = :id';
        $query_user = $this->conn->prepare($sql_user);
        $query_user->execute(array(
            ':id' => $id
        ));
        $data_user = $query_user->fetchObject();
        
        $reserve_orders = unserialize($data_user->reserve_orders);
        
        if(!empty($reserve_orders)) {
            $sql   = 'SELECT * FROM orders AS o LEFT JOIN users AS u ON o.driver = u.id_user WHERE id_order IN (' . implode(',', $reserve_orders) . ')';
            $query = $this->conn->prepare($sql);
            $query->execute(array());  
        } else {
            $query = false;
        }
        if ($query) {
            $i = 0;
            while ($data = $query->fetch()) {    
                if(!empty($data['id_user']) && !empty($data['name']) && !empty($data['tel']) && !empty($data['id_order']) && !empty($data['date']) && !empty($data['count_place'])) {

                    $id   = $data['id_user'];
                    $lugg = $this->idToLugg_size($data['luggage']);

                    if (file_exists("image_person/$id.png")) {
                        $image_person = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id.png";
                    } else {
                        $image_person = null;
                    }
                        
                    if (file_exists("image_car/$id.png")) {
                        $image_car = 'http://192.168.1.60' . "/shuttlecar/" . "image_car/$id.png";
                    } else {
                        $image_car = null;
                    }                

                    
                    $places = array($this->idToPlace($data['pdis']));
                        
                    if (!empty($data['inter_one'])) {
                        array_push($places, $this->idToPlace($data['inter_one']));
                    }
                        
                    if (!empty($data['inter_two'])) {
                        array_push($places, $this->idToPlace($data['inter_two']));
                    }
                        
                    if (!empty($data['inter_three'])) {
                        array_push($places, $this->idToPlace($data['inter_three']));
                    }
                        
                    array_push($places, $this->idToPlace($data['pdel']));
                        
                             $pass_array = array();
                        
                        if (!empty($data['passengers'])) {
                            $passengers = unserialize($data['passengers']);         

                            $sql_pass = 'SELECT id_user, name FROM users WHERE id_user IN (' . implode(',', $passengers) . ')';
                            $query_pass = $this->conn->prepare($sql_pass);
                            $query_pass->execute(array());   
                            
                            $a = 0;

                            while($data_pass = $query_pass->fetch()){
                                $id_user = $data_pass['id_user'];    
                                if (file_exists("image_person/$id_user.png")) {
                                    $image_pass = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id_user.png";
                                } else {
                                    $image_pass = null;
                                }
                                $pass['id'] = $id_user;
                                $pass['name'] = $data_pass['name'];   
                                $pass['image_person'] = $image_pass;   

                                $pass_array[$a] = $pass;
                                $a++;
                            }
                        }
                    
                    $user["name"]    = $data['name'];
                    $user["rating"]  = $data['rating'];
                    $user["image_person"]     = $image_person;
                    $user["image_car"]     = $image_car;
                    $user["car_brand"] = $data['car_brand'];
                    $user["car_model"] = $data['car_model'];
                    $user["tel"]     = $data['tel'];

                    $order["id"]     = $data['id_order'];
                    $order["places"] = $places;
                    $order["time"]   = $this->toTime($data['time']);
                    $order["date"]   = $data['date'];
                    $order["count_place"]  = $data['count_place'];
                    $order["count_pass"]  = $data['count_pass'];
                    $order["lugg"]   = $lugg;
                    $order["driver"] = $user;
                    $order["passengers"] = $pass_array;

                    $orders[$i] = $order;
                    $i++;
                        
                    }else{
                        return false;
                    }
            }
            if (empty($orders)) {
                return null;
            } else {
                return $orders;
            }    
        } else {
            return false;
        }
    }
    
    public function deleteReserveOrder($id, $id_user)
    {
        $sql_user   = 'SELECT reserve_orders FROM users WHERE id_user = :id';
        $query_user = $this->conn->prepare($sql_user);
        $query_user->execute(array(
            ':id' => $id_user
        ));
        $data_user = $query_user->fetchObject();
        
        $sql_order   = 'SELECT count_pass, passengers FROM orders WHERE id_order = :id';
        $query_order = $this->conn->prepare($sql_order);
        $query_order->execute(array(
            ':id' => $id
        ));
        $data_order = $query_order->fetchObject();
        
        $reserve_orders = unserialize($data_user->reserve_orders);
        $passengers = unserialize($data_order->passengers);
        
        if(in_array($id_user,$passengers)) {
            $reserve_orders = array_diff($reserve_orders, [$id]);        
            $passengers = array_diff($passengers, [$id_user]);  
            
            if(empty($passengers)){
                $passengers = null;
            } else {
                $passengers = serialize($passengers);                
            }
            if(empty($reserve_orders)){
                $reserve_orders = null;
            } else {
                $reserve_orders = serialize($reserve_orders);                
            }
            
            $count = $data_order->count_pass;
            $count--;
        
            $sql_user   = 'UPDATE users SET reserve_orders =:reserve_orders WHERE id_user = :id';
            $query_user = $this->conn->prepare($sql_user);
            $query_user->execute(array(
                ':reserve_orders' => $reserve_orders,
                ':id' => $id_user
            ));
            $sql_order   = 'UPDATE orders SET count_pass =:count_pass, passengers =:passengers WHERE id_order = :id';
            $query_order = $this->conn->prepare($sql_order);
            $query_order->execute(array(
                ':count_pass' => $count,
                ':passengers' => $passengers,
                ':id' => $id
            ));

            if ($query_user && $query_order) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    public function checkUserExist($email)
    {
        
        $sql   = 'SELECT COUNT(*) from users WHERE email =:email';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            'email' => $email
        ));
        
        if ($query) {
            
            $row_count = $query->fetchColumn();
            
            if ($row_count == 0) {
                
                return false;
                
            } else {
                
                return true;
                
            }
        } else {
            
            return false;
        }
    }
    
    public function getHash($password)
    {
        
        $salt      = sha1(mt_rand());
        $salt      = substr($salt, 0, 10);
        $encrypted = password_hash($password . "621317", PASSWORD_BCRYPT, ['cost' => 14]);
        $hash      = $encrypted;        
        return $hash;
        
    }
    
    public function verifyHash($password, $hash)
    {
        
        return password_verify($password, $hash);
    }
    
    public function checkLoginUnID($email, $un_id)
    {
        $sql   = 'SELECT COUNT(*) FROM users WHERE email = :email AND un_id = :un_id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':email' => $email,
            ':un_id' => $un_id
        ));
        $count = $query->fetchColumn();
        
        if ($count == 1) {
            return true;
        } else {
            return false;            
        }
        
    }
    
    public function getID($email, $un_id)
    {
        $sql   = 'SELECT id_user FROM users WHERE email = :email AND un_id = :un_id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':email' => $email,
            ':un_id' => $un_id
        ));
        $data = $query->fetchObject();
        
        $sql_count   = 'SELECT COUNT(*) FROM users WHERE  email = :email AND un_id = :un_id';
        $query_count = $this->conn->prepare($sql_count);
        $query_count->execute(array(
            ':email' => $email,
            ':un_id' => $un_id
        ));
        $count = $query_count->fetchColumn();
        
        if ($count == 1) {
            return $data->id_user;
        } else {
            return false;
            
        }
    }
    
    public function findUserTel($tel)
    {
        $sql   = 'SELECT name, id_user, tel FROM users WHERE tel = :tel';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':tel' => $tel
        ));
        $data = $query->fetchObject();
        
        $sql_count   = 'SELECT COUNT(*) FROM users WHERE  tel = :tel';
        $query_count = $this->conn->prepare($sql_count);
        $query_count->execute(array(
            ':tel' => $tel
        ));
        $count = $query_count->fetchColumn();
        
        if ($count == 1 && !empty($data->tel)) {
            $id = $data->id_user;            
            
            if (file_exists("image_person/$id.png")) {
                $image = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id.png";
            } else {
                $image = null;
            }
            
            $user["name"]    = $data->name;
            $user["image_person"]     = $image;
            return $user;
        } else {
            return false;
            
        }
    }
    
    private function getRat_choose($email, $un_id)
    {
        $sql   = 'SELECT rat_choose FROM users WHERE email = :email AND un_id = :un_id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':email' => $email,
            ':un_id' => $un_id
        ));
        $data = $query->fetchObject();
        
        if ($query) {
            
            $rating_choose = unserialize($data->rat_choose);
            if (is_array($rating_choose)) {
                return $rating_choose;
            } else if (empty($rating_choose)) {
                return $rating_choose = array();
            }
            
        } else {
            return false;
            
        }
    }

    public function checkOrder($pdis, $pdel, $time, $date, $id)
    {
        $sql   = 'SELECT COUNT(*) FROM orders WHERE pdis = :pdis AND pdel = :pdel AND time = :time AND date = :date AND driver = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':pdis' => $pdis,
            ':pdel' => $pdel,
            ':time' => $time,
            ':date' => $date,
            ':id' => $id
        ));
        $count = $query->fetchColumn();
        
        if (empty($count)) {
            return true;
        } else {
            return false;
            
        }
    }
    
    public function lugg_sizeToID($lugg)
    {
        $sql   = 'SELECT id FROM lugg_size WHERE lugg = :lugg';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':lugg' => $lugg
        ));
        $data = $query->fetchObject();
        
        return $data->id;
    }
    
    public function idToLugg_size($id)
    {
        $sql   = 'SELECT lugg FROM lugg_size WHERE id = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id
        ));
        $data = $query->fetchObject();
        
        return $data->lugg;
    }
    
    public function placeToID($place)
    {
        if($place != null) {
        
        $data = null;
        $count = null;
            
        $sql   = 'SELECT id FROM place WHERE place = :place';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':place' => $place
        ));
        $data = $query->fetchObject();
        
        $sql_count   = 'SELECT COUNT(*) FROM place WHERE place = :place';
        $query_count = $this->conn->prepare($sql_count);
        $query_count->execute(array(
            ':place' => $place
        ));
        $count = $query_count->fetchColumn();
        
        if ($count == 0) {   
            return false;        
        } else {
            return $data->id; 
        }
        }
    }
    
    public function idToPlace($id)
    {
        if(!empty($id)) {
        $sql   = 'SELECT place FROM place WHERE id = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id
        ));
        $data = $query->fetchObject();
        
        return $data->place;
        }
    }
    
    public function toTime($time)
    {
        $form_time = DateTime::createFromFormat('H:i:s', $time);
        $time = $form_time->format('H:i');        
        return $time;
    }
}
