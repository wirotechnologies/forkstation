<?php

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
                    $restaurantsMenus[] = $row;
                }

                foreach ($dataRestaurant as $row2) {
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
            header ('Content-Type: application/json');
            echo json_encode(
                array("User"=>array("ClientID"=>"1",
                    "UserName"=>"User Name",
                    "Password"=>"",
                    "FullName"=>"Full name",
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
            break;
            
            case 'GetOrder':
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
                        'ProductOrder'=>array(
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

                                ),
                            ),

                        ),
                        'ReceiptLink'=>'',
                        'DeliveryFee'=>'',

                    ),
                    'Restaurant'=>array(
                        'RestaurantID'=>'',
                        'RestaurantChainID'=>'',
                        'Name'=>'',
                        'Description'=>'',
                        'Image'=>'',
                        'Tips'=>'',
                        'DeliveryArea'=>'',
                        'DeliveryTime'=>'',
                        'OurKitchen'=>'',
                        'Address'=>'',
                        'Phones'=>'',
                        'Rate'=>'',
                        'MinimumOrder'=>'',
                        'Tax'=>'',
                        'DeliveryCost'=>'',
                        'Longitude'=>'',
                        'Latitude'=>'',
                        'Zip'=>'',
                        'Distance'=>'',
                        'Web'=>'',
                        'CreationDate'=>'',
                        'Delivery'=>'',
                        'Pickup'=>'',
                        'Enable'=>'',
                        'YelpId'=>'',
                        'Dividends_percent'=>'',
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
break;
case 'AddClientAddress':
    $SessionKey = $_REQUEST['SessionKey'];
    $Address = $_REQUEST['Address'];
    $Suit = $_REQUEST['Suit'];
    $City = $_REQUEST['City'];
    $State = $_REQUEST['State'];
    $ZIPCode = $_REQUEST['ZIPCode'];
    $CrossStreet = $_REQUEST['CrossStreet'];
    $Phone = $_REQUEST['Phone'];
    $AddressName = $_REQUEST['AddressName'];
    header ('Content-Type: application/json');

    $data=array(
        'Success' => 'true',
        'ClientAddress' => array(
            'ClientAddressID' => 12,
            'ClientID' => 12,
            'Address' => 12,
            'Suit' => 12,
            'City' => 12,
            'State' => 12,
            'ZIPCode' => 12,
            'CrossStreet' => 12,
            'Phone' => 12,
            'AddressName' => 12,
            'CreationDate' => 12,
            'Default' => 12,
            'Enable' => 12,
        ),
    );
    echo json_encode($data);

break;

case 'CreateShoppingCart':
    header ('Content-Type: application/json');

    $data=array(
        "TotalValuePreorder" => "2",
        "BaseValuePreorder" => "2",
        "TaxValuePreorder" => "2",
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
    echo json_encode($data);

break;

case 'GetOrderRecalcPay':
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
        'ReceiptLink'=>'3',
        'DeliveryFee'=>'3',
    ),

);
header ('Content-Type: application/json');
echo json_encode($data);

break;
}