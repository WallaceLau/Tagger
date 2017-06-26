<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('/', 'Web\WebController@showHomePage')->name('home');
Route::get('/search/{keywords}', 'Api\FlickrApiController@searchTags');
Route::get('/getPhotoInfo/{photoId}', 'Api\FlickrApiController@getPhotoInfo');
Route::post('/getGzipRequest','Response\ResponseController@getGzipRequest');
Route::get('/androidApi/tag/getTagData/{scanId}','AndroidApi\ScanDateContoller@getTagData');
Route::post('/androidApi/information/enquiries/sendScanDataEnquiry','AndroidApi\Information\EnquiriesContoller@sendScanDataEnquiry');
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => 'web'], function () {
    //
});