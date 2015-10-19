<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Route::get('news', 'NewsController@getNews');
Route::get('news/{lat?}/{long?}', ['middleware' => 'oauth', 'uses' => 'NewsController@getNews']);

Route::get('location/{lat}/{long}', 'LocationController@getLocationInfo');


Route::get('protected', ['middleware' => 'oauth', function() {
    return 'you have access';
}]);


Route::post('/fb-callback',  function() {
    $user = new \App\User;

    $postdata = file_get_contents("php://input");
	$request = json_decode($postdata);

	$user->name = $request->name;
	$user->email = $request->email;
	$user->save();

	$Session = new \App\Session;
	$Session->client_id = 'facebook';
	$Session->owner_type = 'user';
	$Session->owner_id = $user->id;
	$Session->save();

	$AccessToken = new \App\AccessToken;
	$AccessToken->id = $request->access_token;
	$AccessToken->session_id = $Session->id;
	$AccessToken->save();
});

Route::post('/category',  function() {
    // access:token aus Authorization header ziehen
    // validieren
    // user anhand access_token finden
    // gucken ob category_users schon eintrag enthält für die category für den user
    // bei status: true  -> neuen eintrag machen
    // bei status: false -> eintrag löschen
});

Route::post('oauth/access_token', function() {
    return Response::json(Authorizer::issueAccessToken());
});

Route::get('enc', function() {
    return Response::json(Crypt::encrypt('test'));
});

