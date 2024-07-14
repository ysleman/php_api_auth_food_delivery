<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');
$router->post('/login__222__admin', 'AuthController@loginAdmin');
$router->post('/fgpassword','AuthController@forgetpassword');
$router->post('/login/res','AuthController_res@login');
$router->post('/login/del','AuthController_Del@login');
$router->get('/api/resturants/index', 'ResturantsController@index');
$router->get('/menu_items','MenuItemsController@index');

$router->post('/menu_items_id','MenuItemsController@index_id');
$router->post('/resturants_address','ResturantsController@sort_location');
$router->post('/resturants_address_food','ResturantsController@sort_location_food');

$router->group(['middleware' => 'auth','prefix' => 'api'], function ($router)
{
    $router->post('/logout', 'AuthController@logout');
    $router->post('/updatepa','AuthController@upadtepassword');
    $router->get('/userdetails','UserController@userdetails');
    $router->get('/current_orders','UserController@orders_list');
    $router->post('/temp_order_add','UserController@temp_order_add');
    $router->get('/temp_orders','UserController@temp_order');
    $router->post('/add_order','UserController@addorder');
    $router->post('/profile/update', 'UserController@updateProfile');
    $router->post('/favorites/add', 'UserController@favoriteAdd');
    $router->get('/favorites', 'UserController@favorite');
    $router->post('/favorites/remove', 'UserController@favoriteremove');
    $router->post('/payment/process', 'PaymentController@process');
    $router->post('/payment/methods', 'PaymentController@saveMethod');
    $router->get('/payment/methods', 'PaymentController@getMethods');

});
$router->group(['middleware' => 'auth:delivery_drivers','prefix' => 'api/delivery_drivers'], function ($router)
{
    $router->get('/orders', 'DeliveryDriverController@orders_list');
    $router->post('/tracking', 'DeliveryDriverController@tracking');
    $router->post('/update_tracking', 'DeliveryDriverController@update_track');
    $router->post('/remove_order', 'DeliveryDriverController@removeorder');
    $router->get('/index', 'DeliveryDriverController@index');
});
$router->group(['middleware' => 'auth:resturants','prefix' => 'api/resturants'], function ($router)
{
    $router->get('/orders','ResturantsController@order_list');
    $router->get('/remove_order', 'ResturantsController@removeorder');

    $router->post('/menu_items_add','ResturantsController@menu_items_add');
    $router->post('/menu_items_edit','ResturantsController@menu_items_edit');
    $router->post('/menu_items_remove','ResturantsController@menu_items_remove');
    $router->post('/menu_items_ingredients_add','ResturantsController@menu_items_ingredients_add');
    $router->post('/menu_items_ingredients_edit','ResturantsController@menu_items_ingredients_edit');
    $router->post('/menu_items_ingredients_remove','ResturantsController@menu_items_ingredients_remove');
    
});
$router->group(['middleware' => 'auth:admin','prefix' => 'api/admin'], function ($router)
{
    //SHOW 
    $router->get('/orders','AdminController@orders_list');
    $router->get('/users','AdminController@users_list');
    $router->get('/resturants','AdminController@resturants_list');
    $router->get('/delivery_drivers','AdminController@delivery_list');
    $router->get('/resturants/money/all/total','AdminController@resturants_money_all_total'); 
    $router->get('/resturants/money/all/tax','AdminController@resturants_money_all_tax'); 
    $router->post('/resturants/money/total','AdminController@resturants_money_total'); 
    $router->post('/resturants/money/tax','AdminController@resturants_money_tax'); 


    //ADD
    $router->post('/orders_add','AdminController@orders_add');
    $router->post('/users_add','AdminController@users_add');
    $router->post('/resturants_add','AdminController@resturants_add');
    $router->post('/delivery_drivers_add','AdminController@delivery_add');
    
    //Remove
    $router->post('/orders_remove','AdminController@orders_delete');
    $router->post('/users_remove','AdminController@users_delete');
    $router->post('/resturants_remove','AdminController@resturants_delete');
    $router->post('/delivery_drivers_remove','AdminController@delivery_delete');

    //EDIT
    $router->post('/orders_edit','AdminController@orders_edit');
    $router->post('/users_edit','AdminController@users_edit');
    $router->post('/resturants_edit','AdminController@resturants_edit');
    $router->post('/delivery_drivers_edit','AdminController@delivery_edit');

    
});