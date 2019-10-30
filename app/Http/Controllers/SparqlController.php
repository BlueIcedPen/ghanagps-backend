<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class SparqlController extends Controller
{
    //

    public function getLocation($data) {

        $client = new \GuzzleHttp\Client();
        $headers = ['Accept' => 'application/sparql-results+json'];
        $sparql = 'query=select *{?name a dbo:Place;foaf:name "'.$data.'"@en ;geo:lat ?lat;geo:long ?long}';
        $res = $client->get("http://dbpedia.org/sparql?".$sparql, ["headers" => $headers]);

        $responseString = $res->getBody()->getContents();
        $responseString = str_replace("php"," ",$responseString);
        return response($responseString);
    }

}
