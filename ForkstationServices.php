<?php
//include_once("vendor/paragonie/random_compat/lib/random.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once("vendor/autoload.php");
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
$address = $_POST['address'];
    //$address = "48 Southwest 12th Street, Miami, FL, USA";

    $distance = 5; //in kilometers

    function getConnection()
    {
        $username = 'pruebasj_root';
        $password = 'Admin98765!';
        $host = 'localhost';
        $db = 'pruebasj_fork';

        try{
            $connection = new PDO("mysql:dbname=$db;host=$host", $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        } catch (PDOException $e) {
            die("ERROR TRYING TO CONNECT DATABASE: " . $e->getMessage());
        }

        return $connection;
    }

    $op = $_REQUEST['op'];
    $restaurantInfo = null;

    switch ($op) {

        case 'GetRestaurants':

        function getNearLocations($latitude, $longitude, $distance = 1)
        {

                $polygon_radius = $distance / 100; // convert KM to centimeters

                $sql = "SET @hereami = ST_GeomFromText('POINT($longitude $latitude)');";
                $sql2 = "SET @pt_strategy = ST_Buffer_Strategy('point_circle', 8);";
                $sql3 = "SET @polygon = (SELECT ST_AsText(ST_Buffer(@hereami, $polygon_radius, @pt_strategy)));";
                $sql4 = "SELECT
                id,
                name,
                contact,
                phone_rest,
                phone_con,
                address,
                apt,
                email,
                zipcode,
                city,
                state,
                country,
                idasesor,
                orders_email,
                description,
                keywords,
                delivery_area,
                web,
                YelpID,
                orders_fax,
                orders_fax,
                restaurant_chain,
                distance,
                fork_fee,
                citytax,
                rating,
                delivery,
                pickup,
                image,
                position,
                min_order,
                priority,
                cash,
                visa,
                master,
                american,
                discover,
                cashcupon,
                status,
                terms_condition,
                note,
                distance AS delivery_distance,
                ST_Y(location) AS longitude,
                ST_X(location) AS latitude,
                ST_Distance_Sphere(@hereami, location) AS distance
                FROM
                restaurants
                WHERE
                MBRContains (
                ST_GeomFromText(@polygon),
                location
                )
                HAVING
                distance < ($distance * 1000)
                ORDER BY
                distance ASC;";

                $restaurants = [];
                $i = 0;

                $conn = getConnection ();
                $stmt = $conn->query ($sql);
                $stmt = $conn->query ($sql2);
                $stmt = $conn->query ($sql3);
                $stmt = $conn->query ($sql4);
                $data = $stmt->fetchAll (PDO::FETCH_ASSOC);

                foreach ($data as $row) {

                    $restaurant_distance = round (number_format ($row['distance'], 3, '.', ''), 1);
                    $restaurant_delivery_area = $row['delivery_distance'];

                    if ($restaurant_distance >= ($restaurant_delivery_area * 1000))
                        continue;

                    $restaurants[] = $row;
                    $restaurants[$i]['miles'] = (String)round ($row['distance'] / 1609.344, 1);
                    $restaurants[$i]['kilometers'] = (String)round ($row['distance'] / 1000, 1);
                    $list = getDishesRestaurantFavorite($row["id"], $conn);
                    $restaurants[$i]["listdishes"] = $list;
                    $i++;
                }

                $data = [
                    'RestaurantOut' => $restaurants
                ];

                $restaurantInfo = $restaurants;
                return $restaurants;

            }

            $latitude = $_REQUEST['latitude'];
            $longitude = $_REQUEST['longitude'];

            //$data = getNearLocations ('25.802714', '-80.199134');
            $data = getNearLocations($latitude, $longitude);

            header ('Content-Type: application/json');
            echo json_encode ($data);

            break;

            case 'GetRestaurantMenu':

            $restaurantID = $_REQUEST['restaurant_id'];

            function getRestaurantMenu($restaurantID)
            {

                $sql = "SELECT * FROM restaurant_menu WHERE idrestaurant = $restaurantID";
                $sql2 = "SELECT
                id,
                name,
                contact,
                phone_rest,
                phone_con,
                address,
                apt,
                email,
                zipcode,
                city,
                state,
                country,
                idasesor,
                orders_email,
                description,
                keywords,
                delivery_area,
                web,
                YelpID,
                orders_fax,
                orders_fax,
                restaurant_chain,
                distance,
                fork_fee,
                citytax,
                rating,
                delivery,
                pickup,
                image,
                position,
                min_order,
                priority,
                cash,
                visa,
                master,
                american,
                discover,
                cashcupon,
                status,
                terms_condition,
                note,
                distance AS delivery_distance,
                ST_Y(location) AS longitude,
                ST_X(location) AS latitude
                FROM restaurants
                WHERE id = $restaurantID";

                $restaurantsMenus = [];
                $restaurantInfo = [];
                $i = 0;

                $conn = getConnection();
                $stmt = $conn->query($sql);
                $data = $stmt->fetchAll (PDO::FETCH_ASSOC);

                $stmt2 = $conn->query($sql2);
                $dataRestaurant = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                foreach ($data as $row) {
                    $list = getDishesRestaurantMenu($row["id"], $conn);
                    //$pCoupon = getDishesRestaurantMenu($row["id"], $conn);
                    foreach ($list as $key => $value) {
                        $properties = getPropertiesProduct($value["ProductID"], $conn);
                        foreach ($properties as $key2 => $value2) {
                            $properties[$key2]["ProductsPropertyValues"] = getMenuAdds($value2["id"], $conn);
                        }
                        $list[$key]["MenuProductsProperty"] = $properties;

                    }
                    $row["listdishes"] = $list;
                    $restaurantsMenus[] = $row;
                }

                foreach ($dataRestaurant as $row2) {
                    $list = getDishesRestaurantFavorite($row2["id"], $conn);
                    $pCoupon = getDishesRestaurantFavorite($row2["id"], $conn, 0, true);
                    foreach ($list as $key => $value) {
                        $properties = getPropertiesProduct($value["ProductID"], $conn);
                        foreach ($properties as $key2 => $value2) {
                            $properties[$key2]["ProductsPropertyValues"] = getMenuAdds($value2["id"], $conn);
                        }
                        $list[$key]["MenuProductsProperty"] = $properties;

                    }
                    $row2["listdishes"] = $list;
                    $row2["listCoupon"] = $pCoupon;
                    $restaurantInfo[] = $row2;
                }

                $data = [
                    'RestaurantMenuOut' => $restaurantsMenus,
                    'RestaurantOut' => $restaurantInfo
                ];

                return $data;

            }

            //$data = getRestaurantMenu(29);
            $data = getRestaurantMenu($restaurantID);
//var_dump($data);
            header ('Content-Type: application/json');
            echo json_encode($data);

            break;

            case 'ValidateUser':  
                
                $conn = getConnection ();
                $tokenface = $_REQUEST["FacebookToken"];
                if (!empty($_REQUEST["FacebookToken"])) {
                    $sql = "select id as usuID, users.UserID AS ClientID, oauth_identities.user_id As usuarioID, users.username AS UserName, users.password AS Password, users.email AS eMail, users.phone AS Cellphone, users.created_at As CreationDate,  users.id_profile from oauth_identities, users where access_token='$tokenface' and users.id=oauth_identities.user_id";    
                    $stmt = $conn->query ($sql);
                    $data = $stmt->fetchAll (PDO::FETCH_ASSOC);
                    if($stmt){
                        $_SESSION["_token"] = key_random(60);
                        $conn->query ("update users set remember_token = '".$_SESSION["_token"]."', updated_at = '".date('Y-m-d H:i:s')."' where id=".$data[0]["usuID"]);
                    }
                    $sql2 = "select concat_ws(', ', payments_profiles.last_name,  payments_profiles.first_name) As FullName, address AS Address from payments_profiles where payments_profiles.id_profile =".$data[0]["id_profile"]."";    
                    $stmt2 = $conn->query ($sql2);
                    $data2 = $stmt2->fetchAll (PDO::FETCH_ASSOC);
                    //echo json_encode(!isset($data2));
                    $paymentData = array(
                        "FullName" => "",
                        "FacebookID" => "",
                        "TwitterID" => "",
                        "Address" => "",
                    );
                    if(isset($data2)){
                        foreach ($data2[0] as $key => $value) {
                            $paymentData[$key] = $value;
                        }
                    }
                    $_SESSION["idUser"] = $data[0]["usuID"];
                    session_id ($_SESSION["_token"]);

                    $sessionR = array(
                        "SessionKey"=>$_SESSION["_token"],
                        "TimeExpiration"=>"",
                        "ClientID"=>$_SESSION["idUser"],
                    );
                    array_push($data, $paymentData);
                    $response = array("User" => $data[0], "Session" => $sessionR);
                    header ('Content-Type: application/json');
                    echo json_encode($response);
                    //echo json_encode($paymentData);

                }else{
                    /*
                    $sql = "select users.username AS UserName, users.password AS Password, concat_ws(', ', payments_profiles.last_name,  payments_profiles.first_name) As FullName, users.email AS eMail, oauth_identities.id AS FacebookID, payments_profiles.address, users.phone AS Cellphone, users.created_at from users, oauth_identities, payments_profiles where users.id=oauth_identities.user_id and oauth_identities.provider='facebook' and payments_profiles.id_profile = users.id_profile";
                    */
                    $email = $_REQUEST["mail"];
                    $password = $_REQUEST["password"];
                    $sql = "select id as usuID, users.UserID AS ClientID, users.username AS UserName, users.password AS Password, users.email AS eMail, users.phone AS Cellphone, users.created_at As CreationDate,  users.id_profile from users where users.email='$email' and users.password='$password'";
                    $stmt = $conn->query ($sql);
                    $data = $stmt->fetchAll (PDO::FETCH_ASSOC);
                    if($stmt){
                        $_SESSION["_token"] = key_random(60);
                        $conn->query ("update users set remember_token = '".$_SESSION["_token"]."', updated_at = '".date('Y-m-d H:i:s')."' where id=".$data[0]["usuID"]);
                    }
                    $sql2 = "select concat_ws(', ', payments_profiles.last_name,  payments_profiles.first_name) As FullName, address AS Address from payments_profiles where payments_profiles.id_profile =".$data[0]["id_profile"]."";    
                    $stmt2 = $conn->query ($sql2);
                    $data2 = $stmt2->fetchAll (PDO::FETCH_ASSOC);
                    //echo json_encode(!isset($data2));
                    $paymentData = array(
                        "FullName" => "",
                        "FacebookID" => "",
                        "TwitterID" => "",
                        "Address" => "",
                    );
                    if(isset($data2)){
                        foreach ($data2[0] as $key => $value) {
                            $paymentData[$key] = $value;
                        }
                    }
                    try {
                        
                    
                    $_SESSION["idUser"] = $data[0]["usuID"];

                    $sessionR = array(
                        "SessionKey"=>$_SESSION["_token"],
                        "TimeExpiration"=>"15",
                        "ClientID"=>$_SESSION["idUser"],
                    );
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                    array_push($data[0], $paymentData);
                    $response = array("User" => $data[0], "Session" => $sessionR);
                    header ('Content-Type: application/json');
                    echo json_encode($response);
                }
                /*
                echo json_encode(
                    array("User"=>array("ClientID"=>"1",
                        "UserName"=>"User Name",
                        "Password"=>"",
                        "FullName"=>"Full name",    // --
                        "eMail"=>"email@gmail.com",
                        "FacebookID"=>"",
                        "TwitterID"=>"",
                        "Address"=>"",
                        "Cellphone"=>"",
                        "CreationDate"=>"",
                    ), 
                    "Session"=>array(
                        "SessionKey"=>"key_s",
                        "TimeExpiration"=>"",
                        "ClientID"=>"",
                    ),
                    

                ));
                echo json_encode($data);
                */
                break;
            
            case 'GetOrder':
                $getID = $_REQUEST["orderid"];
                $conn = getConnection ();
                $oData = getOrderData($getID, $conn);
               // var_dump($productOrderData);
                //array_push($orderData[0]["ProductOrder"], $productOrderData[1] );
                if (!empty($oData)) {
                    $rData = getRestaurantData($oData["RestaurantID"], $conn);
                }
                header ('Content-Type: application/json');
                echo json_encode(array("Order" => $oData, "Restaurant"=> $rData));
                /*
                header ('Content-Type: application/json');
                echo json_encode(
                    array(
                        'order'=>array(
                            'OrderID'=>'1',
                            'OrderNum'=>'1100',
                            'RestaurantID'=>'1',
                            'ClientID'=>'12',
                            'TotalPriceOrder'=>'15000',
                            'Paid'=>'12',
                            'CreationDate'=>'',
                            'PaymentDate'=>'',
                            'PaymentType'=>'',
                            'BasePriceOrder'=>'',
                            'TaxOrder'=>'',
                            'Tip'=>'',
                            'DeliveryAddressStr'=>'',
                            'Cupon'=>'',
                            'DiscountValue'=>'',
                            'BasePriceOrderAferDiscount'=>'',
                            'TotalPriceOrderComplete'=>'',
                            'Schedule'=>'',
                            'OrderType'=>'',
                            'ProductOrder'=>[array( //order_details
                                'Quantity'=>'',
                                'ProductTotalValue'=>'',
                                'Product'=>array(
                                    'ProductID'=>'',
                                    'Name'=>'',
                                    'Description'=>'',
                                    'ProductImg'=>'',
                                    'Enable'=>'',
                                    'CategoryID'=>'',
                                    'Category'=>'',
                                    'CreationDate'=>'',
                                    'ProductOrder'=>'',
                                    'CategoryOrder'=>'',
                                    'Value'=>'',
                                    'TotalValue'=>'',
                                    'ProductPropertyCart'=>array(
                                        array(
                                            'ProductPropertyID'=>'',
                                            'ProductID'=>'',
                                            'FatherProductPropertyID'=>'',
                                            'Name'=>'',
                                            'PropertyType'=>'',
                                            'GroupingTypeID'=>'',
                                            'GroupingType'=>'',
                                            'PropertyValueCart'=>array(
                                                array(
                                                    'PropertyValueID'=>'',
                                                    'ProductPropertyID'=>'',
                                                    'ProductID'=>'',
                                                    'Label'=>'',
                                                    'Price'=>'',
                                                    'Cant'=>'',
                                                    'TotalPrice'=>'',

                                                ),
                                            ),
                                        ),
                                        array(
                                            'ProductPropertyID'=>'',
                                            'ProductID'=>'',
                                            'FatherProductPropertyID'=>'',
                                            'Name'=>'',
                                            'PropertyType'=>'',
                                            'GroupingTypeID'=>'',
                                            'GroupingType'=>'',
                                            'PropertyValueCart'=>array(
                                                array(
                                                    'PropertyValueID'=>'',
                                                    'ProductPropertyID'=>'',
                                                    'ProductID'=>'',
                                                    'Label'=>'',
                                                    'Price'=>'',
                                                    'Cant'=>'',
                                                    'TotalPrice'=>'',

                                                ),
                                            ),
                                        ),

                                    ),
                                ),

                            )],
                            'ReceiptLink'=>'',
                            'DeliveryFee'=>'',

                        ),
                        'Restaurant'=>array(
                            'RestaurantID'=>'',
                            'RestaurantChainID'=>'',
                            'Name'=>'',
                            'Description'=>'',
                            'Image'=>'',
                            'Tips'=>'', //
                            'DeliveryArea'=>'',
                            'DeliveryTime'=>'', // faltante
                            'OurKitchen'=>'', //
                            'Address'=>'',
                            'Phones'=>'',
                            'Rate'=>'',
                            'MinimumOrder'=>'', // faltante
                            'Tax'=>'', 
                            'DeliveryCost'=>'', // faltante
                            'Longitude'=>'',
                            'Latitude'=>'',
                            'Zip'=>'',
                            'Distance'=>'',
                            'Web'=>'',
                            'CreationDate'=>'',
                            'Delivery'=>'',
                            'Pickup'=>'',
                            'Enable'=>'', // faltante
                            'YelpId'=>'',
                            'Dividends_percent'=>'', // faltante
                            'Filters'=>array(
                                'Delivery'=>'',
                                'Pickup'=>'',
                                'FreeDelivery'=>'',
                                'OpenNow'=>'',
                                'HaveCoupons'=>'',

                            ),
                            'PaymentTypeOut'=>array(
                                array(
                                    'PaymentTypeID'=>'',
                                    'PaymentType'=>'',
                                    'Icon'=>'',
                                    'Enable'=>'',
                                ),
                            ),
                            'ScheduleOut'=>array(
                                'ScheduleID'=>'',                            
                                'Monday'=>'',                            
                                'Tuesday'=>'',                            
                                'Wednesday'=>'',                            
                                'Thursday'=>'',                            
                                'Friday'=>'',                            
                                'Saturday'=>'',                            
                                'Sunday'=>'',                            
                            ),
                        )
                    )
                );
                */
                break;
            case 'AddClientAddress':
                
                
                $SessionKey = $_REQUEST['SessionKey'];

                if(isLogin($SessionKey)){
                    $Address = $_REQUEST['Address'];
                    $Suit = $_REQUEST['Suit'];
                    $City = $_REQUEST['City'];
                    $State = $_REQUEST['State'];
                    $ZIPCode = $_REQUEST['ZIPCode'];
                    $CrossStreet = $_REQUEST['CrossStreet'];
                    $Phone = $_REQUEST['Phone'];
                    $AddressName = $_REQUEST['AddressName'];
                    $id = getIDByToken($SessionKey);
                    $conn = getConnection ();
                        $sql = "INSERT into direcciones_clientes (iduser, type_address, address, apt, zipcode, city, state, cross_street, phone, direcciones_clientes.default, created_at, updated_at) values ($id, '$AddressName', '$Address', '$Suit', '$ZIPCode', '$City', '$State', '$CrossStreet', '$Phone', 0, '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."')";
                        //echo $sql;
                        try {
                            $conn->beginTransaction();
                            $conn->exec($sql);
                            $conn->commit();
                            //echo $sql;
                        } catch (Exception $e) {
                          $conn->rollBack();
                          //echo "Failed: " . $e->getMessage();
                        }
                        //$stmt = $conn->query ($sql);
                        //$data1 = $stmt->fetchAll (PDO::FETCH_ASSOC);
                    $data = getAddress($id, $conn);
                    //$data["Success"] = $sql;
                }else{
                    $data = array("Success" => "false");
                }
               echo json_encode($data);


                break;

            case 'CreateShoppingCart':  //pendiente

                $sessionk = $_REQUEST["SessionKey"];
                $options = $_REQUEST["options"];
                $items = $_REQUEST["items"];

                $clientId = getIDByToken($sessionk);
                $sqlID = "select max(id) as id from orders";
                $conn = getConnection ();
                $stmt = $conn->query ($sqlID);
                $id = $stmt->fetchAll (PDO::FETCH_ASSOC);
               
                $idOrderNew = $id[0]["id"] + 1;
                $typeOrder = ['delivery' => 2, 'pickup' => 1];

                $total = 0;
                foreach ($items as $value) {
                    $total += $value["quantity"]*$value["productvalue"];
                }
                $sql1 = "INSERT into orders (id, order_type, idclient, orderNum, created_at, updated_at, base_price, total_price, estado, idrestaurant) values ($idOrderNew, 1, $clientId, '".(date('Ymdhms') + rand(1,999))."', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."', $total, $total, 0, ".$options["id"].")";
                try {
                    $conn->beginTransaction();
                    $conn->exec($sql1);
                    $conn->commit();
                    //echo $sql;
                } catch (Exception $e) {
                  $conn->rollBack();
                  //echo "Failed: " . $e->getMessage();
                }
                foreach ($items as $value) {
                    $sql2 = "INSERT into order_detail (idorder, type, idproduct, quantity, value, created_at, updated_at) values ($idOrderNew, 1, ".$value["productid"].", ".$value["quantity"].", ".$value["productvalue"].", '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."')";
                        //$id = $stmt->fetchAll (PDO::FETCH_ASSOC);
                    $exito = false;
                    try {
                        $conn->beginTransaction();
                        $conn->exec($sql2);
                        $idLastDishes = $conn->lastInsertId();
                        $conn->commit();
                        $exito = true;
                        //echo $sql;
                    } catch (Exception $e) {
                        $conn->rollBack();
                      //echo "Failed: " . $e->getMessage();
                        $exito = false;
                    }
                    if($exito){
                        foreach ($value["properties"] as $key2 => $value2) {
                            $orderDAddsSQL = "INSERT INTO order_detail (idorder, type, idproduct, quantity, value, created_at, updated_at) VALUES ($idOrderNew, 2, ".$value2["id"].", )";
                        }
                    }
                }

                //$conn = getConnection ();
                $orderSQL = "SELECT orders.total_price as TotalValuePreorder, 
                orders.base_price as BaseValuePreorder,
                orders.tax_order as TaxValuePreorder,
                orders.id as PreOrderID from orders WHERE orders.id = $idOrderNew";
                $stmt = $conn->query ($orderSQL);
                $orderData = $stmt->fetchAll (PDO::FETCH_ASSOC);

                $productOrderSQL = "SELECT id,
                 idorder,
                 idproduct,
                 quantity as Quantity,
                 value,
                 created_at,
                 updated_at,
                 (quantity*value) as ProductTotalValue from order_detail where idorder = $idOrderNew";
                $stmt = $conn->query ($productOrderSQL);
                $productOrderData = $stmt->fetchAll (PDO::FETCH_ASSOC);
                $orderData[0]["ProductOrder"] = array();
                foreach ($productOrderData as $key => $value) {
                    if(isset($value["idproduct"])){
                        $idProduct = $value["idproduct"];
                        $productSQL = "SELECT id, idcategoria, name, description, value, 'order', created_at from menu_dishes where menu_dishes.id = $idProduct";
                        $stmt = $conn->query ($productSQL);
                        $productData = $stmt->fetchAll (PDO::FETCH_ASSOC);
                        $productData[0]["ProductPropertyCart"] = array(array(
                                        "ProductPropertyID" => "2222",
                                        "ProductID" => "2222",
                                        "FatherProductPropertyID" => "2222",
                                        "Name" => "2222",
                                        "PropertyType" => "2222",
                                        "GroupingTypeID" => "2222",
                                        "GroupingType" => "2222",
                                        "PropertyValueCart" => array(array(
                                            "PropertyValueID" => "22222",
                                            "ProductPropertyID" => "22222",
                                            "ProductID" => "22222",
                                            "Label" => "22222",
                                            "Price" => "22222",
                                            "Cant" => "22222",
                                            "TotalPrice" => "22222",
                                        )),
                                    ));
                        $productOrderData[$key]["Product"] = array();
                        $productOrderData[$key]["Product"] = $productData[0];
                        array_push($orderData[0]["ProductOrder"], $productOrderData[$key] );
                    }
                    //var_dump($productOrderData);
                }
               // var_dump($productOrderData);
                //array_push($orderData[0]["ProductOrder"], $productOrderData[1] );
                
                header ('Content-Type: application/json');
                /*
                $data=array(
                    "TotalValuePreorder" => 2,
                    "BaseValuePreorder" => 2,
                    "TaxValuePreorder" => 0,
                    "RestaurantDeliveryFee" => "2",
                    "PreOrderID" => "2",
                    "ProductOrder"=> array(
                        array(
                            "Quantity" => "22",
                            "ProductTotalValue" => "22",
                            "Product" => array(
                                "ProductID" => "222",
                                "Name" => "222",
                                "Description" => "222",
                                "Value" => "222",
                                "ProductImg" => "222",
                                "Enable" => "222",
                                "CategoryID" => "222",
                                "Category" => "222",
                                "CreationDate" => "222",
                                "ProductOrder" => "222",
                                "CategoryOrder" => "222",
                                "ProductPropertyCart" => array(array(
                                    "ProductPropertyID" => "2222",
                                    "ProductID" => "2222",
                                    "FatherProductPropertyID" => "2222",
                                    "Name" => "2222",
                                    "PropertyType" => "2222",
                                    "GroupingTypeID" => "2222",
                                    "GroupingType" => "2222",
                                    "PropertyValueCart" => array(array(
                                        "PropertyValueID" => "22222",
                                        "ProductPropertyID" => "22222",
                                        "ProductID" => "22222",
                                        "Label" => "22222",
                                        "Price" => "22222",
                                        "Cant" => "22222",
                                        "TotalPrice" => "22222",
                                    )),
                                )),
                                "Instructions" => "222",
                            ),
                        ),
                    ),
                );
                */
                //echo json_encode($data);
                echo json_encode($orderData[0]);

                break;

            case 'GetOrderRecalcPay':
                $addressRecal = json_encode($_REQUEST["address"]);
                $ordertypeRecal = $_REQUEST["ordertype"];
                if($ordertypeRecal == 'delivery'){
                    $ordertypeRecal = 2;
                }else if($ordertypeRecal == 'pickup'){
                    $ordertypeRecal = 1;

                }
                $orderidRecal = $_REQUEST["orderid"];

                $conn = getConnection ();

                $updateOrderSQL = "UPDATE orders set order_type = $ordertypeRecal, delivery_address = '$addressRecal', updated_at = '".date('Y-m-d H:i:s')."' where id = $orderidRecal";
                $updateTable = $conn->query ($updateOrderSQL);
                $data = [];
                if ($updateTable) {
                    //get updates data
                    $orderSQL = "SELECT orders.id as OrderID,
                     orderNum as OrderNum,
                     idrestaurant as RestaurantID,
                     idclient as ClientID,
                     total_price as TotalPriceOrder,
                     paid as Paid,
                     orders.created_at as CreationDate,
                     payment_date as PaymentDate,
                     payment_type as PaymentType,
                     base_price as BasePriceOrder,
                     tax_order as TaxOrder,
                     tip as Tip,
                     delivery_address as DeliveryAddressStr,
                     cupon_id as Cupon,
                     discount_value as DiscountValue,
                     base_price_discount as BasePriceOrderAferDiscount,
                     total_price_discount as TotalPriceOrderComplete,
                     schedule as Schedule,
                     order_type as OrderType,
                     auth_code asReceiptLink,
                     delivery_fee as DeliveryFee
                      from orders where orders.id = $orderidRecal";
                    /*
                    $sql2 = "select 
                    quality as Quality,
                    value as ProductTotalValue
                    idproduct as Product_ID
                    from order_detail where idorder = $orderidRecal";
                    */
                    //$stmt = $conn->query ($sql1);
                    //$data = $stmt->fetchAll (PDO::FETCH_ASSOC);
                     //$conn = getConnection ();
                    /*
                    $orderSQL = "SELECT orders.total_price as TotalValuePreorder, 
                    orders.base_price as BaseValuePreorder,
                    orders.tax_order as TaxValuePreorder,
                    orders.id as PreOrderID from orders WHERE orders.id = $orderidRecal";
                    */
                    $stmt = $conn->query ($orderSQL);
                    $orderData = $stmt->fetchAll (PDO::FETCH_ASSOC);

                    $productOrderSQL = "SELECT id,
                     idorder,
                     idproduct,
                     quantity as Quantity,
                     value,
                     created_at,
                     updated_at,
                     (quantity*value) as ProductTotalValue from order_detail where idorder = $orderidRecal";
                    $stmt2 = $conn->query ($productOrderSQL);
                    $productOrderData = $stmt2->fetchAll (PDO::FETCH_ASSOC);
                    $orderData[0]["ProductOrder"] = array();
                    foreach ($productOrderData as $key => $value) {
                        if(isset($value["idproduct"])){
                            $idProduct = $value["idproduct"];
                            $productSQL = "SELECT id, idcategoria, name, description, value, 'order', created_at from menu_dishes where menu_dishes.id = $idProduct";
                            $stmt3 = $conn->query ($productSQL);
                            $productData = $stmt3->fetchAll (PDO::FETCH_ASSOC);
                            $productData[0]["ProductPropertyCart"] = array(array(
                                            "ProductPropertyID" => "2222",
                                            "ProductID" => "2222",
                                            "FatherProductPropertyID" => "2222",
                                            "Name" => "2222",
                                            "PropertyType" => "2222",
                                            "GroupingTypeID" => "2222",
                                            "GroupingType" => "2222",
                                            "PropertyValueCart" => array(array(
                                                "PropertyValueID" => "22222",
                                                "ProductPropertyID" => "22222",
                                                "ProductID" => "22222",
                                                "Label" => "22222",
                                                "Price" => "22222",
                                                "Cant" => "22222",
                                                "TotalPrice" => "22222",
                                            )),
                                        ));
                            $productOrderData[$key]["Product"] = array();
                            $productOrderData[$key]["Product"] = $productData[0];
                            array_push($orderData[0]["ProductOrder"], $productOrderData[$key] );
                        }
                        //var_dump($productOrderData);
                    }
                    $data["Success"] = "true";
                    $data["Order"] = $orderData[0];
                }else{
                    $data["ErrMessage"] = "Error Model Conect";

                }
                /*
            $data = array(
                'ErrMessage' => "Error Model Conect",
                'Success' => "true",
                "Order"=>array(
                    'OrderID'=>'3',
                    'OrderNum'=>'3',
                    'RestaurantID'=>'3',
                    'ClientID'=>'3',
                    'TotalPriceOrder'=>'3',
                    'Paid'=>'3',
                    'CreationDate'=>'3',
                    'PaymentDate'=>'3',
                    'PaymentType'=>'3',
                    'BasePriceOrder'=>'3',
                    'TaxOrder'=>'3',
                    'Tip'=>'3',
                    'DeliveryAddressStr'=>'3',
                    'Cupon'=>'3',
                    'DiscountValue'=>'3',
                    'BasePriceOrderAferDiscount'=>'3',
                    'TotalPriceOrderComplete'=>'3',
                    'Schedule'=>'3',
                    'OrderType'=>'3',
                    'ProductOrder'=>array(array(
                        "Quantity" => "33",
                        "ProductTotalValue" => "33",
                        "Product" => array(
                            "ProductID" => "333",
                            "Name" => "333",
                            "Description" => "333",
                            "ProductImg" => "333",
                            "Enable" => "333",
                            "CategoryID" => "333",
                            "Category" => "333",
                            "CreationDate" => "333",
                            "ProductOrder" => "333",
                            "CategoryOrder" => "333",
                            "Value" => "333",
                            "TotalValue" => "333",
                            "ProductPropertyCart" => array(array(
                                "ProductPropertyID"=>"3333",
                                "ProductID"=>"3333",
                                "FatherProductPropertyID"=>"3333",
                                "Name"=>"3333",
                                "PropertyType"=>"3333",
                                "GroupingTypeID"=>"3333",
                                "GroupingType"=>"3333",
                                "PropertyValueCart"=>array(array(
                                    "PropertyValueID" => "33333",
                                    "ProductPropertyID" => "33333",
                                    "ProductID" => "33333",
                                    "Label" => "33333",
                                    "Price" => "33333",
                                    "Cant" => "33333",
                                    "TotalPrice" => "33333",
                                )),
                            )),
                        ),
                        "Instructions" => "33",
                    )),
                    'ReceiptLink'=>'3', // 
                    'DeliveryFee'=>'3', // 
                    ),

                );
                */
                header ('Content-Type: application/json');
                echo json_encode($data);

                break;

            case 'SetTipsAndDiscount':
                $sessionkey = $_REQUEST["SessionKey"];
                $orderid = $_REQUEST["orderid"];
                $tips = $_REQUEST["tips"];
                $conn = getConnection();
                if (isset($tips)) {
                    $tip = $tips["tip"];
                    $tiptype = $tips["tiptype"];
                    $clientPaymentSQL = "update orders set tip = $tip, updated_at = '".date('Y-m-d H:i:s')."' where id=$orderid";
                    $stmt3 = $conn->query ($clientPaymentSQL);
                }else{
                    $coupon = $_REQUEST["couponid"];
                    $clientPaymentSQL = "update orders set cupon_id = '$coupon', updated_at = '".date('Y-m-d H:i:s')."' where id=$orderid";
                    $stmt3 = $conn->query ($clientPaymentSQL);
                    
                }

                    //$clientPaymentData = $stmt3->fetchAll (PDO::FETCH_ASSOC);
                    
                    
                $data["Order"] = getOrderData($orderid, $conn);
                
                /*
                    $data = array(
                        'Order' => array(
                            "OrderID" => "4",
                            "OrderNum" => "4",
                            "RestaurantID" => "4",
                            "ClietID" => "4",
                            "TotalPriceOrder" => "4",
                            "Paid" => "4",
                            "CreationDate" => "4",
                            "PaymentDate" => "4",
                            "PaymentType" => "4",
                            "BasePriceOrder" => "4",
                            "TaxOrder" => "4",
                            "Tip" => "4",
                            "DeliveryAddressStr" => "4",
                            "Cupon" => "4",
                            "DiscountValue" => "4",
                            "BasePriceOrderAferDiscount" => "4",
                            "TotalPriceOrderComplete" => "4",
                            "Schedule" => "4",
                            "OrderType" => "4",
                            "ProductOrder" => array(array(
                                "Quantity" =>"44",
                                "ProductTotalValue" =>"44",
                                "Product" => array(
                                    "ProductID" => "444",
                                    "Name" => "444",
                                    "Description" => "444",
                                    "ProductImg" => "444",
                                    "Enable" => "444",
                                    "CategoryID" => "444",
                                    "Category" => "444",
                                    "CreationDate" => "444",
                                    "ProductOrder" => "444",
                                    "CategoryOrder" => "444",
                                    "Value" => "444",
                                    "TotalValue" => "444",
                                    "ProductPropertyCart" => array(array(
                                        "ProductPropertyID" => "4444" ,
                                        "ProductID" => "4444" ,
                                        "FatherProductPropertyID" => "4444" ,
                                        "Name" => "4444" ,
                                        "PropertyType" => "4444" ,
                                        "GroupingTypeID" => "4444" ,
                                        "GroupingType" => "4444" ,
                                        "PropertyValueCart" => array(array(
                                            "PropertyValueID" => "44444",
                                            "ProductPropertyID" => "44444",
                                            "ProductID" => "44444",
                                            "Label" => "44444",
                                            "Price" => "44444",
                                            "Cant" => "44444",
                                            "TotalPrice" => "44444",
                                        )) ,
                                    )),
                                ),
                                "Instructions" =>"44",
                            )),
                            "ReceipLink" => "4",
                            "DeliveryFee" => "4",
                        ),
                    );
                */
                header ('Content-Type: application/json');
                echo json_encode($data);
                break;

            case 'GetClientPayments':
                $user_token = $_REQUEST["SessionKey"];
                $clientPaymentSQL = "SELECT payments_profiles.id, payments_profiles.id_payment, payments_profiles.first_name as First, payments_profiles.last_name as Last, payments_profiles.address as Street, payments_profiles.city as City, payments_profiles.state as State, payments_profiles.zipcode as Zip from payments_profiles, users where remember_token='$user_token' and users.id_profile=payments_profiles.id_profile";
                $conn = getConnection();

                $stmt3 = $conn->query ($clientPaymentSQL);
                $data = array(
                    "TcOut" => array(array(
                        "CardType" => "",
                        "CardExpiration" => "",
                        "CardNumber" => "",
                        "PaymenProfileID" => "",
                        "BillingInfo" => array(
                            "First" => "",
                            "Last" => "",
                            "Street" => "",
                            "City" => "",
                            "State" => "",
                            "Zip" => "",
                        ),
                    )),
                );
                if($stmt3){
                    $clientPaymentData = $stmt3->fetchAll (PDO::FETCH_ASSOC);
                    if(count($clientPaymentData)>0){
                        foreach ($clientPaymentData as $key => $value) {
                            $data["TcOut"][$key]["BillingInfo"]["First"] = $value["First"];                            
                            $data["TcOut"][$key]["BillingInfo"]["Last"] = $value["Last"];                            
                            $data["TcOut"][$key]["BillingInfo"]["Street"] = $value["Street"];                            
                            $data["TcOut"][$key]["BillingInfo"]["City"] = $value["City"];                            
                            $data["TcOut"][$key]["BillingInfo"]["State"] = $value["State"];                            
                            $data["TcOut"][$key]["BillingInfo"]["Zip"] = $value["Zip"];  
                            $data["TcOut"][$key]["PaymenProfileID"] = $value["id"];  
                            $data["TcOut"][$key]["CardNumber"] = $value["id_payment"];  
                        }
                    }
                }else{
                    echo "error";
                }

                header ('Content-Type: application/json');
                echo json_encode($data);
                
                break;

            case 'CheckOrderStatus':
                    /*
                        $data = array(
                            'Order'=>array(
                                "OrderID" => "7",
                                "OrderNum" => "7",
                                "RestaurantID" => "7",
                                "ClientID" => "7",
                                "TotalPriceOrder" => "7",
                                "Paid" => "7",
                                "CreationDate" => "7",
                                "PaymentDate" => "7",
                                "PaymentType" => "7",
                                "BasePriceOrder" => "7",
                                "TaxOrder" => "7",
                                "Tip" => "7",
                                "DeliveryAddressStr" => "7",
                                "Cupon" => "7",
                                "DiscountValue" => "7",
                                "BasePriceOrderAferDiscount" => "7",
                                "TotalPriceOrderComplete" => "7",
                                "Schedule" => "7",
                                "OrderType" => "7",
                                "ProductOrder" => array(array(
                                    "Quantity" => "77",
                                    "ProductTotalValue" => "77",
                                    "Product" => array(
                                        "ProductID" => "777",
                                        "Name" => "777",
                                        "Description" => "777",
                                        "ProductImg" => "777",
                                        "Enable" => "777",
                                        "CategoryID" => "777",
                                        "Category" => "777",
                                        "CreationDate" => "777",
                                        "ProductOrder" => "777",
                                        "CategoryOrder" => "777",
                                        "Value" => "777",
                                        "TotalValue" => "777",
                                        "ProductPropertyCart" => array(array(
                                            "ProductPropertyID" => "7777",
                                            "ProductID" => "7777",
                                            "FatherProductPropertyID" => "7777",
                                            "Name" => "7777",
                                            "PropertyType" => "7777",
                                            "GroupingTypeID" => "7777",
                                            "GroupingType" => "7777",
                                            "PropertyValueCart" => array(array(
                                                "PropertyValueID" => "77777",
                                                "ProductPropertyID" => "77777",
                                                "ProductID" => "77777",
                                                "Label" => "77777",
                                                "Price" => "77777",
                                                "Cant" => "77777",
                                                "TotalPrice" => "77777",
                                            )),
                                        )),
                                    ),
                                    "Instructions" => "77"
                                )),
                                "ReceiptLink" => "7",
                                "DeliveryFee" => "7",
                            ),
                            "Restaurant" => array(
                                "RestaurantID" => "7",
                                "RestaurantChainID" => "7",
                                "Name" => "7",
                                "Description" => "7",
                                "Image" => "7",
                                "Tips" => "7",
                                "DeliveryArea" => "7",
                                "DeliveryTime" => "7",
                                "OurKitchen" => "7",
                                "Address" => "7",
                                "Phones" => "7",
                                "Rate" => "7",
                                "MinimumOrder" => "7",
                                "Tax" => "7",
                                "DeliveryCost" => "7",
                                "Longitude" => "7",
                                "Latitude" => "7",
                                "Zip" => "7",
                                "Distance" => "7",
                                "Web" => "7",
                                "CreationDate" => "7",
                                "Delivery" => "7",
                                "Pickup" => "7",
                                "Enable" => "7",
                                "YelpId" => "7",
                                "Dividends_percent" => "7",
                                "Filters" => array(
                                    "Delivery"=> "77",
                                    "Pickup"=> "77",
                                    "FreeDelivery"=> "77",
                                    "OpenNow"=> "77",
                                    "HaveCoupons"=> "77",
                                ),
                                "PaymentTypeOut" => array(array(
                                    "PaymentTypeID" => "77",
                                    "PaymentType" => "77",
                                    "Icon" => "77",
                                    "Enable" => "77",
                                )),
                                "ScheduleOut" => array(
                                    "ScheduleID" => "77",
                                    "Monday" => "77",
                                    "Tuesday" => "77",
                                    "Wednesday" => "77",
                                    "Thursday" => "77",
                                    "Friday" => "77",
                                    "Saturday" => "77",
                                    "Sunday" => "77",
                                ),
                            ),
                            "OrderStatus" => "true",
                        );
                        header ('Content-Type: application/json');
                        echo json_encode($data);
                    */
                    $getID = $_REQUEST["orderid"];
                    $sessionk = $_REQUEST["SessionKey"];
                    $lg = $_REQUEST["lg"];
                    $conn = getConnection ();
                    $oData = getOrderData($getID, $conn);
                   // var_dump($productOrderData);
                    //array_push($orderData[0]["ProductOrder"], $productOrderData[1] );
                    if (!empty($oData)) {
                        $rData = getRestaurantData($oData["RestaurantID"], $conn);
                    }
                    header ('Content-Type: application/json');
                    echo json_encode(array("Order" => $oData, "Restaurant"=> $rData,"OrderStatus" => "true"));
                    break;
            case 'GetUserAddress':
                
                //echo "ls".$_SESSION["idUser"];
                $sessionk = $_REQUEST["SessionKey"];
                $id = getIDByToken($sessionk);
                $conn = getConnection();
                $data = getAddress($id, $conn, 'ClientAddressOut');
                //$data["codeerr"] = "campo: ".$_SESSION["idUser"].$_SESSION["_token"]."++".session_id ();
                echo json_encode($data);

                
                break;
            case 'GetProfileBySessionKey':
                $sessionkey = $_REQUEST["SessionKey"];
                $id = getIDByToken($sessionkey);
                $conn = getConnection ();
                //$data1 = $stmt->fetchAll (PDO::FETCH_ASSOC);
                //if($stmt){
                    $data = getUserByID($conn, $sessionkey);
                //}
                /*
                $data = array(
                    'User' => array(
                        "ClientID" => "11",
                        "UserName" => "11",
                        "Password" => "11",
                        "FullName" => "11",
                        "eMail" => "11",
                        "FacebookID" => "11",
                        "TwitterID" => "11",
                        "Address" => "11",
                        "Cellphone" => "11",
                        "CreationDate" => "Cra 25 # 12 - 15",
                    ),
                    'Session'=>[
                        "SessionKey" => "key_s",
                        "TimeExpiration" => "11",
                        "ClientID" => "11",
                    ],
                );
                */
                header ('Content-Type: application/json');
                echo json_encode($data);
                    break;
            case 'GetOrderForPay':
                $sessionkey = $_REQUEST["SessionKey"];
                $ordertype = $_REQUEST["ordertype"];
                $paymenttype = $_REQUEST["paymenttype"];
                $instructions = $_REQUEST["instructions"];
                $address = $_REQUEST["address"];
                $schedule = $_REQUEST["schedule"];
                $orderid = $_REQUEST["orderid"];

                $updateOrderFinalSQL = "UPDATE orders SET order_type = $ordertype, payment_type = '$paymenttype', instructions = '$instructions', delivery_address = '$address', schedule = '$schedule' WHERE id=$orderid";
                $conn = getConnection();
                //$resp = $conn->query($updateOrderFinalSQL);
                if($resp){
                    $frmInputSQL ="SELECT 
                    tip as x_tip,
                    tax_order as x_tax


                     FROM orders where id=$orderid";
                }
                    $data = array(
                        "FrmParams" => array(
                            "buttonLabel" => "10",
                            "URL" => "10",
                        ),
                        "FrmInputs" => array(
                            "x_login" => "10",
                            "x_amount" => "10",
                            "x_tip" => "10",
                            "x_freight" => "10",
                            "x_tax" => "10",
                            "x_tax_exempt" => "10",
                            "x_po_num" => "10",
                            "x_description" => "10",
                            "x_test_request" => "10",
                            "x_invoice_num" => "10",
                            "x_myorder_id" => "10",
                            "x_fp_sequence" => "10",
                            "x_fp_timestamp" => "10",
                            "x_fp_hash" => "10",
                            "x_show_form" => "10",
                            "x_recurring_billing" => "10",
                            "x_cust_id" => "10",
                            "x_myclient_id" => "10",
                            "x_zip" => "10",
                            "x_address" => "10",
                            "x_city" => "10",
                            "x_state" => "10",
                            "x_email" => "10",
                            "x_email_customer" => "10",
                            "x_first_name" => "10",
                            "x_ship_to_zip" => "10",
                            "x_ship_to_address" => "10",
                            "x_ship_to_city" => "10",
                            "x_ship_to_state" => "10",
                            "x_ship_to_first_name" => "10",
                            "x_relay_always" => "10",
                            "x_relay_url" => "10",
                            "x_relay_response" => "10",
                            "string" => array("String1s","String2s"),
                        ),
                    );
                
                header ('Content-Type: application/json');
                echo json_encode($data);
                break;
            case 'GetFavoritesOrders':
                $data = array(
                    'OrderOutRestaurant' => [array(
                        "OrderID" => "A",
                        "RestaurantID" => "A",
                        "OrderNum" => "A",
                        "ClientID" => "A",
                        "TotalPriceOrder" => "A",
                        "Paid" => "A",
                        "CreationDate" => "2018-12-15T12:00",
                        "PaymentDate" => "A",
                        "PaymentType" => "A",
                        "BasePriceOrder" => "A",
                        "TaxOrder" => "A",
                        "Tip" => "A",
                        "DeliveryAddressStr" => "A",
                        "Cupon" => "A",
                        "DiscountValue" => "A",
                        "BasePriceOrderAferDiscount" => "A",
                        "TotalPriceOrderComplete" => "A",
                        "Schedule" => "A",
                        "OrderType" => "A",
                        "OrderProducts" => array(
                            "ProductOrder" => [[
                                "ProductID" => "AA",
                                "Quantity" => "AA",
                                "ProductTotalValue" => "AA",
                                "ProductID" => "AA",
                                "Name" => "AA",
                                "Description" => "AA",
                                "Value" => "AA",
                                "ProductImg" => "AA",
                                "Enable" => "AA",
                                "CategoryID" => "AA",
                                "Category" => "AA",
                                "CreationDate" => "AA",
                                "ProductOrder" => "AA",
                                "CategoryOrder" => "AA",
                                "ProductPropertyCart" => [[
                                    "ProductPropertyID" => "AAA",
                                    "ProductD" => "AAA",
                                    "FatherProductPropertyID" => "AAA",
                                    "Name" => "AAA",
                                    "PropertyType" => "AAA",
                                    "GroupingTypeID" => "AAA",
                                    "GroupingType" => "AAA",
                                    "PropertyValueCart" => [[
                                        "PropertyValueID" => "AAAA",
                                        "ProductPropertyID" => "AAAA",
                                        "ProductID" => "AAAA",
                                        "Label" => "AAAA",
                                        "Price" => "AAAA",
                                        "Cant" => "AAAA",
                                        "TotalPrice" => "AAAA",
                                    ]],
                                ]],
                            ]],
                        ),
                        "ReceiptLink" => "A",
                        "Restaurant" => [
                            "RestaurantID" => "A",
                            "RestaurantChainID" => "A",
                            "Name" => "A",
                            "Description" => "A",
                            "Image" => "A",
                            "Tips" => "A",
                            "DeliveryArea" => "A",
                            "DeliveryTime" => "A",
                            "OurKitchen" => "A",
                            "Address" => "A",
                            "Phones" => "A",
                            "Rate" => "A",
                            "MinimumOrder" => "A",
                            "Tax" => "A",
                            "DeliveryCost" => "A",
                            "Longitude" => "A",
                            "Latitude" => "A",
                            "Zip" => "A",
                            "Distance" => "A",
                            "Web" => "A",
                            "CreationDate" => "A",
                            "Delivery" => "A",
                            "Pickup" => "A",
                            "Enable" => "A",
                            "YelpId" => "A",
                            "Dividends_percent" => "A",
                            "Filters" => [
                                "Delivery" => "AA",
                                "Pickup" => "AA",
                                "FreeDelivery" => "AA",
                                "OpenNow" => "AA",
                                "HaveCoupons" => "AA",
                            ],
                            "PaymentTypeOut" => [[
                                "PaymentTypeID" => "AA",
                                "PaymentType" => "AA",
                                "Icon" => "AA",
                                "Enable" => "AA",
                            ]],
                            "ScheduleOut" => [
                                "ScheduleID" => "AA",
                                "Monday" => "AA",
                                "Tuesday" => "AA",
                                "Wednesday" => "AA",
                                "Thursday" => "AA",
                                "Friday" => "AA",
                                "Saturday" => "AA",
                                "Sunday" => "AA",
                            ],
                        ],
                    )],
                );
                header ('Content-Type: application/json');
                echo json_encode($data);
                break;

            case 'GetFavoritesRestaurants':
                $data = array(
                    "RestaurantOut" => [[
                        "RestaurantID" => "B",
                        "RestaurantChainID" => "B",
                        "Name" => "B",
                        "Description" => "B",
                        "Image" => "B",
                        "Tips" => "B",
                        "DeliveryArea" => "B",
                        "DeliveryTime" => "B",
                        "OurKitchen" => "B",
                        "Address" => "B",
                        "Phones" => "B",
                        "Rate" => "B",
                        "MinimumOrder" => "B",
                        "Tax" => "B",
                        "DeliveryCost" => "B",
                        "Longitude" => "B",
                        "Latitude" => "B",
                        "Zip" => "B",
                        "Distance" => "B",
                        "Web" => "B",
                        "CreationDate" => "B",
                        "Delivery" => "B",
                        "Pickup" => "B",
                        "Enable" => "B",
                        "YelpId" => "B",
                        "Dividends_percent" => "B",
                        "Filters" => [
                            "delivery" => "BB",
                            "Pickup" => "BB",
                            "FreeDelivery" => "BB",
                            "OpenNow" => "BB",
                            "HaveCoupons" => "BB",
                        ],
                        "PaymentTypeOut" => [[
                            "PaymentTypeID" => "BB",
                            "PaymentType" =>   "BB",
                            "Icon" =>  "BB",
                            "Enable" =>    "BB",
                        ]],
                        "ScheduleOut" => [
                            "ScheduleID" => "BB",
                            "Monday" => "BB",
                            "Tuesday" => "BB",
                            "Wednesday" => "BB",
                            "Thursday" => "BB",
                            "Friday" => "BB",
                            "Saturday" => "BB",
                            "Sunday" => "BB",
                        ],
                    ]],
                );
                header ('Content-Type: application/json');
                echo json_encode($data);
                break;
            case 'SetNewCard':
                $data = [];
                
                $sessionk = $_REQUEST["SessionKey"];
                $cardNumber = $_REQUEST["cardNumber"];
                $cardCode = $_REQUEST["cardCode"];
                $FirstName = $_REQUEST["FirstName"];
                $LastName = $_REQUEST["LastName"];
                $Address = $_REQUEST["Address"];
                $City = $_REQUEST["City"];
                $State = $_REQUEST["State"];
                $ZipCode = $_REQUEST["ZipCode"];
                $conn = getConnection();
                $getProfileIDSQL = "SELECT id_profile from users where remember_token='$sessionk'";
                $stmt3 = $conn->query ($getProfileIDSQL);
                $id_profileData = $stmt3->fetchAll (PDO::FETCH_ASSOC);
                $id_profile = $id_profileData[0]["id_profile"];
                if($_REQUEST["update"] == "true"){
                    $id = $_REQUEST["PaymenProfileID"];
                    $sql1 = "UPDATE payments_profiles set id_profile=$id_profile, id_payment=$cardNumber, cc_name='$cardCode', first_name='$FirstName', last_name='$LastName', address='$Address', city='$City', state='$State', zipcode='$ZipCode' WHERE id=$id";
                }else{
                    $sql1 = "INSERT into payments_profiles (id_profile, id_payment, cc_name, first_name, last_name, address, city, state, zipcode) values ($id_profile, $cardNumber, '$cardCode', '$FirstName', '$LastName', '$Address', '$City', '$State', '$ZipCode')";
                }
                try {
                    $conn->beginTransaction();
                    $conn->exec($sql1);
                    $conn->commit();
                    $data["Success"] = "true";                    
                    //echo $sql;
                } catch (Exception $e) {
                  $conn->rollBack();
                  $data["ErrMessage"] = "Message Error DB";
                  //echo "Failed: " . $e->getMessage();
                }
                header ('Content-Type: application/json');
                echo json_encode($data);
                break; 
            case 'UpdateClientAddress':
                $sessionkey = $_REQUEST["SessionKey"];
                $clientaddressid = $_REQUEST["ClientAddressID"];
                $address = $_REQUEST["Address"];
                $suit = $_REQUEST["Suit"];
                $city = $_REQUEST["City"];
                $state = $_REQUEST["State"];
                $zipcode = $_REQUEST["ZIPCode"];
                $crossstreet = $_REQUEST["CrossStreet"];
                $phone = $_REQUEST["Phone"];
                $addressname = $_REQUEST["AddressName"];
                $conn = getConnection ();
                $uClientAddressSQL = "UPDATE direcciones_clientes SET type_address = '$address', apt = '$suit', city = '$city', state = '$state', zipcode = '$zipcode', cross_street = '$crossstreet', phone = '$phone', updated_at = '".date('Y-m-d H:i:s')."' WHERE id=$clientaddressid";
                $stmt = $conn->query ($uClientAddressSQL);
                $data = array(
                    "ClientAddressID" => "",
                    "ClientID" => "",
                    "Address" =>"",
                    "Suit" => "",
                    "City" => "",
                    "State" => "",
                    "ZIPCode" => "",
                    "CrossStreet" => "",
                    "Phone" => "",
                    "AddressName" => "",
                    "CreationDate" => "",
                    "Default" => "",
                    "Enable" => "",
                );
                if ($stmt) {
                    $data = getAddressID($clientaddressid, $conn);
                }
                header ('Content-Type: application/json');
                echo json_encode($data);
                break; 
            case 'UpdateClientProfile':

                $sessionkey = $_REQUEST["SessionKey"];
                $id = getIDByToken($sessionkey);
                $conn = getConnection ();
                if(isset($_REQUEST["fullname"])){
                    $fullname = $_REQUEST["fullname"];
                    $ClientProfileSQL = "update users set username = '$fullname', updated_at = '".date('Y-m-d H:i:s')."' where id=$id";
                }else if (isset($_REQUEST["mail"])){
                    $mail = $_REQUEST["mail"];
                    $ClientProfileSQL = "update users set email = '$mail', updated_at = '".date('Y-m-d H:i:s')."' where id=$id";

                }else if (isset($_REQUEST["OldPassword"])){
                    $OldPassword = $_REQUEST["OldPassword"];
                    $NewPassword = $_REQUEST["NewPassword"];
                    if(isPassword($conn, $sessionkey, $OldPassword) && isset($_REQUEST["NewPassword"])){
                        $ClientProfileSQL = "update users set password = '$NewPassword', updated_at = '".date('Y-m-d H:i:s')."' where id=$id";

                    }
                    //echo "bien";
                }
                $stmt = $conn->query ($ClientProfileSQL);
                //$data1 = $stmt->fetchAll (PDO::FETCH_ASSOC);
                //if($stmt){
                    $data = getUserByID($conn, $sessionkey);
                //}
                /*
                $data = array(
                    'User' => array(
                        "ClientID" => "11",
                        "UserName" => "11",
                        "Password" => "11",
                        "FullName" => "11",
                        "eMail" => "11",
                        "FacebookID" => "11",
                        "TwitterID" => "11",
                        "Address" => "11",
                        "Cellphone" => "11",
                        "CreationDate" => "Cra 25 # 12 - 15",
                    ),
                    'Session'=>[
                        "SessionKey" => "key_s",
                        "TimeExpiration" => "11",
                        "ClientID" => "11",
                    ],
                );
                */
                header ('Content-Type: application/json');
                echo json_encode($data);
                break; 
            case 'GetAllContactsType':
                $data = [
                    
                         "ContactTypeOut"=> [[
                            "ContactTypeID" => "Contact Type ID",
                            "ContactType" => "Contact Type",
                            ],
                            [
                            "ContactTypeID" => "Contact Type ID 2",
                            "ContactType" => "Contact Type 2",
                            ]]
                    
                ];
                header ('Content-Type: application/json');
                echo json_encode($data);
                break;
            case 'NewContact':
                $mail = $_REQUEST["eMail"];
                $to = "crhis_316360@hotmail.com";
                $subject = "Your password";
                $message = "Hello Homer, thanks for registering. Your password is: springfield";
                $from = "crhisdlm94@gmail.com";
                $headers = "From: $from";

                // Send email
                $status = mail($to,$subject,$message,$headers);
                $eval =  $status ? "true" : "false";
                $data = [];
                if($status){
                    $data["Success"] = "true";
                }else{
                    $data["ErrMessage"] = "Message Error";
                }

                header ('Content-Type: application/json');
                echo json_encode($data);
                break;
            case 'NewPwdChange':
                $mail = $_REQUEST["mail"];
                $data = [];
                if(!empty($mail)){
                    $newPassword = randomPassword();
                    $data["ls"] = $newPassword;
                    sendMailRP($mail, "Reset Password", $newPassword);
                    $newPassword = sha1($newPassword);
                    $conn = getConnection ();
                    $newPwdSQL = "update users set password = '$newPassword', updated_at = '".date('Y-m-d H:i:s')."' where email='$mail'";
                    $stmt = $conn->query ($newPwdSQL);
                    if($stmt){
                        $data["Success"] = "true";                        
                    }else{
                        $data["ErrMessage"] = "Not changed Password in DB";                                            
                    }
                }else{
                    $data["ErrMessage"] = "Empty Email";                    
                }
                header ('Content-Type: application/json');
                echo json_encode($data);

                break;
            case 'CreateClient':
                $email = $_REQUEST["mail"];
                $pass = $_REQUEST["password"];
                $fullname = $_REQUEST["fullname"];
                $conn = getConnection ();
                if(!empty($email)) {
                    $sql = "insert into users (email, password, username, UserID, created_at, updated_at) values ('$email', '$pass', '$fullname', 1, '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."')";
                    try {
                        $conn->beginTransaction();
                        $conn->exec($sql);
                        $conn->commit();
                        //echo $sql;
                    } catch (Exception $e) {
                      $conn->rollBack();
                      //echo "Failed: " . $e->getMessage();
                    }
                    //$stmt = $conn->query ($sql);
                    //$data1 = $stmt->fetchAll (PDO::FETCH_ASSOC);
                }
                $data = array(
                        'User' => array(
                                "ClientID" => "12",
                                "UserName" => "12",
                                "Password" => "12",
                                "FullName" => "12",
                                "eMail" => "12",
                                "FacebookID" => "12",
                                "TwitterID" => "12",
                                "Address" => "12",
                                "Cellphone" => "12",
                                "CreationDate" => "12",
                            ),
                        'Section' => array(
                            "SessionKey" => "key_s",
                            "TimeExpiration" => "12",
                            "ClientID" => "12",
                        ),
                    );
                    header ('Content-Type: application/json');
                    echo json_encode($data);
                break;
case 'ChangePassword':
    $
    $data = [
        "Success" => "Success Process",
        "ErrMessage" => "Message Error",
    ];
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
case 'ClientLogOut':
    //$_SESSION["_token"] = key_random(60);
    session_unset(); 

    // destroy the session 
    session_destroy(); 
    $conn = getConnection ();
    $sessionKey = $_REQUEST["SessionKey"];
    $logoutSQL ="update users set remember_token = '".key_random(60)."', updated_at = '".date('Y-m-d H:i:s')."' where id=".getIDByToken($sessionKey);
    $stmt = $conn->query ($logoutSQL);
    $data = [];
    if($stmt){
        $data["Success"] = "Success Process";
    }else{
        $data["ErrMessage"] ="Message Error ";
    }
    
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
case 'DeleteCard':
    $data = [];
    $conn = getConnection ();

    $paymenProfileID = $_REQUEST["PaymenProfileID"];
    $deleteCardSQL = "DELETE from payments_profiles where id = $paymenProfileID";
    $stmt = $conn->query ($deleteCardSQL);
    if($stmt){
        $orderData = $stmt->fetchAll (PDO::FETCH_ASSOC); 
        $data["Success"] = "true";
    }else{
        $data["ErrMessage"] = "Message Error";
    }
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
case 'DuplicateOrder2':
    $sessionkey = $_REQUEST["SessionKey"];
    $orderid = $_REQUEST["orderid"];
    /*
        $data = [
            "Success" => "true",
            "Order" => [
                "OrderID" => "13",
                "OrderNum" => "13",
                "RestaurantID" => "13",
                "ClientID" => "13",
                "TotalPriceOrder" => "13",
                "Paid" => "13",
                "CreationDate" => "13",
                "PaymentDate" => "13",
                "PaymentType" => "13",
                "BasePriceOrder" => "13",
                "TaxOrder" => "13",
                "Tip" => "13",
                "DeliveryAddressStr" => "13",
                "Cupon" => "13",
                "DiscountValue" => "13",
                "BasePriceOrderAferDiscount" => "13",
                "TotalPriceOrderComplete" => "13",
                "Schedule" => "13",
                "OrderType" => "13",
                "ProductOrder" => [[
                    "Quantity" => "1313",
                    "ProductTotalValue" => "1313",
                    "Product" => [
                        "ProductID" => "131313",
                        "Name" => "131313",
                        "Description" => "131313",
                        "ProductImg" => "131313",
                        "Enable" => "131313",
                        "CategoryID" => "131313",
                        "Category" => "131313",
                        "CreationDate" => "131313",
                        "ProductOrder" => "131313",
                        "CategoryOrder" => "131313",
                        "Value" => "131313",
                        "TotalValue" => "131313",
                        "ProductPropertyCart" => [[
                            "ProductPropertyID" => "13131313",
                            "ProductID" => "13131313",
                            "FatherProductPropertyID" => "13131313",
                            "Name" => "13131313",
                            "PropertyType" => "13131313",
                            "GroupingTypeID" => "13131313",
                            "GroupingType" => "13131313",
                            "PropertyValueCart" => [[
                                "PropertyValueID" => "1313131313",
                                "ProductPropertyID" => "1313131313",
                                "ProductID" => "1313131313",
                                "Label" => "1313131313",
                                "Price" => "1313131313",
                                "Cant" => "1313131313",
                                "TotalPrice" => "1313131313",
                            ]],
                        ]],
                    ],
                    "Instructions" => "1313",
                ]],
                "ReceiptLink" => "13",
                "DeliveryFee" => "13",
            ],
            "Restaurant" => [
                "RestaurantID" => "13",
                "RestaurantChainID" => "13",
                "Name" => "13",
                "Description" => "13",
                "Image" => "13",
                "Tips" => "13",
                "DeliveryArea" => "13",
                "DeliveryTime" => "13",
                "OurKitchen" => "13",
                "Address" => "13",
                "Phones" => "13",
                "Rate" => "13",
                "MinimumOrder" => "13",
                "Tax" => "13",
                "DeliveryCost" => "13",
                "Longitude" => "13",
                "Latitude" => "13",
                "Zip" => "13",
                "Distance" => "13",
                "Web" => "13",
                "CreationDate" => "13",
                "Delivery" => "13",
                "Pickup" => "13",
                "Enable" => "13",
                "YelpID" => "13",
                "Dividends_percent" => "13",
                "ListFavoriteDisch" => [
                    "ProductMenuOut" => [[
                        "ProductID" => "1313",
                        "Name" => "1313",
                        "Description" => "1313",
                        "Value" => "1313",
                        "ProductImg" => "1313",
                        "Enable" => "1313",
                        "CategoryID" => "1313",
                        "Category" => "1313",
                        "CreationDate" => "1313",
                        "ProductOrder" => "1313",
                        "CategoryOrder" => "1313",
                        "MenuID" => "1313",
                        "ProductPropertyOut" => [[
                            "ProductPropertyID" => "131313",
                            "ProductID" => "131313",
                            "FatherProductPropertyID" => "131313",
                            "Name" => "131313",
                            "PropertyType" => "131313",
                            "GroupingTypeID" => "131313",
                            "GroupingType" => "131313",
                            "PropertyValueOut" => [[
                                "PropertyValueID" => "13131313",
                                "ProductPropertyID" => "13131313",
                                "ProductID" => "13131313",
                                "Label" => "13131313",
                                "Price" => "13131313",
                            ]],
                        ]],
                    ]],
                ],
                "ListCoupons" => [
                    "ProductMenuOut" => [
                        "ProductID" => "1313",
                        "Name" => "1313",
                        "Description" => "1313",
                        "Value" => "1313",
                        "ProductImg" => "1313",
                        "Enable" => "1313",
                        "CategoryID" => "1313",
                        "Category" => "1313",
                        "CreationDate" => "1313",
                        "ProductOrder" => "1313",
                        "CategoryOrder" => "1313",
                        "MenuID" => "1313",
                        "ProductPropertyOut" => [[
                            "ProductPropertyID" => "131313",
                            "ProductID" => "131313",
                            "FatherProductPropertyID" => "131313",
                            "Name" => "131313",
                            "PropertyType" => "131313",
                            "GroupingTypeID" => "131313",
                            "GroupingType" => "131313",
                            "PropertyValueOut" => [[
                                "PropertyValueID" => "13131313",
                                "ProductPropertyID" => "13131313",
                                "ProductID" => "13131313",
                                "Label" => "13131313",
                                "Price" => "13131313",
                            ]],
                        ]],
                    ],
                ],
                "Filters" => [
                    "Delivery" => "13",
                    "Pickup" => "13",
                    "FreeDelivery" => "13",
                    "OpenNow" => "13",
                    "HaveCoupons" => "13",
                ],
                "PaymentTypeOut" => [[
                    "PaymentTypeID" => "13",
                    "PaymentType" => "13",
                    "Icon" => "13",
                    "Enable" => "13",
                ]],
                "ScheduleOut" => [
                    "ScheduleID" => "13",
                    "Monday" => "13",
                    "Tuesday" => "13",
                    "Wednesday" => "13",
                    "Thursday" => "13",
                    "Friday" => "13",
                    "Saturday" => "13",
                    "Sunday" => "13",
                ],
            ],
            "ErrMessage" => "",
        ];
    */
    $conn = getConnection ();
    $orderOriginSQL = "select * from orders where id=$orderid";
    $originOrder = $conn->query ($orderOriginSQL);
    $dataOriginOrder = $originOrder->fetchAll (PDO::FETCH_ASSOC)[0];
    $dataOriginOrder["orderNum"] = date('Ymdhms') + rand(1,999);
    $dataOriginOrder["created_at"] = date('Y-m-d H:i:s');
    $dataOriginOrder["updated_at"] = date('Y-m-d H:i:s');
    

    $clientId = getIDByToken($sessionkey);
    $sqlID = "select max(id) as id from orders";
    $stmt = $conn->query ($sqlID);
    $id = $stmt->fetchAll (PDO::FETCH_ASSOC);
    $idOrderNew = $id[0]["id"] + 1;
    $dataOriginOrder["id"] = $idOrderNew;

    
    $valuesO = array_values($dataOriginOrder);
    foreach ($valuesO as $key => $value) {
        if(empty($value)){
            $valuesO[$key] = "NULL";
        }else{
            $valuesO[$key] = "'".$value."'";            
        }
    }

    $fields = "(".(implode(", ",array_keys($dataOriginOrder))). ")";
    $values = "(".(implode(", ",array_values($valuesO))). ")";
    

    $orderDOriginSQL = "select * from order_detail where idorder=$orderid";
    $originOrder = $conn->query ($orderDOriginSQL);
    $dataDOriginOrder = $originOrder->fetchAll (PDO::FETCH_ASSOC);

    if($dataDOriginOrder){        
        $sql1 = "INSERT into orders ".$fields." values ".$values;
        try {
            $conn->beginTransaction();
            $conn->exec($sql1);
            $conn->commit();
            //echo $sql;
        } catch (Exception $e) {
          $conn->rollBack();
          //echo "Failed: " . $e->getMessage();
        }
        foreach ($dataDOriginOrder as $value) {
            $sql2 = "INSERT into order_detail (idorder, type, idproduct, quantity, value, created_at, updated_at) values ($idOrderNew, ".$value["type"].", ".$value["idproduct"].", ".$value["quantity"].", ".$value["value"].", '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."')";
                //$id = $stmt->fetchAll (PDO::FETCH_ASSOC);
            try {
                $conn->beginTransaction();
                $conn->exec($sql2);
                $conn->commit();
                //echo $sql;
            } catch (Exception $e) {
              $conn->rollBack();
              //echo "Failed: " . $e->getMessage();
            }
        }
    }
    //$conn = getConnection ();
    $oData = getOrderData($idOrderNew, $conn);
   // var_dump($productOrderData);
    //array_push($orderData[0]["ProductOrder"], $productOrderData[1] );
    if (!empty($oData)) {
        $rData = getRestaurantData($oData["RestaurantID"], $conn);
    }
    header ('Content-Type: application/json');
    echo json_encode(array("Order" => $oData, "Restaurant"=> $rData));
    break;
case 'NewProductComment':
    $sessionkey = $_REQUEST["SessionKey"];
    $productid = $_REQUEST["ProductID"];
    $comment = $_REQUEST["Comment"];
    $idUser = getIDByToken($sessionkey);
    $nPCommentSQL = "insert into comments_dishes (id_user, id_dishes, comment, created_at, updated_at) values ($idUser, $productid, '$comment', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."')";
    $conn = getConnection();
    $stmt = $conn->query ($nPCommentSQL);
    $data = [];
    if($stmt){
        $data["Success"] = "true";
    }else{
        $data["ErrMessage"] = "Message Error";
    }  
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
case 'PayOrder':
    $data = [
        "Amount" => "14",
        "Approved" => "14",
        "AuthorizationCode" => "14",
        "InvoiceNumber" => "14",
        "CardNumber" => "14",
        "ResponseCode" => "14",
        "AuthorizeMessage" => "14",
        "TransactionID" => "14",
        "ResponseReasonCode" => "14", //1 = exito
    ];
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;

case 'RateRestaurantOrder':
    $sessionkey = $_REQUEST["SessionKey"];
    $orderid = $_REQUEST["orderid"];
    $rate = $_REQUEST["rate"];
    $time = $_REQUEST["time"];
    $description = $_REQUEST["description"];
    $values = "";
    if (!empty($_REQUEST["rate"])) {
        $values .= "rate_order = $rate";
    }if (!empty($_REQUEST["time"])) {
        if(!empty($values)) {$values.= ", ";}
        $values .= "duration_delivery = '$time'";

    }if (!empty($_REQUEST["description"])) {
        if(!empty($values)) {$values.= ", ";}
        $values .= ", comment_rate = '$description'";
    }
    $id = getIDByToken($sessionkey);
    $rateSQL = "UPDATE orders SET ".$values.", updated_at = '".date('Y-m-d H:i:s')."' WHERE id = $orderid";
    $conn = getConnection ();
    $stmt = $conn->query ($rateSQL);
    $data = [];
    if ($stmt) {
        $data["Success"] = "Success Process";
    }else{
        $data["ErrMessage"] = "Message Error ".$rateSQL;
    }

    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
case 'RemoveClientAddress':
    $sk = $_REQUEST["SessionKey"];
    $ClientAddressID = $_REQUEST["ClientAddressID"];
    $conn = getConnection ();
    $deleteCardSQL = "DELETE from direcciones_clientes where id = $ClientAddressID";
    $stmt = $conn->query ($deleteCardSQL);
    if($stmt){
        $orderData = $stmt->fetchAll (PDO::FETCH_ASSOC); 
        $data["Success"] = "true";
    }else{
        $data["ErrMessage"] = "Message Error";
    }    
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
case 'SetDefaultClientAddress':
    $sk = $_REQUEST["SessionKey"];
    $clientaddressid = $_REQUEST["ClientAddressID"];
    $conn = getConnection ();    
    $setDefaultSQL = "UPDATE direcciones_clientes SET direcciones_clientes.default = 1 , updated_at = '".date('Y-m-d H:i:s')."' WHERE id = $clientaddressid";
    $stmt = $conn->query ($setDefaultSQL);
    if($stmt){
        $data["Success"] = "true";
    }else{
        $data["ErrMessage"] = "Message Error";
    }    
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
case 'GetBanners':
    $data = [
        "BannerOut" => [[
            "BannerID" => "15",
            "BannerTypeID" => "15",
            "Type" => "15",
            "Title" => "15",
            "Description" => "15",
            "Image" => "15",
            "Enable" => "15",
            "CreationDate" => "15",
        ]],
    ];
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
case 'GetDefaultUserAddress':
    $SessionKey = $_REQUEST['SessionKey'];
    $conn = getConnection();
    $id = getIDByToken($SessionKey);
    /*    
        $data = [
            "ClientAddress" => [
                "ClientAddressID" => "16",
                "ClientID" => "16",
                "Address" => "16",
                "Suit" => "16",
                "City" => "16",
                "State" => "16",
                "ZIPCode" => "16",
                "CrossStreet" => "16",
                "Phone" => "16",
                "AddressName" => "16",
                "CreationDate" => "16",
                "Default" => "16",
                "Enable" => "16",
            ]
        ];
    */
    $data["ClientAddress"] = getAddressID($id, $conn, true);
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
case 'GetProductComments':
    $sessionkey = $_REQUEST["SessionKey"];
    $productid = $_REQUEST["ProductID"];

    $id = getIDByToken($sessionkey);

    $commentProductSQL = "SELECT 
        comments_dishes.id as ProductCommentID,
        comments_dishes.id_dishes as ProductID,
        comments_dishes.id_user as ClientID,
        comments_dishes.comment as Comment,
        comments_dishes.created_at as CreationDate,
        users.username as FullName 
        from comments_dishes, users WHERE users.id = comments_dishes.id_user and comments_dishes.id_dishes = $productid";

    $conn = getConnection();
    $stmt = $conn->query($commentProductSQL);
/*
    $data = [
        "BannerOut" => [ "ProductCommentOut" => [[
                "ProductCommentID" => "16",
                "ProductID" => "16",
                "ClientID" => "16",
                "Rate" => "16",
                "Comment" => "16",
                "CreationDate" => "16",
                "FullName" => "16",
            ]],
        ],
    ];*/
    $data = [
        "BannerOut" => [ "ProductCommentOut" =>[]]];
    if ($stmt) {
        $result = $stmt->fetchAll (PDO::FETCH_ASSOC);
        foreach ($result as $value) {
            array_push($data["BannerOut"]["ProductCommentOut"], $value);
        }
    }
    header ('Content-Type: application/json');
    echo json_encode($data);
    break;
    case "test":
       if(false){$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
               try {
                   //Server settings
                   $mail->SMTPDebug = 2;                                 // Enable verbose debug output
                   $mail->isSMTP();                                      // Set mailer to use SMTP
                   $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
                   $mail->SMTPAuth = true;                               // Enable SMTP authentication
                   $mail->Username = 'crhisdlm94@gmail.com';                 // SMTP username
                   $mail->Password = 'apofis1151';                           // SMTP password
                   $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                   $mail->Port = 587;                                    // TCP port to connect to
       
                   //Recipients
                   $mail->setFrom('crhisdlm94@gmail.com', 'Mailer');
                   $mail->addAddress('cristian.lucumi00@usc.edu.co', 'cristian');     // Add a recipient
                   //$mail->addAddress('ellen@example.com');               // Name is optional
                   $mail->addReplyTo('info@example.com', 'Information');
                   $mail->addCC('cc@example.com');
                   $mail->addBCC('bcc@example.com');
       
                   //Content
                   $mail->isHTML(true);                                  // Set email format to HTML
                   $mail->Subject = 'Here is the subject';
                   $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
                   $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
       
                   $mail->send();
                   echo 'Message has been sent';
               } catch (Exception $e) {
                   echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
               }}

        if(false){
            $conn = getConnection();
         
            try {
                    $conn->beginTransaction();
                    $conn->exec("INSERT INTO `comments` (`id`, `idnota`, `detalle`, `user`, `created_at`, `updated_at`) VALUES (NULL, '1', 'de', '993', '2019-01-10 00:00:00', '2019-01-10 00:00:00')");
                    $id = $conn->lastInsertId();
                    $conn->commit();
                    //echo $sql;
                } catch (Exception $e) {
                  $conn->rollBack();
                  //echo "Failed: " . $e->getMessage();
                }
            var_dump($id);
        }
        break;

}     

function getAddress($SessionKey, $conn, $sd = 'ClientAddress'){
    $colums = "id As ClientAddressID, ";
    $colums .= "idUser As ClientID, ";
    $colums .= "type_address As Address, ";
    $colums .= "apt As Suit, ";
    $colums .= "city As City, ";
    $colums .= "state As State, ";
    $colums .= "zipcode As ZIPCode, ";
    $colums .= "cross_street As CrossStreet, ";
    $colums .= "phone As Phone, ";
    $colums .= "address As AddressName, ";
    $colums .= "created_at As CreationDate, ";
    $colums .= "direcciones_clientes.default As 'Default', ";
    $colums .= "id As 'Enable'"; //pendiente
    //echo $sql;
    try {
        $sql = "select ".$colums." from direcciones_clientes where iduser=".$SessionKey."";
        $stmt = $conn->query($sql);
        if($stmt){
            $result = $stmt->fetchAll (PDO::FETCH_ASSOC);
            $data = array(
                'Success' => 'true',
                $sd => $result,
            );
        }else{
            $data=["errorResponse"=>"query error DB"] ;                            
        }

        header ('Content-Type: application/json');
        
    } catch (Exception $e) {
            $data=["errorResponse"=>"query error DB"] ;                            
        header ('Content-Type: application/json');
    }
    return ($data);
}
function getAddressID($id, $conn, $default = false){
    $colums = "id As ClientAddressID, ";
    $colums .= "idUser As ClientID, ";
    $colums .= "type_address As Address, ";
    $colums .= "apt As Suit, ";
    $colums .= "city As City, ";
    $colums .= "state As State, ";
    $colums .= "zipcode As ZIPCode, ";
    $colums .= "cross_street As CrossStreet, ";
    $colums .= "phone As Phone, ";
    $colums .= "address As AddressName, ";
    $colums .= "created_at As CreationDate, ";
    $colums .= "direcciones_clientes.default As 'Default', ";
    $colums .= "id As 'Enable'"; //pendiente
    //echo $sql;
    try {
        $sql = "select ".$colums." from direcciones_clientes where id=".$id."";
        if($default){
            $sql = "select ".$colums." from direcciones_clientes where iduser=".$id."";
            $sql.=" and direcciones_clientes.default = 1";
        }
        $stmt = $conn->query($sql);
        if($stmt){
            $result = $stmt->fetchAll (PDO::FETCH_ASSOC);
            $data = $result[0];
        }else{
            $data=["errorResponse"=>"query error DB"] ;                            
        }

        header ('Content-Type: application/json');
        
    } catch (Exception $e) {
            $data=["errorResponse"=>"query error DB"] ;                            
        header ('Content-Type: application/json');
    }
    return ($data);
}
function key_random($length = 16)
{
    $string = '';

    while (($len = strlen($string)) < $length) {
        $size = $length - $len;

        $bytes = random_bytes($size);

        $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
    }

    return $string;
}
function getIDByToken($sessionk){
    $sqlID = "select id from users where remember_token = '".$sessionk."'";
                $conn = getConnection ();
                $stmt = $conn->query ($sqlID);
                $id = $stmt->fetchAll (PDO::FETCH_ASSOC);
                return $id[0]["id"];
}
function isLogin($sessionk){
    $sqlID = "select remember_token from users where remember_token = '".$sessionk."'";
                $conn = getConnection ();
                $stmt = $conn->query ($sqlID);
                $id = $stmt->fetchAll (PDO::FETCH_ASSOC);
                return count($id[0]["remember_token"])>0;
}

function getOrderData($getID, $conn){
    
                $orderSQL = "SELECT orders.id as OrderID,
                 orderNum as OrderNum,
                 idrestaurant as RestaurantID,
                 idclient as ClientID,
                 total_price as TotalPriceOrder,
                 paid as Paid,
                 orders.created_at as CreationDate,
                 payment_date as PaymentDate,
                 payment_type as PaymentType,
                 base_price as BasePriceOrder,
                 tax_order as TaxOrder,
                 tip as Tip,
                 delivery_address as DeliveryAddressStr,
                 cupon_id as Cupon,
                 discount_value as DiscountValue,
                 base_price_discount as BasePriceOrderAferDiscount,
                 total_price_discount as TotalPriceOrderComplete,
                 schedule as Schedule,
                 order_type as OrderType,
                 auth_code asReceiptLink,
                 delivery_fee as DeliveryFee
                  from orders where orders.id = $getID";
                /*
                $sql2 = "select 
                quality as Quality,
                value as ProductTotalValue
                idproduct as Product_ID
                from order_detail where idorder = $getID";
                */
                //$stmt = $conn->query ($sql1);
                //$data = $stmt->fetchAll (PDO::FETCH_ASSOC);
                 //$conn = getConnection ();
                /*
                $orderSQL = "SELECT orders.total_price as TotalValuePreorder, 
                orders.base_price as BaseValuePreorder,
                orders.tax_order as TaxValuePreorder,
                orders.id as PreOrderID from orders WHERE orders.id = $getID";
                */
                $stmt = $conn->query ($orderSQL);
                $orderData = $stmt->fetchAll (PDO::FETCH_ASSOC);

                $productOrderSQL = "SELECT id,
                 idorder,
                 idproduct,
                 quantity as Quantity,
                 value,
                 created_at,
                 updated_at,
                 (quantity*value) as ProductTotalValue from order_detail where idorder = $getID";
                $stmt2 = $conn->query ($productOrderSQL);
                $productOrderData = $stmt2->fetchAll (PDO::FETCH_ASSOC);
                $orderData[0]["ProductOrder"] = array();
                $orderData[0]['ReceiptLink']='';
                $orderData[0]['DeliveryFee']='';
                foreach ($productOrderData as $key => $value) {
                    if(isset($value["idproduct"])){
                        $idProduct = $value["idproduct"];
                        $productSQL = "SELECT id, idcategoria, name, description, value, 'order', created_at from menu_dishes where menu_dishes.id = $idProduct";
                        $stmt3 = $conn->query ($productSQL);
                        $productData = $stmt3->fetchAll (PDO::FETCH_ASSOC);
                        $productData[0]["ProductPropertyCart"] = array(array(
                                        "ProductPropertyID" => "2222",
                                        "ProductID" => "2222",
                                        "FatherProductPropertyID" => "2222",
                                        "Name" => "2222",
                                        "PropertyType" => "2222",
                                        "GroupingTypeID" => "2222",
                                        "GroupingType" => "2222",
                                        "PropertyValueCart" => array(array(
                                            "PropertyValueID" => "22222",
                                            "ProductPropertyID" => "22222",
                                            "ProductID" => "22222",
                                            "Label" => "22222",
                                            "Price" => "22222",
                                            "Cant" => "22222",
                                            "TotalPrice" => "22222",
                                        )),
                                    ));
                        $productOrderData[$key]["Product"] = array();
                        $productOrderData[$key]["Product"] = $productData[0];
                        array_push($orderData[0]["ProductOrder"], $productOrderData[$key] );
                    }
                    //var_dump($productOrderData);
                }
                return $orderData[0];
}
function getRestaurantData($restaurantID, $conn){
$restaurantData = [];
                
                    $restautantIdOrder = $restaurantID;
                    $restaurantSQL = "SELECT id as RestaurantID,
                    restaurant_chain as RestaurantChainID,
                    name as Name,
                    description as Description,
                    image as Image,
                    delivery_area as DeliveryArea,
                    address as Address,
                    phone_rest as Phone,
                    rating as Rate,
                    citytax as Tax,
                    longitud as Longitude,
                    latitud as Latitude,
                    zipcode as Zip,
                    distance as Distance,
                    web as Web,
                    created_at as CreationDate,
                    delivery as Delivery,
                    pickup as Pickup,
                    YelpID as YelpId
                    from restaurants where id = $restautantIdOrder
                    ";
                    $stmt4 = $conn->query ($restaurantSQL);
                    $restaurantData = $stmt4->fetchAll (PDO::FETCH_ASSOC);
                
                return $restaurantData[0];
}
function getUserByID($conn, $token){
    $sql = "select id as usuID, users.UserID AS ClientID, users.username AS UserName, users.username AS FullName, users.password AS Password, users.email AS eMail, users.phone AS Cellphone, users.created_at As CreationDate,  users.id_profile from users where users.remember_token='$token'";
                    $stmt = $conn->query ($sql);
                    $data = $stmt->fetchAll (PDO::FETCH_ASSOC);
                    
                    //$sql2 = "select concat_ws(', ', payments_profiles.last_name,  payments_profiles.first_name) As FullName, address AS Address from payments_profiles where payments_profiles.id_profile =".$data[0]["id_profile"]."";    
                    $sql2 = "select address AS Address from payments_profiles where payments_profiles.id_profile =".$data[0]["id_profile"]."";    
                    $stmt2 = $conn->query ($sql2);
                    $data2 = $stmt2->fetchAll (PDO::FETCH_ASSOC);
                    //echo json_encode(!isset($data2));
                    $paymentData = array(
                        "FullName" => "",
                        "FacebookID" => "",
                        "TwitterID" => "",
                        "Address" => "",
                    );
                    if(isset($data2)){
                        foreach ($data2[0] as $key => $value) {
                            $data[0][$key] = $value;
                        }
                    }
                    try {                                          

                    $sessionR = array(
                        "SessionKey"=>$token,
                        "TimeExpiration"=>"15",
                        "ClientID"=>$data[0]["usuID"],
                    );
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                    //array_push($data[0], $paymentData);
                    $response = array("User" => $data[0], "Session" => $sessionR);
                    return $response;
}
function isPassword($conn, $token, $password){
    $sql = "select id as usuID, users.password AS Password from users where users.remember_token='$token'";
                    $stmt = $conn->query ($sql);
                    $data = $stmt->fetchAll (PDO::FETCH_ASSOC);
                    return $data[0]["Password"] == $password;
}

function sendMailRP($to, $subject, $newPass){
//    $to = "somebody@example.com, somebodyelse@example.com";
$from = "webmaster@example.com";
$message = "
<html>
<head>
<title>Reset Password</title>
</head>
<body>
<p>Reset Password</p>
<table>
<tr>
<th>Email</th>
<th>Password</th>
</tr>
<tr>
<td>".$to."</td>
<td>".$newPass."</td>
</tr>
</table>
</body>
</html>
";

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: <'.$from.'>' . "\r\n";
//$headers .= 'Cc: myboss@example.com' . "\r\n";

mail($to,$subject,$message,$headers);
}
function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function getDishesRestaurantFavorite($menuRestaurantID, $conn, $pag = 0, $coupon = false){
//AS ProductImg
//AS Enable
    $dataforPage = 80;
    $offset = $dataforPage*$pag;
    $condition = $coupon ? "restaurant_menu.coupons = 1" : "menu_dishes.favorite = 1";

    $menuRestaurantDishesSQL = "SELECT 
    menu_dishes.id AS ProductID,
    menu_dishes.name AS Name,
    menu_dishes.name AS Description,
    menu_dishes.value AS Value,
    menu_categorias.id AS CategoryID,
    menu_categorias.name AS Category,
    menu_dishes.created_at AS CreationDate,
    menu_dishes.order AS ProductOrder,
    menu_categorias.order AS CategoryOrder,
    menu_categorias.idmenu AS MenuID
    FROM restaurant_menu, menu_categorias, menu_dishes 
    WHERE restaurant_menu.idrestaurant = $menuRestaurantID AND menu_categorias.idmenu = restaurant_menu.id AND menu_categorias.id = menu_dishes.idcategoria and $condition";
    //WHERE restaurant_menu.idrestaurant = $menuRestaurantID AND menu_categorias.idmenu = restaurant_menu.id AND menu_categorias.id = menu_dishes.idcategoria and $condition LIMIT ".$dataforPage." OFFSET ".$offset;

    $stmt = $conn->query($menuRestaurantDishesSQL);
    $data = [];
    if ($stmt) {
        $data = $stmt->fetchAll (PDO::FETCH_ASSOC);
    }
    return $data;
}
function getDishesRestaurantMenu($menuRestaurantID, $conn, $pag = 0){
//AS ProductImg
//AS Enable
    $offset = 85*$pag;
    $menuRestaurantDishesSQL = "SELECT 
    menu_dishes.id AS ProductID,
    menu_dishes.name AS Name,
    menu_dishes.name AS Description,
    menu_dishes.value AS Value,
    menu_categorias.id AS CategoryID,
    menu_categorias.name AS Category,
    menu_dishes.created_at AS CreationDate,
    menu_dishes.order AS ProductOrder,
    menu_categorias.order AS CategoryOrder,
    menu_categorias.idmenu AS MenuID
    FROM restaurant_menu, menu_categorias, menu_dishes 
    WHERE restaurant_menu.id = $menuRestaurantID AND menu_categorias.idmenu = restaurant_menu.id AND menu_categorias.id = menu_dishes.idcategoria";
    $stmt = $conn->query($menuRestaurantDishesSQL);
    $data = [];
    if ($stmt) {
        $data = $stmt->fetchAll (PDO::FETCH_ASSOC);
    }
    return $data;
}
function getPropertiesProduct($dishesID, $conn){
    $propertyProductSQL = "SELECT 
    id,
    iddishes,
    name,
    quantity,
    operation,
    mandatory,
    menu_property.order,
    created_at,
    updated_at 
    FROM menu_property WHERE iddishes = $dishesID";
    //echo $propertyProductSQL."\n";

    $stmt = $conn->query($propertyProductSQL);
    $data = [];
    if ($stmt) {
        $data = $stmt->fetchAll (PDO::FETCH_ASSOC);
    }
    return $data;
}
function getMenuAdds($propertyID, $conn){
    $propertyProductSQL = "SELECT 
    id,
    idproperty,
    name,
    price,
    menu_adds.order,
    created_at,
    updated_at 
    FROM menu_adds WHERE idproperty = $propertyID";
    $stmt = $conn->query($propertyProductSQL);
    $data = [];
    if ($stmt) {
        $data = $stmt->fetchAll (PDO::FETCH_ASSOC);
    }
    return $data;
}