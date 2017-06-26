<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HttpRequest\HttpRequestController;
use Illuminate\Http\Request;
use Cache;
use Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

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

    public function handleOtherRequest($url, $methodType, $json){
        return $this->requestHandler->doRequest($url, $methodType, ['json' => $json]);
    }

    public function checkRequestStatus($arraizedResult){
        if($arraizedResult['stat'] == 'ok'){
            return $arraizedResult;
        } else{
            Log::info('FlickrApiController------ Flickr API call failed with status code:: '.$arraizedResult['code'].' and message :: '.$arraizedResult['message']);
        }
    }

    public function testing(){

        //create testing json 
        $arrayOfData = array();
        $arrayOfJson = array();
        for($i=0;$i<16000;$i++) {
            $data = array( "recordTime" => "2017-01-01 11:45:34","humidity" => "52","light" => "1","temperature" => "23");
            array_push($arrayOfData, $data);
        }
        array_push($arrayOfJson, array("scanedDate" => "2017-2-22"));
        array_push($arrayOfJson, array("sersorId" => "xxxxx"));
        array_push($arrayOfJson, array("readData" => $arrayOfData));
        $testingJson = json_encode($arrayOfJson);

        //gzip the json to reduce the request size
        $gzipJson = gzencode($testingJson);

        //get the md5 of gzipedJson
        $md5 = md5($gzipJson);
        echo "em5 of sent json: ".$md5;

        //send POST request to server
        try {
            $httpClient = new Client();
            //$httpClient->setHttpClient(new Client(['verify' => false]));
            //$httpClient->getHttpClient()->setDefaultOption(['verify'=> '/etc/nginx/ssl/cherish.app.crt']);
            //$res = $httpClient->request('POST', 'http://192.168.10.11/getGzipRequest', ['headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json','Content-MD5' => $md5],'body' => $gzipJson]);
            $res = $httpClient->request('POST', 'https://192.168.10.11/getGzipRequest', ['curl' => [ CURLOPT_SSLCERT => '/etc/nginx/ssl/nginx.pem'], 'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json','Content-MD5' => $md5],'body' => $gzipJson]);
            //$res = $httpClient->request('POST', 'http://52.221.14.117/getGzipRequest', ['headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json','Content-MD5' => $md5],'body' => $gzipJson]);
            $md5 = $res->getHeader('X-Header-md5'); 
            $counter = $res->getHeader('X-Header-json-counter'); 
            echo ": em5 of received json: ".$md5[0]." ";  
            echo "Total record: ".$counter[0];
            echo gethostname();
        } catch (GuzzleHttp\Exception\ClientException $e) {
            //$responseBody = $e->getResponse()->getBody(true);
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            echo "Eception: ".$responseBodyAsString;
        }
    }

    /**
     * call Flickr API to search photos by givn keywords
     *
     * @param string  $keywords
     * @return Response
     */

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
