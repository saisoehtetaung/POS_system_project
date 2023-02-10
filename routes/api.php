<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RouteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//GET
Route::get('product/list',[RouteController::class,'productList']);
Route::get('category/list',[RouteController::class,'categoryList']);
Route::get('contact/list',[RouteController::class,'contactList']);

//POST
Route::post('create/category',[RouteController::class,'categoryCreate']);
Route::post('create/contact',[RouteController::class,'createContact']);

// Route::post('category/delete',[RouteController::class,'deleteCategory']);

Route::get('category/delete/{id}',[RouteController::class,'deleteCategory']);

Route::post('category/details',[RouteController::class,'categoryDetails']);

Route::post('category/update',[RouteController::class,'categoryUpdate']);



/*

Product LIst
localhost:8000/api/product/list (GET)

Category list
localhost:8000/api/category/list (GET)

Contact List
localhost:8000/api/contact/list (GET)


create category
localhost:8000/api/create/category(POST)
{
    'name' : '',
}

create Contact
localhost:8000/api/create/contact (POST)
{
    'name': '',
    'email' : '',
    'message' : '',
}

Delete Category
localhost:8000/api/category/delete/{id} (GET)

Category Details
localhost:8000/api/category/details (POST)
{
    'category_id' : '',
}

Category Update
localhost:8000/api/category/update (POST)
{
    'category_id' : '',
    'category_name' : '',
}

*/

