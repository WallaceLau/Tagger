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
Route::get('/{keywords}', 'Api\FlickrApiController@searchTags');
Route::get('/{photoId}', 'Api\FlickrApiController@getPhotoInfo')->where('photoId','[0-9]+');

Route::get('/show/{id}', 'Db\DbController@show')->name('showRecords')->where('id','[0-9]+');
Route::get('/insert/{name}', 'Db\DbController@insert')->name('insertRecords')->where('name','[A-Za-z]+');
Route::get('/update/{id}/{name}','Db\DbController@update')->name('updateRecords')->where('name','[A-Za-z]+')->where('id','[0-9]+');
Route::get('/delete/{id}','Db\DbController@delete')->name('deleteRecords')->where('id','[0-9]+');

Route::get('/listAll/{tableName}','Db\DbController@listAll')->name('listAll')->where('name','[A-Za-z]+');

Route::get('/email/testing','Email\EmailController@sendNotification')->name('sendTestingEmail');

Route::get("/email/show", function(){
   return View::make("emails.notification");
});

Route::get('/sendTestingRequest/testing','Api\FlickrApiController@testing');


//Route::get('/insertWfError/yes','Db\DbController@insertWfError')->name('insertWfError');

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
