<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
$postData = file_get_contents('php://input');
    //$xml = simplexml_load_string($postData);

$xml = str_replace('soap:', '', $postData);
$obj = simplexml_load_string($xml);
$jsonData = json_decode($obj->Body->GetRestaurants->JsonGetRestaurants);
$restaurantZipCode = $jsonData->zipcode;
$restaurantLatitude = $jsonData->latitude;
$restaurantLongitude = $jsonData->longitude;
$restaurantCity = $jsonData->city;
$restaurantTips = $jsonData->tips;

$op = $_REQUEST['op'];
$time = $_REQUEST['time'];

    //$latitude = $_POST['latitude'];
$latitude = $jsonData->latitude;
    //$latitude = '25.802714';
    //$longitude = $_POST['longitude'];
$longitude = $jsonData->longitude;
    //$longitude = '-80.199134';
$address = $jsonData->address;
    //$address = "48 Southwest 12th Street, Miami, FL, USA";

    //echo $address;

    $distance = 5; //in kilometers

    function getCoordinatesFromAddress($address)
    {
        $address = preg_replace('/\#/', '', preg_replace('/\s+/', '+', $address));
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=AIzaSyB9OZjpWlUSwSddcLdW8yw-rGjum9_qBpQ";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        $result = json_decode($result);
        if($result->status == 'OK'){

            $data = [
                'code' => 0,
                'lat' => $result->results[0]->geometry->location->lat,
                'lng' => $result->results[0]->geometry->location->lng
            ];

        } else {

            $data = [
                'code' => 1,
                'status' => $result->status,
                'msg' => $result->error_message
            ];

        }

        curl_close($ch);

        return $data;
    }

    if (!isset($latitude) && !isset($longitude) && isset($address)) {

        $data = getCoordinatesFromAddress($address);

        if ($data['code'] == 0) {
            $latitude = $data['lat'];
            $longitude = $data['lng'];
        } else {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }

    }

    $urlServices = "http://backservices.forkstation.com/ForkstationServices.php";
    //$urlServices = "http://localhost/forkstation/ForkstationServices.php";
    $data = [];

    /* DATOS ESTATICOS PARA PRUEBAS */

    $restaurantMenuProducts = [
        [
            'product_id' => 1,
            'name' => 'Name of Product',
            'description' => 'Desc',
            'value' => '3',
            'image' => 'Img',
            'enable' => 'true',
            'category_id' => 'cat_id',
            'category' => 'category',
            'create_at' => 'creation',
            'product_order' => 'prod order',
            'category_order' => 'cat order'
        ],
        [
            'product_id' => 2,
            'name' => 'Name of Product',
            'description' => 'Description',
            'value' => '2.5',
            'image' => 'Img',
            'enable' => 'true',
            'category_id' => 'cat_id',
            'category' => 'category',
            'create_at' => 'creation',
            'product_order' => 'prod order',
            'category_order' => 'cat order'
        ],
        [
            'product_id' => 3,
            'name' => 'Name of Product',
            'description' => 'Desc',
            'value' => '1.5',
            'image' => 'Img',
            'enable' => 'true',
            'category_id' => 'cat_id',
            'category' => 'category',
            'create_at' => 'creation',
            'product_order' => 'prod order',
            'category_order' => 'cat order'
        ]
    ];

    $restaurantMenuProductsProperty = [
        [
            'product_property_id' => 1,
            'product_property_product_id' => 1,
            'product_property_father_property_id' => 1,
            'name' => 'name',
            'property_type' => 'Prop Type',
            'grouping_type_id' => 'Group Type ID',
            'grouping_type' => 'Group Type'
        ],
        [
            'product_property_id' => 2,
            'product_property_product_id' => 2,
            'product_property_father_property_id' => 2,
            'name' => 'name',
            'property_type' => 'Prop Type',
            'grouping_type_id' => 'Group Type ID',
            'grouping_type' => 'Group Type'
        ]
    ];

    $restaurantMenuProductsPropertyValues = [
        [
            'product_property_value_id' => 1,
            'product_property_value_property_id' => 1,
            'product_property_value_product_id' => 1,
            'label' => 'Label',
            'price' => 'Price',
        ],
        [
            'product_property_value_id' => 2,
            'product_property_value_property_id' => 2,
            'product_property_value_product_id' => 2,
            'label' => 'Label',
            'price' => 'Price',
        ]
    ];

    $listCouponsProductsMenus = [
        [
            'product_id' => '1',
            'name' => 'Product Name',
            'description' => 'Product Description',
            'image' => 'Product Image',
            'enable' => 'true',
            'categpry_id' => '1',
            'created_at' => 'Creation Date',
            'product_order' => 'Product Order',
            'category_order' => 'Category Order',
            'menu_id' => '1'
        ],
        [
            'product_id' => '2',
            'name' => 'Product Name',
            'description' => 'Product Description',
            'image' => 'Product Image',
            'enable' => 'true',
            'categpry_id' => '2',
            'created_at' => 'Creation Date',
            'product_order' => 'Product Order',
            'category_order' => 'Category Order',
            'menu_id' => '2'
        ],
        [
            'product_id' => '3',
            'name' => 'Product Name',
            'description' => 'Product Description',
            'image' => 'Product Image',
            'enable' => 'true',
            'categpry_id' => '3',
            'created_at' => 'Creation Date',
            'product_order' => 'Product Order',
            'category_order' => 'Category Order',
            'menu_id' => '3'
        ]
    ];

    $listCouponsProductsPropertys = [
        [
            'product_property_id' => '1',
            'product_property_product_id' => '1',
            'product_property_Father_product_property_id' => '1',
            'name' => 'Product Property Name',
            'type' => 'Product Property Type',
            'grouping_type_id' => 'Grouping Type ID',
            'grouping_type' => 'Grouping Type',
        ],
        [
            'product_property_id' => '2',
            'product_property_product_id' => '2',
            'product_property_Father_product_property_id' => '2',
            'name' => 'Product Property Name',
            'type' => 'Product Property Type',
            'grouping_type_id' => 'Grouping Type ID',
            'grouping_type' => 'Grouping Type',
        ],
        [
            'product_property_id' => '3',
            'product_property_product_id' => '3',
            'product_property_Father_product_property_id' => '3',
            'name' => 'Product Property Name',
            'type' => 'Product Property Type',
            'grouping_type_id' => 'Grouping Type ID',
            'grouping_type' => 'Grouping Type',
        ],
    ];

    $listCouponsProductsPropertysValues = [
        [
            'property_value_id' => '1',
            'property_value_product_property_id' => '1',
            'property_value_product_id' => '1',
            'label' => 'Label',
            'price' => 'Price'
        ]
    ];

    $paymentTypes = [
        [
            'payment_type_id' => '1',
            'payment_type' => 'Payment Type',
            'payment_icon' => 'Icon',
            'enable' => 'true',
        ],
        [
            'payment_type_id' => '2',
            'payment_type' => 'Payment Type 2',
            'payment_icon' => 'Icon 2',
            'enable' => 'true',
        ],
        [
            'payment_type_id' => '3',
            'payment_type' => 'Payment Type 3',
            'payment_icon' => 'Icon 3',
            'enable' => 'true',
        ]
    ];

    /* DATOS ESTATICOS PARA PRUEBAS */


    switch ($op) {

        case 'GetRestaurants':

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlServices);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('latitude' => $latitude, 'longitude' => $longitude, 'op' => $op)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $data = $result;

        $restaurants = [];
        $i = 0;

        $data = json_decode($data, true);

        foreach($data as $row) {

            $restaurant_distance = round(number_format($row['distance'], 3, '.', ''), 1);
            $restaurant_delivery_area = $row['delivery_distance'];

            if($restaurant_distance >= ($restaurant_delivery_area * 1000))
                continue;

            $restaurants[] = $row;
            $restaurants[$i]['miles'] = (String)round($row['distance'] / 1609.344, 1);
            $restaurants[$i]['kilometers'] = (String)round($row['distance'] / 1000, 1);
            $i++;
        }

        $dom = new DOMDocument();
        $nodoRoot = $dom->createElement('RestaurantData');
        $dom->appendChild($nodoRoot);

        foreach($restaurants as $restaurant) {
            $restaurantOut = $dom->createElement('RestaurantOut');

            /* Restaurant Info */
            $restaurantDataChainID = $dom->createElement('RestaurantChainID', $restaurant['restaurant_chain']);
            $restaurantDataID = $dom->createElement('RestaurantID', $restaurant['id']);
            $restaurantDataName = $dom->createElement('Name', $restaurant['name']);
            $restaurantDataDescription = $dom->createElement('Description', $restaurant['description']);
            $restaurantDataImage = $dom->createElement('Image', $restaurant['image']);
            $restaurantDataTips = $dom->createElement('Tips', $restaurant['tips']);
            $restaurantDataDeliveryArea = $dom->createElement('DeliveryArea', $restaurant['delivery_area']);
            $restaurantDataDeliveryTime = $dom->createElement('DeliveryTime', $restaurant['delivery_time']);
            $restaurantDataOurKitchen = $dom->createElement('OurKitchen', $restaurant['our_kitchen']);
            $restaurantDataAddress = $dom->createElement('Address', $restaurant['address']);
            $restaurantDataPhones = $dom->createElement('Phones', $restaurant['phone_rest']);
            $restaurantDataRate = $dom->createElement('Rate', $restaurant['rate']);
            $restaurantDataMinimunOrder = $dom->createElement('MinimunOrder', $restaurant['minimun_order']);
            $restaurantDataTax = $dom->createElement('Tax', $restaurant['tax']);
            $restaurantDataDeliveryCost = $dom->createElement('DeliveryCost', $restaurant['delivery_cost']);
            $restaurantDataLongitude = $dom->createElement('Longitude', $restaurant['longitude']);
            $restaurantDataLatitude = $dom->createElement('Latitude', $restaurant['latitude']);
            $restaurantDataZip = $dom->createElement('Zip', 33067);
            $restaurantDataDistance = $dom->createElement('Distance', 1);
            $restaurantDataWeb = $dom->createElement('Web', $restaurant['web']);
            $restaurantDataCreationDate = $dom->createElement('CreationDate', $restaurant['created_at']);
            $restaurantDataDelivery = $dom->createElement('Delivery', 'true');
            $restaurantDataPickup = $dom->createElement('Pickup', $restaurant['pickup']);
            $restaurantDataEnable = $dom->createElement('Enable', $restaurant['enable']);
            $restaurantDataYelpID = $dom->createElement('YelpID', $restaurant['YelpID']);
            $restaurantDataDividendsPercent = $dom->createElement('Dividends_percent', $restaurant['dividends_percent']);

            $restaurantOut->appendChild($restaurantDataChainID);
            $restaurantOut->appendChild($restaurantDataID);
            $restaurantOut->appendChild($restaurantDataName);
            $restaurantOut->appendChild($restaurantDataDescription);
            $restaurantOut->appendChild($restaurantDataImage);
            $restaurantOut->appendChild($restaurantDataTips);
            $restaurantOut->appendChild($restaurantDataDeliveryArea);
            $restaurantOut->appendChild($restaurantDataDeliveryTime);
            $restaurantOut->appendChild($restaurantDataOurKitchen);
            $restaurantOut->appendChild($restaurantDataAddress);
            $restaurantOut->appendChild($restaurantDataPhones);
            $restaurantOut->appendChild($restaurantDataRate);
            $restaurantOut->appendChild($restaurantDataMinimunOrder);
            $restaurantOut->appendChild($restaurantDataTax);
            $restaurantOut->appendChild($restaurantDataDeliveryCost);
            $restaurantOut->appendChild($restaurantDataLongitude);
            $restaurantOut->appendChild($restaurantDataLatitude);
            $restaurantOut->appendChild($restaurantDataZip);
            $restaurantOut->appendChild($restaurantDataDistance);
            $restaurantOut->appendChild($restaurantDataWeb);
            $restaurantOut->appendChild($restaurantDataCreationDate);
            $restaurantOut->appendChild($restaurantDataDelivery);
            $restaurantOut->appendChild($restaurantDataPickup);
            $restaurantOut->appendChild($restaurantDataEnable);
            $restaurantOut->appendChild($restaurantDataYelpID);
            $restaurantOut->appendChild($restaurantDataDividendsPercent);

            /* Favorite Disch*/
            $listFavoriteDisch = $dom->createElement('ListFavoriteDisch');

            foreach ($restaurantMenuProducts as $menuProduct) {

                /* --- Product Menu */
                $productMenuOut = $dom->createElement('ProductMenuOut');

                $productID = $dom->createElement('ProductID', 1);
                $productName = $dom->createElement('Name', 1);
                $productDescription = $dom->createElement('Description', 1);
                $productValue = $dom->createElement('Value', 1);
                $productImg = $dom->createElement('ProductImg', 1);
                $productEnable = $dom->createElement('Enable', 1);
                $productCategoryID = $dom->createElement('CategoryID', 1);
                $productCategory = $dom->createElement('Category', 1);
                $productCreationDate = $dom->createElement('CreationDate', 1);
                $productOrder = $dom->createElement('ProductOrder', 1);
                $productCategoryOrder = $dom->createElement('CategoryOrder', 1);
                $productMenuID = $dom->createElement('MenuID', 1);

                $productMenuOut->appendChild($productID);
                $productMenuOut->appendChild($productName);
                $productMenuOut->appendChild($productDescription);
                $productMenuOut->appendChild($productValue);
                $productMenuOut->appendChild($productImg);
                $productMenuOut->appendChild($productEnable);
                $productMenuOut->appendChild($productCategoryID);
                $productMenuOut->appendChild($productCategory);
                $productMenuOut->appendChild($productCreationDate);
                $productMenuOut->appendChild($productOrder);
                $productMenuOut->appendChild($productCategoryOrder);
                $productMenuOut->appendChild($productMenuID);

                foreach ($restaurantMenuProductsProperty as $menuProductProperty) {

                    /* --- Product Property */
                    $productPropertyOut = $dom->createElement('ProductPropertyOut');

                    $productPropertyID = $dom->createElement('ProductPropertyID', 2);
                    $productPropertyProductID = $dom->createElement('ProductID', 2);
                    $productPropertyFatherProductPropertyID = $dom->createElement('FatherProductPropertyID', 2);
                    $productPropertyName = $dom->createElement('Name', 2);
                    $productPropertyType = $dom->createElement('PropertyType', 2);
                    $productPropertyGroupingTypeID = $dom->createElement('GroupingTypeID', 2);
                    $productPropertyGroupingType = $dom->createElement('GroupingType', 2);

                    $productPropertyOut->appendChild($productPropertyID);
                    $productPropertyOut->appendChild($productPropertyProductID);
                    $productPropertyOut->appendChild($productPropertyFatherProductPropertyID);
                    $productPropertyOut->appendChild($productPropertyName);
                    $productPropertyOut->appendChild($productPropertyType);
                    $productPropertyOut->appendChild($productPropertyGroupingTypeID);
                    $productPropertyOut->appendChild($productPropertyGroupingType);

                    foreach ($restaurantMenuProductsPropertyValues as $productPropertyValue) {

                        /* --- Product Property Value */
                        $productPropertyValueOut = $dom->createElement('PropertyValueOut');

                        $productPropertyValueID = $dom->createElement('PropertyValueID', 3);
                        $productPropertyValueProductPropertyID = $dom->createElement('ProductPropertyID', 3);
                        $productPropertyValueProductID = $dom->createElement('ProductID', 3);
                        $productPropertyValueLabel = $dom->createElement('Label', 3);
                        $productPropertyValuePrice = $dom->createElement('Price', 3);

                        $productPropertyValueOut->appendChild($productPropertyValueID);
                        $productPropertyValueOut->appendChild($productPropertyValueProductPropertyID);
                        $productPropertyValueOut->appendChild($productPropertyValueProductID);
                        $productPropertyValueOut->appendChild($productPropertyValueLabel);
                        $productPropertyValueOut->appendChild($productPropertyValuePrice);

                        $productPropertyOut->appendChild($productPropertyValueOut);

                    }

                    $productMenuOut->appendChild($productPropertyOut);

                }

                $listFavoriteDisch->appendChild($productMenuOut);

            }

            /* --- List Coupons */
            $listCouponsOut = $dom->createElement('ListCoupons');

            foreach ($listCouponsProductsMenus as $couponProductMenu) {

                /* --- List Coupons Product Menu */
                $listCouponsProductMenuOut = $dom->createElement('ProductMenuOut');

                $listCouponsProductID = $dom->createElement('ProductID', $couponProductMenu['product_id']);
                $listCouponsProductName = $dom->createElement('Name', $couponProductMenu['name']);
                $listCouponsProductDescription = $dom->createElement('Description', $couponProductMenu['description']);
                $listCouponsProductValue = $dom->createElement('Value', $couponProductMenu['value']);
                $listCouponsProductImg = $dom->createElement('ProductImg', $couponProductMenu['image']);
                $listCouponsProductEnable = $dom->createElement('Enable', $couponProductMenu['enable']);
                $listCouponsProductCategoryID = $dom->createElement('CategoryID', $couponProductMenu['category_id']);
                $listCouponsProductCategory = $dom->createElement('Category', $couponProductMenu['category']);
                $listCouponsProductCreationDate = $dom->createElement('CreationDate', $couponProductMenu['created_at']);
                $listCouponsProductOrder = $dom->createElement('ProductOrder', $couponProductMenu['product_order']);
                $listCouponsProductCategoryOrder = $dom->createElement('CategoryOrder', $couponProductMenu['category_order']);
                $listCouponsProductMenuID = $dom->createElement('MenuID', $couponProductMenu['menu_id']);

                $listCouponsProductMenuOut->appendChild($listCouponsProductID);
                $listCouponsProductMenuOut->appendChild($listCouponsProductName);
                $listCouponsProductMenuOut->appendChild($listCouponsProductDescription);
                $listCouponsProductMenuOut->appendChild($listCouponsProductValue);
                $listCouponsProductMenuOut->appendChild($listCouponsProductImg);
                $listCouponsProductMenuOut->appendChild($listCouponsProductEnable);
                $listCouponsProductMenuOut->appendChild($listCouponsProductCategoryID);
                $listCouponsProductMenuOut->appendChild($listCouponsProductCategory);
                $listCouponsProductMenuOut->appendChild($listCouponsProductCreationDate);
                $listCouponsProductMenuOut->appendChild($listCouponsProductOrder);
                $listCouponsProductMenuOut->appendChild($listCouponsProductCategoryOrder);
                $listCouponsProductMenuOut->appendChild($listCouponsProductMenuID);

                foreach($listCouponsProductsPropertys as $couponProductProperty) {

                    /* --- List Coupons Product Property */
                    $listCouponsProductPropertyOut = $dom->createElement('ProductPropertyOut');

                    $listCouponsProductPropertyID = $dom->createElement('ProductPropertyID', $couponProductProperty['product_property_id']);
                    $listCouponsProductPropertyProductID = $dom->createElement('ProductID', $couponProductProperty['product_property_product_id']);
                    $listCouponsProductPropertyFatherProductPropertyID = $dom->createElement('FatherProductPropertyID', $couponProductProperty['product_property_Father_product_property_id']);
                    $listCouponsProductPropertyName = $dom->createElement('Name', $couponProductProperty['name']);
                    $listCouponsProductPropertyType = $dom->createElement('PropertyType', $couponProductProperty['type']);
                    $listCouponsProductPropertyGroupingTypeID = $dom->createElement('GroupingTypeID', $couponProductProperty['grouping_type_id']);
                    $listCouponsProductPropertyGroupingType = $dom->createElement('GroupingType', $couponProductProperty['grouping_type']);

                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyID);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyProductID);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyFatherProductPropertyID);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyName);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyType);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyGroupingTypeID);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyGroupingType);

                    foreach ($listCouponsProductsPropertysValues as $couponProductPropertyValue) {

                        /* --- List Coupons Product Property Value */
                        $listCouponsProductPropertyValueOut = $dom->createElement('PropertyValueOut');

                        $listCouponsProductPropertyValueID = $dom->createElement('PropertyValueID', $couponProductPropertyValue['property_value_id']);
                        $listCouponsProductPropertyValueProductPropertyID = $dom->createElement('ProductPropertyID', $couponProductPropertyValue['property_value_product_property_id']);
                        $listCouponsProductPropertyValueProductID = $dom->createElement('ProductID', $couponProductPropertyValue['property_value_product_id']);
                        $listCouponsProductPropertyValueLabel = $dom->createElement('Label', $couponProductPropertyValue['label']);
                        $listCouponsProductPropertyValuePrice = $dom->createElement('Price', $couponProductPropertyValue['price']);

                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValueID);
                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValueProductPropertyID);
                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValueProductID);
                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValueLabel);
                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValuePrice);

                        $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyValueOut);

                    }

                    $listCouponsProductMenuOut->appendChild($listCouponsProductPropertyOut);

                }

                $listCouponsOut->appendChild($listCouponsProductMenuOut);

            }

            $restaurantOut->appendChild($listCouponsOut);

            /* --- Filters */
            $filters = $dom->createElement('Filters');

                //REVISAR ESTE PUNTO, YA QUE AQUI VALIDA SI LOS MUESTRA O NO
            $filtersDelivery = $dom->createElement('Delivery', true);
            $filtersPickup = $dom->createElement('Pickup', true);
            $filtersFreeDelivery = $dom->createElement('FreeDelivery', true);
            $filtersOpenNow = $dom->createElement('OpenNow', 'true');
            $filtersHaveCoupons = $dom->createElement('HaveCoupons', true);

            $filters->appendChild($filtersDelivery);
            $filters->appendChild($filtersPickup);
            $filters->appendChild($filtersFreeDelivery);
            $filters->appendChild($filtersOpenNow);
            $filters->appendChild($filtersHaveCoupons);

            $restaurantOut->appendChild($filters);

            /* --- Schelude */
            $scheduleOut = $dom->createElement('ScheduleOut');

            $scheduleID = $dom->createElement('ScheduleID', 7);
            $scheduleMonday = $dom->createElement('Monday', true);
            $scheduleTuesday = $dom->createElement('Tuesday', true);
            $scheduleWednesday = $dom->createElement('Wednesday', 7);
            $scheduleThursday = $dom->createElement('Thursday', 7);
            $scheduleFriday = $dom->createElement('Friday', 7);
            $scheduleSaturday = $dom->createElement('Saturday', 7);
            $scheduleSunday = $dom->createElement('Sunday', 7);

            $scheduleOut->appendChild($scheduleID);
            $scheduleOut->appendChild($scheduleMonday);
            $scheduleOut->appendChild($scheduleTuesday);
            $scheduleOut->appendChild($scheduleWednesday);
            $scheduleOut->appendChild($scheduleThursday);
            $scheduleOut->appendChild($scheduleFriday);
            $scheduleOut->appendChild($scheduleSaturday);
            $scheduleOut->appendChild($scheduleSunday);

            $restaurantOut->appendChild($scheduleOut);

            $productPropertyOut->appendChild($productPropertyValueOut);
            $productMenuOut->appendChild($productPropertyOut);
            $listFavoriteDisch->appendChild($productMenuOut);
            $restaurantOut->appendChild($listFavoriteDisch);

            $nodoRoot->appendChild($restaurantOut);
        }

        Header('Content-type: text/xml');
        echo $dom->saveXML();

        break;

        case 'GetRestaurantMenu':

        $jsonData = json_decode($obj->Body->GetRestaurantMenu->JsonGetRestaurantMenu);

        $restaurantID = $jsonData->RestaurantID;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlServices);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('restaurant_id' => $restaurantID, 'op' => $op)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $restaurantInfo = json_decode($result, true);

        $restaurantMenu = $restaurantInfo['RestaurantMenuOut'];
        $restaurantData = $restaurantInfo['RestaurantOut'];

        $dom = new DOMDocument();
        $nodoRoot = $dom->createElement('RestaurantMenuData');
        $dom->appendChild($nodoRoot);

        foreach($restaurantMenu as $menu) {

            $restaurantMenuOut = $dom->createElement('RestaurantMenuOut');

            $menuRestaurantID = $dom->createElement('RestaurantID', $menu['idrestaurant']);
            $menuRestaurantChainID = $dom->createElement('RestaurantChainID', 29);
            $menuRestaurantDescription = $dom->createElement('Description', $menu['name']);
            $menuRestaurantIsMaster = $dom->createElement('IsMaster', $menu['master']);
            $menuRestaurantMenuID = $dom->createElement('MenuID', $menu['id']);

            $restaurantMenuOut->appendChild($menuRestaurantID);
            $restaurantMenuOut->appendChild($menuRestaurantChainID);
            $restaurantMenuOut->appendChild($menuRestaurantDescription);
            $restaurantMenuOut->appendChild($menuRestaurantIsMaster);
            $restaurantMenuOut->appendChild($menuRestaurantMenuID);

            foreach($restaurantMenuProducts as $menuProduct) {

                $restaurantMenuProductOut = $dom->createElement('ProductOut');

                $menuRestaurantProductID = $dom->createElement('ProductID', $menuProduct['product_id']);
                $menuRestaurantProductName = $dom->createElement('Name', $menuProduct['name']);
                $menuRestaurantProductDescription = $dom->createElement('Description', $menuProduct['description']);
                $menuRestaurantProductValue = $dom->createElement('Value', $menuProduct['value']);
                $menuRestaurantProductImage = $dom->createElement('ProductImg', $menuProduct['image']);
                $menuRestaurantProductEnable = $dom->createElement('Enable', $menuProduct['enable']);
                $menuRestaurantProductCategoryID = $dom->createElement('CategoryID', $menuProduct['category_id']);
                $menuRestaurantProductCategory = $dom->createElement('Category', $menuProduct['']);
                $menuRestaurantProductCreationDate = $dom->createElement('CreationDate', $menuProduct['created_at']);
                $menuRestaurantProductProductOrder = $dom->createElement('ProductOrder', $menuProduct['product_order']);
                $menuRestaurantProductCategoryOrder = $dom->createElement('CategoryOrder', $menuProduct['category_order']);

                $restaurantMenuProductOut->appendChild($menuRestaurantProductID);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductName);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductDescription);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductValue);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductImage);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductEnable);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductCategoryID);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductCategory);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductCreationDate);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductProductOrder);
                $restaurantMenuProductOut->appendChild($menuRestaurantProductCategoryOrder);

                foreach($restaurantMenuProductsProperty as $menuProductProperty) {

                    $restaurantMenuPropertyOut = $dom->createElement('ProductPropertyOut');

                    $menuRestaurantProductPropertyID = $dom->createElement('ProductPropertyID', $menuProductProperty['product_property_id']);
                    $menuRestaurantProductPropertyProductID = $dom->createElement('ProductD', $menuProductProperty['product_property_product_id']);
                    $menuRestaurantProductPropertyFatherPropertyID = $dom->createElement('FatherProductPropertyID', $menuProductProperty['produt_property_father_property_id']);
                    $menuRestaurantProductPropertyName = $dom->createElement('Name', $menuProductProperty['name']);
                    $menuRestaurantProductPropertyType = $dom->createElement('PropertyType', $menuProductProperty['property_type']);
                    $menuRestaurantProductPropertyGroupingTypeID = $dom->createElement('GroupingTypeID', $menuProductProperty['grouping_type_id']);
                    $menuRestaurantProductPropertyGroupingType = $dom->createElement('GroupingType', $menuProductProperty['grouping_type']);

                    $restaurantMenuPropertyOut->appendChild($menuRestaurantProductPropertyID);
                    $restaurantMenuPropertyOut->appendChild($menuRestaurantProductPropertyProductID);
                    $restaurantMenuPropertyOut->appendChild($menuRestaurantProductPropertyFatherPropertyID);
                    $restaurantMenuPropertyOut->appendChild($menuRestaurantProductPropertyName);
                    $restaurantMenuPropertyOut->appendChild($menuRestaurantProductPropertyType);
                    $restaurantMenuPropertyOut->appendChild($menuRestaurantProductPropertyGroupingTypeID);
                    $restaurantMenuPropertyOut->appendChild($menuRestaurantProductPropertyGroupingType);

                    foreach($restaurantMenuProductsPropertyValues as $menuProductPropertyValue) {

                        $restaurantMenuPropertyValueOut = $dom->createElement('PropertyValueOut');

                        $menuRestaurantProductPropertyValueID = $dom->createElement('ProductPropertyID', $menuProductPropertyValue['product_property_value_id']);
                        $menuRestaurantProductPropertyValuePropertyID = $dom->createElement('ProductPropertyID', $menuProductPropertyValue['product_property_id']);
                        $menuRestaurantProductPropertyValueProductID = $dom->createElement('ProductID', $menuProductPropertyValue['produt_property_value_product_id']);
                        $menuRestaurantProductPropertyValueLabel = $dom->createElement('Label', $menuProductPropertyValue['label']);
                        $menuRestaurantProductPropertyValuePrice = $dom->createElement('ProductID', $menuProductPropertyValue['price']);

                        $restaurantMenuPropertyValueOut->appendChild($menuRestaurantProductPropertyValueID);
                        $restaurantMenuPropertyValueOut->appendChild($menuRestaurantProductPropertyValuePropertyID);
                        $restaurantMenuPropertyValueOut->appendChild($menuRestaurantProductPropertyValueProductID);
                        $restaurantMenuPropertyValueOut->appendChild($menuRestaurantProductPropertyValueLabel);
                        $restaurantMenuPropertyValueOut->appendChild($menuRestaurantProductPropertyValuePrice);

                        $restaurantMenuPropertyOut->appendChild($restaurantMenuPropertyValueOut);

                    }

                    $restaurantMenuProductOut->appendChild($restaurantMenuPropertyOut);

                }

                $restaurantMenuOut->appendChild($restaurantMenuProductOut);
            }

            $nodoRoot->appendChild($restaurantMenuOut);

        }

        foreach($restaurantData as $restaurant) {
            $restaurantOut = $dom->createElement('Restaurant');

                // Restaurant Info 
            $restaurantDataChainID = $dom->createElement('RestaurantChainID', $restaurant['restaurant_chain']);
            $restaurantDataID = $dom->createElement('RestaurantID', $restaurant['id']);
            $restaurantDataName = $dom->createElement('Name', $restaurant['name']);
            $restaurantDataDescription = $dom->createElement('Description', $restaurant['description']);
            $restaurantDataImage = $dom->createElement('Image', $restaurant['image']);
            $restaurantDataTips = $dom->createElement('Tips', $restaurant['tips']);
            $restaurantDataDeliveryArea = $dom->createElement('DeliveryArea', $restaurant['delivery_area']);
            $restaurantDataDeliveryTime = $dom->createElement('DeliveryTime', $restaurant['delivery_time']);
            $restaurantDataOurKitchen = $dom->createElement('OurKitchen', $restaurant['our_kitchen']);
            $restaurantDataAddress = $dom->createElement('Address', $restaurant['address']);
            $restaurantDataPhones = $dom->createElement('Phones', $restaurant['phone_rest']);
            $restaurantDataRate = $dom->createElement('Rate', $restaurant['rate']);
            $restaurantDataMinimunOrder = $dom->createElement('MinimunOrder', $restaurant['minimun_order']);
            $restaurantDataTax = $dom->createElement('Tax', $restaurant['tax']);
            $restaurantDataDeliveryCost = $dom->createElement('DeliveryCost', $restaurant['delivery_cost']);
            $restaurantDataLongitude = $dom->createElement('Longitude', $restaurant['longitude']);
            $restaurantDataLatitude = $dom->createElement('Latitude', $restaurant['latitude']);
            $restaurantDataZip = $dom->createElement('Zip', 33067);
            $restaurantDataDistance = $dom->createElement('Distance', 1);
            $restaurantDataWeb = $dom->createElement('Web', $restaurant['web']);
            $restaurantDataCreationDate = $dom->createElement('CreationDate', $restaurant['created_at']);
            $restaurantDataDelivery = $dom->createElement('Delivery', 'true');
            $restaurantDataPickup = $dom->createElement('Pickup', $restaurant['pickup']);
            $restaurantDataEnable = $dom->createElement('Enable', $restaurant['enable']);
            $restaurantDataYelpID = $dom->createElement('YelpID', $restaurant['YelpID']);
            $restaurantDataDividendsPercent = $dom->createElement('Dividends_percent', $restaurant['dividends_percent']);

            $restaurantOut->appendChild($restaurantDataChainID);
            $restaurantOut->appendChild($restaurantDataID);
            $restaurantOut->appendChild($restaurantDataName);
            $restaurantOut->appendChild($restaurantDataDescription);
            $restaurantOut->appendChild($restaurantDataImage);
            $restaurantOut->appendChild($restaurantDataTips);
            $restaurantOut->appendChild($restaurantDataDeliveryArea);
            $restaurantOut->appendChild($restaurantDataDeliveryTime);
            $restaurantOut->appendChild($restaurantDataOurKitchen);
            $restaurantOut->appendChild($restaurantDataAddress);
            $restaurantOut->appendChild($restaurantDataPhones);
            $restaurantOut->appendChild($restaurantDataRate);
            $restaurantOut->appendChild($restaurantDataMinimunOrder);
            $restaurantOut->appendChild($restaurantDataTax);
            $restaurantOut->appendChild($restaurantDataDeliveryCost);
            $restaurantOut->appendChild($restaurantDataLongitude);
            $restaurantOut->appendChild($restaurantDataLatitude);
            $restaurantOut->appendChild($restaurantDataZip);
            $restaurantOut->appendChild($restaurantDataDistance);
            $restaurantOut->appendChild($restaurantDataWeb);
            $restaurantOut->appendChild($restaurantDataCreationDate);
            $restaurantOut->appendChild($restaurantDataDelivery);
            $restaurantOut->appendChild($restaurantDataPickup);
            $restaurantOut->appendChild($restaurantDataEnable);
            $restaurantOut->appendChild($restaurantDataYelpID);
            $restaurantOut->appendChild($restaurantDataDividendsPercent);

            /* Favorite Disch*/
            $listFavoriteDisch = $dom->createElement('ListFavoriteDisch');

            foreach ($restaurantMenuProducts as $menuProduct) {

                /* --- Product Menu */
                $productMenuOut = $dom->createElement('ProductMenuOut');

                $productID = $dom->createElement('ProductID', 1);
                $productName = $dom->createElement('Name', 1);
                $productDescription = $dom->createElement('Description', 1);
                $productValue = $dom->createElement('Value', 1);
                $productImg = $dom->createElement('ProductImg', 1);
                $productEnable = $dom->createElement('Enable', 1);
                $productCategoryID = $dom->createElement('CategoryID', 1);
                $productCategory = $dom->createElement('Category', 1);
                $productCreationDate = $dom->createElement('CreationDate', 1);
                $productOrder = $dom->createElement('ProductOrder', 1);
                $productCategoryOrder = $dom->createElement('CategoryOrder', 1);
                $productMenuID = $dom->createElement('MenuID', 1);

                $productMenuOut->appendChild($productID);
                $productMenuOut->appendChild($productName);
                $productMenuOut->appendChild($productDescription);
                $productMenuOut->appendChild($productValue);
                $productMenuOut->appendChild($productImg);
                $productMenuOut->appendChild($productEnable);
                $productMenuOut->appendChild($productCategoryID);
                $productMenuOut->appendChild($productCategory);
                $productMenuOut->appendChild($productCreationDate);
                $productMenuOut->appendChild($productOrder);
                $productMenuOut->appendChild($productCategoryOrder);
                $productMenuOut->appendChild($productMenuID);

                foreach ($restaurantMenuProductsProperty as $menuProductProperty) {

                    /* --- Product Property */
                    $productPropertyOut = $dom->createElement('ProductPropertyOut');

                    $productPropertyID = $dom->createElement('ProductPropertyID', 2);
                    $productPropertyProductID = $dom->createElement('ProductID', 2);
                    $productPropertyFatherProductPropertyID = $dom->createElement('FatherProductPropertyID', 2);
                    $productPropertyName = $dom->createElement('Name', 2);
                    $productPropertyType = $dom->createElement('PropertyType', 2);
                    $productPropertyGroupingTypeID = $dom->createElement('GroupingTypeID', 2);
                    $productPropertyGroupingType = $dom->createElement('GroupingType', 2);

                    $productPropertyOut->appendChild($productPropertyID);
                    $productPropertyOut->appendChild($productPropertyProductID);
                    $productPropertyOut->appendChild($productPropertyFatherProductPropertyID);
                    $productPropertyOut->appendChild($productPropertyName);
                    $productPropertyOut->appendChild($productPropertyType);
                    $productPropertyOut->appendChild($productPropertyGroupingTypeID);
                    $productPropertyOut->appendChild($productPropertyGroupingType);

                    foreach ($restaurantMenuProductsPropertyValues as $productPropertyValue) {

                        /* --- Product Property Value */
                        $productPropertyValueOut = $dom->createElement('PropertyValueOut');

                        $productPropertyValueID = $dom->createElement('PropertyValueID', 3);
                        $productPropertyValueProductPropertyID = $dom->createElement('ProductPropertyID', 3);
                        $productPropertyValueProductID = $dom->createElement('ProductID', 3);
                        $productPropertyValueLabel = $dom->createElement('Label', 3);
                        $productPropertyValuePrice = $dom->createElement('Price', 3);

                        $productPropertyValueOut->appendChild($productPropertyValueID);
                        $productPropertyValueOut->appendChild($productPropertyValueProductPropertyID);
                        $productPropertyValueOut->appendChild($productPropertyValueProductID);
                        $productPropertyValueOut->appendChild($productPropertyValueLabel);
                        $productPropertyValueOut->appendChild($productPropertyValuePrice);

                        $productPropertyOut->appendChild($productPropertyValueOut);

                    }

                    $productMenuOut->appendChild($productPropertyOut);

                }

                $listFavoriteDisch->appendChild($productMenuOut);

            }

            /* --- List Coupons */
            $listCouponsOut = $dom->createElement('ListCoupons');

            foreach ($listCouponsProductsMenus as $couponProductMenu) {

                /* --- List Coupons Product Menu */
                $listCouponsProductMenuOut = $dom->createElement('ProductMenuOut');

                $listCouponsProductID = $dom->createElement('ProductID', $couponProductMenu['product_id']);
                $listCouponsProductName = $dom->createElement('Name', $couponProductMenu['name']);
                $listCouponsProductDescription = $dom->createElement('Description', $couponProductMenu['description']);
                $listCouponsProductValue = $dom->createElement('Value', $couponProductMenu['value']);
                $listCouponsProductImg = $dom->createElement('ProductImg', $couponProductMenu['image']);
                $listCouponsProductEnable = $dom->createElement('Enable', $couponProductMenu['enable']);
                $listCouponsProductCategoryID = $dom->createElement('CategoryID', $couponProductMenu['category_id']);
                $listCouponsProductCategory = $dom->createElement('Category', $couponProductMenu['category']);
                $listCouponsProductCreationDate = $dom->createElement('CreationDate', $couponProductMenu['created_at']);
                $listCouponsProductOrder = $dom->createElement('ProductOrder', $couponProductMenu['product_order']);
                $listCouponsProductCategoryOrder = $dom->createElement('CategoryOrder', $couponProductMenu['category_order']);
                $listCouponsProductMenuID = $dom->createElement('MenuID', $couponProductMenu['menu_id']);

                $listCouponsProductMenuOut->appendChild($listCouponsProductID);
                $listCouponsProductMenuOut->appendChild($listCouponsProductName);
                $listCouponsProductMenuOut->appendChild($listCouponsProductDescription);
                $listCouponsProductMenuOut->appendChild($listCouponsProductValue);
                $listCouponsProductMenuOut->appendChild($listCouponsProductImg);
                $listCouponsProductMenuOut->appendChild($listCouponsProductEnable);
                $listCouponsProductMenuOut->appendChild($listCouponsProductCategoryID);
                $listCouponsProductMenuOut->appendChild($listCouponsProductCategory);
                $listCouponsProductMenuOut->appendChild($listCouponsProductCreationDate);
                $listCouponsProductMenuOut->appendChild($listCouponsProductOrder);
                $listCouponsProductMenuOut->appendChild($listCouponsProductCategoryOrder);
                $listCouponsProductMenuOut->appendChild($listCouponsProductMenuID);

                foreach($listCouponsProductsPropertys as $couponProductProperty) {

                    /* --- List Coupons Product Property */
                    $listCouponsProductPropertyOut = $dom->createElement('ProductPropertyOut');

                    $listCouponsProductPropertyID = $dom->createElement('ProductPropertyID', $couponProductProperty['product_property_id']);
                    $listCouponsProductPropertyProductID = $dom->createElement('ProductID', $couponProductProperty['product_property_product_id']);
                    $listCouponsProductPropertyFatherProductPropertyID = $dom->createElement('FatherProductPropertyID', $couponProductProperty['product_property_Father_product_property_id']);
                    $listCouponsProductPropertyName = $dom->createElement('Name', $couponProductProperty['name']);
                    $listCouponsProductPropertyType = $dom->createElement('PropertyType', $couponProductProperty['type']);
                    $listCouponsProductPropertyGroupingTypeID = $dom->createElement('GroupingTypeID', $couponProductProperty['grouping_type_id']);
                    $listCouponsProductPropertyGroupingType = $dom->createElement('GroupingType', $couponProductProperty['grouping_type']);

                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyID);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyProductID);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyFatherProductPropertyID);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyName);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyType);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyGroupingTypeID);
                    $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyGroupingType);

                    foreach ($listCouponsProductsPropertysValues as $couponProductPropertyValue) {

                        /* --- List Coupons Product Property Value */
                        $listCouponsProductPropertyValueOut = $dom->createElement('PropertyValueOut');

                        $listCouponsProductPropertyValueID = $dom->createElement('PropertyValueID', $couponProductPropertyValue['property_value_id']);
                        $listCouponsProductPropertyValueProductPropertyID = $dom->createElement('ProductPropertyID', $couponProductPropertyValue['property_value_product_property_id']);
                        $listCouponsProductPropertyValueProductID = $dom->createElement('ProductID', $couponProductPropertyValue['property_value_product_id']);
                        $listCouponsProductPropertyValueLabel = $dom->createElement('Label', $couponProductPropertyValue['label']);
                        $listCouponsProductPropertyValuePrice = $dom->createElement('Price', $couponProductPropertyValue['price']);

                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValueID);
                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValueProductPropertyID);
                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValueProductID);
                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValueLabel);
                        $listCouponsProductPropertyValueOut->appendChild($listCouponsProductPropertyValuePrice);

                        $listCouponsProductPropertyOut->appendChild($listCouponsProductPropertyValueOut);

                    }

                    $listCouponsProductMenuOut->appendChild($listCouponsProductPropertyOut);

                }

                $listCouponsOut->appendChild($listCouponsProductMenuOut);

            }

            $restaurantOut->appendChild($listCouponsOut);

                // --- Filters
            $filters = $dom->createElement('Filters');

                //REVISAR ESTE PUNTO, YA QUE AQUI VALIDA SI LOS MUESTRA O NO
            $filtersDelivery = $dom->createElement('Delivery', true);
            $filtersPickup = $dom->createElement('Pickup', true);
            $filtersFreeDelivery = $dom->createElement('FreeDelivery', true);
            $filtersOpenNow = $dom->createElement('OpenNow', 'true');
            $filtersHaveCoupons = $dom->createElement('HaveCoupons', true);

            $filters->appendChild($filtersDelivery);
            $filters->appendChild($filtersPickup);
            $filters->appendChild($filtersFreeDelivery);
            $filters->appendChild($filtersOpenNow);
            $filters->appendChild($filtersHaveCoupons);

            $restaurantOut->appendChild($filters);

            foreach ($paymentTypes as $paymentType) {

                /* --- Payment Type */
                $paymentTypeOut = $dom->createElement('PaymentTypeOut');

                $paymentTypeID = $dom->createElement('PaymentTypeID', $paymentType['payment_type_id']);
                $paymentType = $dom->createElement('PaymentType', $paymentType['payment_type']);
                $paymentIcon = $dom->createElement('Icon', 1);
                $paymentEnable = $dom->createElement('Enable', 1);

                $paymentTypeOut->appendChild($paymentTypeID);
                $paymentTypeOut->appendChild($paymentType);
                $paymentTypeOut->appendChild($paymentIcon);
                $paymentTypeOut->appendChild($paymentEnable);

                $restaurantOut->appendChild($paymentTypeOut);

            }

                // --- Schelude
            $scheduleOut = $dom->createElement('ScheduleOut');

            $scheduleID = $dom->createElement('ScheduleID', 7);
            $scheduleMonday = $dom->createElement('Monday', true);
            $scheduleTuesday = $dom->createElement('Tuesday', true);
            $scheduleWednesday = $dom->createElement('Wednesday', 7);
            $scheduleThursday = $dom->createElement('Thursday', 7);
            $scheduleFriday = $dom->createElement('Friday', 7);
            $scheduleSaturday = $dom->createElement('Saturday', 7);
            $scheduleSunday = $dom->createElement('Sunday', 7);

            $scheduleOut->appendChild($scheduleID);
            $scheduleOut->appendChild($scheduleMonday);
            $scheduleOut->appendChild($scheduleTuesday);
            $scheduleOut->appendChild($scheduleWednesday);
            $scheduleOut->appendChild($scheduleThursday);
            $scheduleOut->appendChild($scheduleFriday);
            $scheduleOut->appendChild($scheduleSaturday);
            $scheduleOut->appendChild($scheduleSunday);

            $restaurantOut->appendChild($scheduleOut);

            $productPropertyOut->appendChild($productPropertyValueOut);
            $productMenuOut->appendChild($productPropertyOut);
            $listFavoriteDisch->appendChild($productMenuOut);
            $restaurantOut->appendChild($listFavoriteDisch);

            $nodoRoot->appendChild($restaurantOut);
        }

        Header('Content-type: text/xml');
        echo $dom->saveXML();

        break;
        case 'ValidateUser':
            
            $jsonData = json_decode($obj->Body->ValidateUser->JsonValidaUser, true);
            $ch = curl_init();
            $jsonData["op"] = $op;
            //$jsonData["mail"]=$_REQUEST["mail"];
            //$jsonData["password"]=$_REQUEST["password"];
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($jsonData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $data = $result;

            $data = json_decode($data, true);

            $dom = new DOMDocument();
            $ValidateU = $dom->createElement('ValidatorUser');
            $user = $dom->createElement('User');
            $clientId = $dom->createElement('ClientID', $data["User"]["ClientID"]);
            $UserName = $dom->createElement('UserName', $data["User"]["UserName"]);
            $Password = $dom->createElement('Password', $data["User"]["Password"]);
            $FullName = $dom->createElement('FullName', $data["User"]["FullName"]);
            $eMail = $dom->createElement('eMail', $data["User"]["eMail"]);
            $FacebookID = $dom->createElement('FacebookID', $data["User"]["FacebookID"]);
            $TwitterID = $dom->createElement('TwitterID', $data["User"]["TwitterID"]);
            $Address = $dom->createElement('Address', $data["User"]["Address"]);
            $Cellphone = $dom->createElement('Cellphone', $data["User"]["Cellphone"]);
            $CreationDate = $dom->createElement('CreationDate', $data["User"]["CreationDate"]);
            $user->appendChild($clientId);
            $user->appendChild($UserName);
            $user->appendChild($Password);
            $user->appendChild($FullName);
            $user->appendChild($eMail);
            $user->appendChild($FacebookID);
            $user->appendChild($TwitterID);
            $user->appendChild($Address);
            $user->appendChild($Cellphone);
            $user->appendChild($CreationDate);
            $ValidateU->appendChild($user);
            
            $_SESSION["_token"]=$data["Session"]["SessionKey"];
            $_SESSION["idUser"] = $data["Session"]["ClientID"];

            $session = $dom->createElement('Session');
            $sessionkey = $dom->createElement('SessionKey', $data["Session"]["SessionKey"]);
            //$sessionkey = $dom->createElement('SessionKey', $data["User"]["ClientID"]);
            $TimeExpiration = $dom->createElement('TimeExpiration', $data["Session"]["TimeExpiration"]."--".$_SESSION["_token"]);
            $clientIdS = $dom->createElement('ClientID', $data["User"]["ClientID"]);
            $session->appendChild($clientIdS);
            $session->appendChild($sessionkey);
            $session->appendChild($TimeExpiration);
            $ValidateU->appendChild($session);

            $dom->appendChild($ValidateU);
            Header('Content-type: text/xml');
            echo $dom->saveXML();

            break;

        case 'GetOrder':            
            $jsonData = json_decode($obj->Body->GetOrder->JsonGetOrder);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('SessionKey' => $jsonData->SessionKey, 'lg' => $jsonData->lg, 'orderid' => $jsonData->orderid, 'op' => $op)));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $data = $result;

            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $Cont_order = $dom->createElement('GetOrder');
            $order = $dom->createElement('Order');
            $Restaurant = $dom->createElement('Restaurant');
            $order = getXMLChildrens($dom, $data["Order"], $order, 1);
            $Restaurant = getXMLChildrens($dom, $data["Restaurant"], $Restaurant,1);
            $Cont_order->appendChild($order);
            $Cont_order->appendChild($Restaurant);
            $dom->appendChild($Cont_order);
            Header('Content-type: text/xml');
            echo $dom->saveXML();

            break;
        case 'AddClientAddress':
            $jsonData = json_decode($obj->Body->AddClientAddress->JsonAddAddressClient);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                array(
                    'SessionKey' => $jsonData->SessionKey, 
                    'Address' => $jsonData->Address, 
                    'Suit' => $jsonData->Suit, 
                    'City' => $jsonData->City,
                    'State' => $jsonData->State, 
                    'ZIPCode' => $jsonData->ZIPCode, 
                    'CrossStreet' => $jsonData->CrossStreet, 
                    'Phone' => $jsonData->Phone,
                    'AddressName' => $jsonData->AddressName,
                    'op' => $op
                )
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $Cont_AddressR = $dom->createElement('ClientAddressC');
            $success = $dom->createElement('Success', $data["Success"]);
            $ClientAddress = $dom->createElement('contClientAddress');
            if($data["Success"] == "true"){
                $ClientAddress = getXMLChildrens($dom, $data["ClientAddress"], $ClientAddress,2, 'ClientAddress');                
            }else{
                $ClientAddress = getXMLChildrens($dom, $data["errorResponse"], $ClientAddress);              

            }
            $Cont_AddressR->appendChild($success);
            $Cont_AddressR->appendChild($ClientAddress);
            $dom->appendChild($Cont_AddressR);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'CreateShoppingCart':
            $jsonData = json_decode($obj->Body->CreateShoppingCart->JsonCreateShoppingCart, true);
            $ch = curl_init();
            $jsonData["op"] = $op;
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $shoppingCart = $dom->createElement('CreateShoppingCart');
            $TotalValuePreorder = $dom->createElement('TotalValuePreorder', $data["TotalValuePreorder"]);
            $BaseValuePreorder = $dom->createElement('BaseValuePreorder', $data["BaseValuePreorder"]);
            $TaxValuePreorder = $dom->createElement('TaxValuePreorder', $data["TaxValuePreorder"]);
            $RestaurantDeliveryFee = $dom->createElement('RestaurantDeliveryFee', $data["RestaurantDeliveryFee"]);
            $PreOrderID = $dom->createElement('PreOrderID', $data["PreOrderID"]);
            $ProductOrder = $dom->createElement('contProductOrder');
            $ProductOrder = getXMLChildrens($dom, $data["ProductOrder"], $ProductOrder, 2, "ProductOrder");
            $shoppingCart->appendChild($TotalValuePreorder);
            $shoppingCart->appendChild($BaseValuePreorder);
            $shoppingCart->appendChild($TaxValuePreorder);
            $shoppingCart->appendChild($RestaurantDeliveryFee);
            $shoppingCart->appendChild($PreOrderID);
            $shoppingCart->appendChild($ProductOrder);
            $dom->appendChild($shoppingCart);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'GetOrderRecalcPay':
            $jsonData = json_decode($obj->Body->GetOrderRecalcPay->JsonGetOrderRecalcPay);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                array(
                    "SessionKey" => $jsonData->SessionKey,
                    "lg" => $jsonData->lg,
                    "address" => $jsonData->address,
                    "ordertype" => $jsonData->ordertype,
                    "paymenttype" => $jsonData->paymenttype,
                    "orderid" => $jsonData->orderid,
                    "op" => $op,
                )
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $response = NULL;
            $OrderRecalcPay = $dom->createElement('GetOrderRecalcPay');

            if(isset($data["Success"])){
                $response = $dom->createElement('Success', $data["Success"]);
            }else{
                $response = $dom->createElement('ErrMessage', $data["ErrMessage"]);                
            }
            $order = $dom->createElement("Order");
            $order = getXMLChildrens($dom, $data["Order"], $order);
            $OrderRecalcPay->appendChild($order);

            $OrderRecalcPay->appendChild($response);
            $dom->appendChild($OrderRecalcPay);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'SetTipsAndDiscount':
            $jsonData = json_decode($obj->Body->SetTipsAndDiscount->JsonSetTipsAndDiscount, true);
            $jsonData["op"] = $op;
            //$jsonData["orderid"] = $_REQUEST["orderid"];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
            //    echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('TipsAndDiscount');
            $orderElement = $dom->createElement('Order');
            $orderElement = getXMLChildrens($dom, $data["Order"], $orderElement,1);
            $mainElement->appendChild($orderElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break; 
        case 'GetClientPayments':
            $jsonData = json_decode($obj->Body->GetClientPayments->JsonGetClientPayments, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('ClientPayments');
            $txoutElement = $dom->createElement('TcOutCont');
            $txoutElement = getXMLChildrens($dom, $data["TcOut"], $txoutElement, 2, "TcOut");
            $mainElement->appendChild($txoutElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break; 
        case 'CheckOrderStatus':
            $jsonData = json_decode($obj->Body->CheckOrderStatus->JsonCheckOrderStatus, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('CheckOrderStatus');
            $orderElement = $dom->createElement('Order');
            $RestaurantElement = $dom->createElement('Restaurant');
            $orderStatusElement = $dom->createElement('OrderStatus', !empty($data["OrderStatus"])?$data["OrderStatus"]:"error" );
            $orderElement = getXMLChildrens($dom, $data["Order"], $orderElement);
            $RestaurantElement = getXMLChildrens($dom, $data["Restaurant"], $RestaurantElement);
            $mainElement->appendChild($orderElement);
            $mainElement->appendChild($RestaurantElement);
            $mainElement->appendChild($orderStatusElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break; 
        case 'ChangePassword':
            $jsonData = json_decode($obj->Body->ChangePassword->JsonChangePassword, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('ChangePassword');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'ClientLogOut':
            $jsonData = json_decode($obj->Body->ClientLogOut->JsonLogOut, true);
            $jsonData["op"] = $op;
            //$jsonData["SessionKey"] = $_REQUEST["SessionKey"];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('ClientLogOut');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();

            break;
        case 'CreateClient':
            $jsonData = json_decode($obj->Body->CreateClient->JsonCreateClient, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('CreateClient');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();

            break;
        case 'DeleteCard':
            $jsonData = json_decode($obj->Body->DeleteCard->JsonDeleteCard, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('DeleteCard');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();

            break;
        case 'DuplicateOrder2':
            $jsonData = json_decode($obj->Body->DuplicateOrder2->JsonDuplicateOrder2, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('DuplicateOrder2');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();

            break;
        case 'NewContact':
            $jsonData = json_decode($obj->Body->NewContact->JsonNewContact, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('NewContact');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();

            break;
        case 'NewProductComment':
            $jsonData = json_decode($obj->Body->NewProductComment->JsonNewProductComment, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('NewProductComment');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'NewPwdChange':
            $jsonData = json_decode($obj->Body->NewPwdChange->JsonNewPwdChange, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('NewPwdChange');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'PayOrder':
            $jsonData = json_decode($obj->Body->PayOrder->JsonPayOrder, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('PayOrder');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'RateRestaurantOrder':
            $jsonData = json_decode($obj->Body->RateRestaurantOrder->JsonRateRestaurantOrder, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('RateRestaurantOrder');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'RemoveClientAddress':
            $jsonData = json_decode($obj->Body->RemoveClientAddress->JsonRemoveClientAddress, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('RemoveClientAddress');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'SetDefaultClientAddress':
            $jsonData = json_decode($obj->Body->SetDefaultClientAddress->JsonSetDefaultClientAddress, true);
            $jsonData["op"] = $op;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('SetDefaultClientAddress');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'SetNewCard':
            $jsonData = json_decode($obj->Body->SetNewCard->JsonSetNewCard, true);
            if(!isset($obj->Body->SetNewCard)){
                $jsonData = json_decode($obj->Body->UpdateCard->JsonUpdateCard, true);
                $jsonData["update"] = "true";
            }
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('SetNewCard');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();

            break; 
        case 'UpdateClientAddress':
            $jsonData = json_decode($obj->Body->UpdateClientAddress->JsonUpdateAddressClient, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('UpdateClientAddress');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break; 
        case 'UpdateClientProfile':
            $jsonData = json_decode($obj->Body->UpdateClientProfile->JsonUpdateClientProfile, true);
            $jsonData["op"] = $op;
            //$jsonData["SessionKey"] = $_REQUEST["SessionKey"];
            //$jsonData["fullname"] = $_REQUEST["fullname"];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('UpdateClientProfile');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();

            break; 
        case 'GetAllContactsType':
            $jsonData = json_decode($obj->Body->GetAllContactTypes->JsonGetAllContactTypes, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('GetAllContactTypes');
            getXMLChildrens($dom, $data, $mainElement);
            $mainElement =$dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'GetBanners':
            $jsonData = json_decode($obj->Body->GetBanners->JsonGetBanners, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('GetBanners');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'GetDefaultUserAddress':
            $jsonData = json_decode($obj->Body->GetDefaultUserAddress->JsonGetDefaultUserAddress, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('GetDefaultUserAddress');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'GetFavoritesOrders':
            $jsonData = json_decode($obj->Body->GetFavoritesOrders->JsonGetFavoritesOrders, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('GetFavoritesOrders');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'GetFavoritesRestaurants':
            $jsonData = json_decode($obj->Body->GetFavoritesRestaurants->JsonGetFavoritesRestaurants, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('GetFavoritesRestaurants');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'GetOrderForPay':
            $jsonData = json_decode($obj->Body->GetOrderForPay->JsonGetOrderForPay, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('GetOrderForPay');
            $FrmParamsElement = $dom->createElement('FrmParams');
            $FrmParamsElement = getXMLChildrens($dom, $data["FrmParams"], $FrmParamsElement);
            $FrmInputsElement = $dom->createElement('FrmInputs');
            $FrmInputsElement = getXMLChildrens($dom, $data["FrmInputs"], $FrmInputsElement);
            $mainElement->appendChild($FrmParamsElement);
            $mainElement->appendChild($FrmInputsElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break; 
        case 'GetProductComments':
            $jsonData = json_decode($obj->Body->GetProductComments->JsonGetProductComments, true);
            $jsonData["op"] = $op;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('GetProductComments');
            $mainElement = getXMLChildrens($dom, $data, $mainElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;
        case 'GetProfileBySessionKey':
            $jsonData = json_decode($obj->Body->GetProfileBySessionKey->JsonUserSessionBySessionKey, true);
            $jsonData["op"] = $op;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('GetProfileBySessionKey');
            $UserElement = $dom->createElement('User');
            $SessionElement = $dom->createElement('Session');
            $UserElement = getXMLChildrens($dom, $data["User"], $UserElement);
            $SessionElement = getXMLChildrens($dom, $data["Session"], $SessionElement);
            $mainElement->appendChild($UserElement);
            $mainElement->appendChild($SessionElement);
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;  
        case 'GetUserAddress':
            
            $jsonData = json_decode($obj->Body->GetUserAddress->JsonGetUserAddress, true);
            $jsonData["op"] = $op;
            //$jsonData["SessionKey"] = 935;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlServices);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                $jsonData
            )); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $data = $result;
                //echo $data;
            $data = json_decode($data, true);
                //var_dump($data);
            $dom = new DOMDocument();
            $mainElement =  $dom->createElement('GetUserAddress');
            if(!empty($data["ClientAddressOut"]) && $data["errorResponse"] != "query error DB"){
                try {
                    $ClientAddressOutElement = $dom->createElement('ContClientAddressOut');
                    $ClientAddressOutElement = getXMLChildrens($dom, $data["ClientAddressOut"], $ClientAddressOutElement, 2, "ClientAddressOut");
                    $mainElement->appendChild($ClientAddressOutElement);
                    
                } catch (Exception $e) {
                        echo 'Excepción capturada: 11',  $e->getMessage(), "\n";
                        
                }
            }else{                
                $ClientAddressOutElement = $dom->createElement('errorResponse');
                    $ClientAddressOutElement = getXMLChildrens($dom, $data["errorResponse"], $ClientAddressOutElement );
                    $mainElement->appendChild($ClientAddressOutElement);
                    $error = $dom->createElement('err', $data["codeerr"]);
                    $mainElement->appendChild($error);
            }
            $dom->appendChild($mainElement);
            Header('Content-type: text/xml');
            echo $dom->saveXML();
            break;

     
    }

    function getInsertChildren($dom, $contenedor, $padre){
        $elements = [];
        try {
            foreach ($contenedor as $key => $value) {
                if(is_array($value)){
                    /*$son = $dom->createElement($key);
                    $son=getInsertChildren($dom, $value[0], $son);
                    $padre->appendChild($son);
    */              
                    //echo $key."\n";
                    $son = $dom->createElement(is_numeric ($key) ? $padre->tagName.$key : $key);
                    $padre->appendChild(getInsertChildren($dom, $value,$son));
                    //var_dump($value);
                }else{
                    $padre->appendChild($dom->createElement(is_numeric ($key) ? $padre->tagName.$key : $key, $value));
                }
            }
        } catch (Exception $e) {
            echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        }
        return $padre;
    }


    function getInsertChildren2($dom, $contenedor, $padre){
        $elements = [];
        try {
            foreach ($contenedor as $key => $value) {
                if(is_array($value)){
                    foreach ($value as $key2 => $value2) {
                        $child = $dom->createElement($key);
                        foreach ($value2 as $key3 => $value3) {
                            $child->appendChild($dom->createElement($key3, $value3));
                        }
                        $padre->appendChild($child);
                    }
                }
            }
        } catch (Exception $e) {
            echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        }
        return $padre;
    }
function getXMLChildrens($dom, $contenedor, $name, $type=1, $dis = "ls"){

    if($type == 1){
        foreach ($contenedor as $key => $value) {
            if(!is_array($value)){
                $name->appendChild($dom->createElement($key, $value));
            }else{
                //echo "falso ". $key;
                $sub = null;
                if(!is_assoc($value)){
                    $sub = $dom->createElement($key."0");
                    $sub = getXMLChildrens($dom, $value, $sub, 2, $key);
                }else{
                    $sub = $dom->createElement($key);
                    $sub = getXMLChildrens($dom, $value, $sub, 1);
                }
                $name->appendChild($sub);
            }
        }
    }
    if($type == 2){
        foreach ($contenedor as $clave => $valor) {
            $arr = $dom->createElement($dis);
            if (is_assoc($valor)) {
                $arr = getXMLChildrens($dom, $valor, $arr, 1);     
            }
            $name->appendChild($arr); 
        }
    }
    return $name;
}
function is_assoc( $array ) {

     foreach(array_keys($array) as $key) {
         if (!is_int($key)) return true;
     }

     return false;

}