<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HttpRequest\HttpRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FlickrApiController extends Controller
{
    private $requestHandler;

    public function __construct(){
        $this->requestHandler = new HttpRequestController();
    }

    public function constructImageUrl($farmId, $serverId, $id, $secert){
        return 'https://farm'.$farmId.'.staticflickr.com/'.$serverId.'/'.$id.'_'.$secert.'.jpg';
    }

    public function handleFlickrRequest($methodType, $url, $cacheName){
        $value = Cache::remember( $cacheName, 15, function() use ($cacheName, $url, $methodType) {
            return $this->requestHandler->doRequest($url, $methodType, []);
        });
        //Log::info('FlickrApiController::handleFlickrRequest() retrivied value: '.json_encode($value));
        return $this->checkRequestStatus($value);
    }

    public function checkRequestStatus($arraizedResult){
        if($arraizedResult['stat'] == 'ok'){
            return $arraizedResult;
        } else{
            Log::info('FlickrApiController------ Flickr API call failed with status code:: '.$arraizedResult['code'].' and message :: '.$arraizedResult['message']);
        }
    }

    public function searchTags($keywords){
        $url = env('FLICKR_BASE_URL').'?method='.env('FLICKR_SEARCH_METHOD').'&api_key='.env('FLICKR_API_KEY').'&tags='.$keywords.'&format='.env('FLICKR_RESPONSE_FORMAT').'&'.env('FLICKR_ENABLE_JSONCALLBACK').
        //'&license='.env('FLICKR_LICENSE').
        '&tag_mode='.env('FLICKR_TAG_MODE').'&per_page='.env('FLICKR_PER_PAGE').'&page='.env('FLICKR_PAGE');        
        Log::info('FlickrApiController::searchTags() request URL: '.$url);

        $arraizedResult = $this->handleFlickrRequest('GET', $url, $keywords);   
        $resultSets = array();
        foreach($arraizedResult['photos']['photo'] as $element){
            (string) $url = $this->constructImageUrl($element['farm'], $element['server'], $element['id'], $element['secret']);
            //Log::info('FlickrApiController::searchTags()------ Constructed URL: '.$url);
            $resultSet = array($url, $element['id']);
            \Debugbar::info('resultSet: '.implode('\n',$resultSet));
            array_push($resultSets, $resultSet);
        }
        //\Debugbar::info('resultSets: '.implode('\n',$resultSets));
        return response()->json($resultSets);
    }

    public function getPhotoInfo($photoId){
        $url = env('FLICKR_BASE_URL').'?method='.env('FLICKR_GETINFO_METHOD').'&api_key='.env('FLICKR_API_KEY').'&photo_id='.$photoId.'&format='.env('FLICKR_RESPONSE_FORMAT').'&'.env('FLICKR_ENABLE_JSONCALLBACK');
        Log::info('FlickrApiController::getPhotoInfo() request URL: '.$url);

        $arraizedResult = $this->handleFlickrRequest('GET', $url, $photoId);  
        Log::info('FlickrApiController::getPhotoInfo()------  photo owner: https://www.flickr.com/photos/'.$arraizedResult['photo']['owner']['nsid']); 
        $tagSets = array();
        foreach($arraizedResult['photo']['tags']['tag'] as $element){
            (string) $tag = $element['raw'];
            //Log::info('FlickrApiController::getPhotoInfo()------  tag: '.$tag);
            \Debugbar::info('Tag: '.$tag);
            array_push($tagSets, $tag);
        }
        //\Debugbar::info('resultSets: '.implode('\n',$resultSets));
        return response()->json($tagSets);
    }

}
