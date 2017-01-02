<?php

namespace App\Http\Controllers\HttpRequest;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class HttpRequestController extends Controller
{
    private $client = null;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function doRequest($url = '', $method = 'GET', $option = [])
    {
        $res = $this->client->request($method, $url, $option);
        $result = $res->getBody();
        try{
            return (array) json_decode($result, true);
        } catch (Exception $e) {
            Log::info("Error in parseing json to array. Caught exception:: ".$e->getMessage());
        }
        return null;
    } 
}
