<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use \CloudConvert\Api;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    public function getLocationInfo($long = null, $lat = null) 
    {
        $places = $this->queryGooglePlaces($long, $lat);
        $text = $this->getLocationText($places);
        $text = preg_replace( "/\r|\n/", "", $text );

        $result = exec('curl -u 0c0c75ec-a4a2-4062-95d8-a49dd24decaf:bH6CZOCHPeap -X POST \
--header "Content-Type: application/json" \
--header "Accept: audio/wav" \
--data "{\"text\":\"'.$text.'\"}" \
"https://stream.watsonplatform.net/text-to-speech/api/v1/synthesize" \
> public_locations.wav');
        # Print response.
        


        $api = new Api("fJA6VwxM0PQU8JuKOcVNswtVClJOtSfRSXCDi1AwfJSdnCC6h2JKqeuZPH__CqXS6Kn_NujSWKdm4V9VygmwbA");
        
        $api->convert([
            "input" => "upload",
            "inputformat" => "wav",
            "outputformat" => "mp3",
            "file" => fopen('../public/public_locations.wav', 'r'),
        ])
        ->wait()
        ->download();
    }



    protected function queryGooglePlaces($lat, $long) 
    {
        $account_key = $_ENV['GOOGLE_API'];

        $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?key=".$account_key."&location=".$lat.",".$long."&radius=100";

        $user = \App\User::findOrFail(1);

        foreach ($user->places as $value) {
            $types[] = $value->name;
        }

        $url .= "&types=" . implode('|', $types);

        $curl = curl_init();
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_RETURNTRANSFER => true,
        );

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $data = json_decode($response);

        $results = array_slice($data->results, 0, 1);
        // $results = $data->results;
        $placesdesc = [];
        foreach ($results as $gPlace) {
            $placeName = $gPlace->name;
            $url = "https://maps.googleapis.com/maps/api/place/details/json?key=".$account_key."&placeid=". $gPlace->place_id;
            $curl = curl_init();
            $options = array(
                CURLOPT_URL            => $url,
                CURLOPT_HEADER         => false,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_RETURNTRANSFER => true,
            );

            curl_setopt_array($curl, $options);
            // var_dump($gPlace);
            $gPlace = json_decode(curl_exec($curl));

            // var_dump('##############');
            
            //Gather info from FreeBase 
            $url = "https://www.googleapis.com/freebase/v1/search?"
                    ."indent=true"
                    ."&key=".$account_key
                    ."&filter=%28all"
                    ."+type%3Alocation"
                    ."+name%3A%22". urlencode($gPlace->result->name) ."%22"
                    ."%28within+radius%3A100ft"
                    ."+lon%3A". $gPlace->result->geometry->location->lng
                    ."+lat%3A". $gPlace->result->geometry->location->lat ."%29%29"
                    ."&output=%28description%29";           
            $curl = curl_init();
            $options = array(
                CURLOPT_URL            => $url,
                CURLOPT_HEADER         => false,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_RETURNTRANSFER => true,
            );

            curl_setopt_array($curl, $options);
            $FreeBase = json_decode(curl_exec($curl));
            //ensure we got results from FreeBase
            //All we want from FreeBase is the Description
            $info = [];
            $info['name'] = $placeName;

                if ($FreeBase->status == "200 OK" && $FreeBase->hits > 0 && $FreeBase->result)  {
                    $member = "/common/topic/description";
                    $Description = $FreeBase->result[0]->output->description->$member;
                    $info['desc'] = $Description[0];
                }

                $placesdesc[] = $info;
        }

        return $placesdesc;
    }

    public function getLocationText($places) {
        $text = "Here's what's around you: ";

        foreach ($places as $key => $value) {
           $text .= $value['name'] . ". ";
           if (isset($value['desc'])) {
                $text .= $value['desc'] . ". ";
           } else {
                $text .= "No Description available.";
           }
        }
        return $text;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
