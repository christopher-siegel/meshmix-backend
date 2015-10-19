<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use \CloudConvert\Api;

class NewsController extends Controller
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


    public function getNews(Request $request, $long = null, $lat = null) 
    {
        $AccessToken = \App\AccessToken::find($request->input('access_token'));

        $User = $AccessToken->session->user;

        $searchwords = [
            "rt_Business" => "business, financial", 
            "rt_Entertainment" => "entertainment", 
            "rt_Health" => "health",  
            "rt_Politics" => "politics", 
            "rt_Sports" => "sports", 
            "rt_US"  => "USA",  
            "rt_World" => "world news",  
            "rt_ScienceAndTechnology" => "science technology"
        ];

        foreach ($User->categories as $value) {
            $cat_results[$value->name] = $this->queryBing($value->name, $searchwords[$value->name], $long, $lat);
        };

        $text = $this->getNewsText($cat_results);
        $text = preg_replace( "/\r|\n/", "", $text );


//   TEXT TO SPEECH WITH WATSON


//         $result = exec('curl -u 0c0c75ec-a4a2-4062-95d8-a49dd24decaf:bH6CZOCHPeap -X POST \
// --header "Content-Type: application/json" \
// --header "Accept: audio/wav" \
// --data "{\"text\":\"'.$text.'\"}" \
// "https://stream.watsonplatform.net/text-to-speech/api/v1/synthesize" \
// > public_news.wav');
//         # Print response.
        


//         $api = new Api("fJA6VwxM0PQU8JuKOcVNswtVClJOtSfRSXCDi1AwfJSdnCC6h2JKqeuZPH__CqXS6Kn_NujSWKdm4V9VygmwbA");
        
//         $api->convert([
//             "input" => "upload",
//             "inputformat" => "wav",
//             "outputformat" => "mp3",
//             "file" => fopen('../public/public_news.wav', 'r'),
//         ])
//         ->wait()
//         ->download();
        

        // return $this->getNewsText($cat_results);


        return $text;

    }



    protected function queryBing($category, $query, $lat, $long) 
    {
        $account_key = $_ENV['BING_API'];
        $url = "https://api.datamarket.azure.com/Bing/Search/v1/News?\$format=json&Market=%27en-US%27&NewsCategory=".urlencode("'{$category}'")."&Query=".urlencode("'{$query}'");

        if ($long && $lat) {
            $url .= "&Latitude=".$lat."&Longitude=".$long;
        }

        $ch = curl_init();
         
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT,true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
        curl_setopt($ch, CURLOPT_USERPWD, $account_key . ":" . $account_key);
         
        $json = curl_exec($ch);

        curl_close($ch);
        $data = json_decode($json); 
            foreach ($data->d->results as $value) {
                $return['title'] = htmlentities($value->Title);
                $return['text'] = htmlentities($value->Description);
                $splittedText = explode('.', $return['text']);
                array_splice($splittedText, -4);
                $return['text'] = implode('.', $splittedText);
                if (!empty($return['text']) && trim($return['text']) !== "") {
                    $results[] = $return; 
                }
            }
        $results = array_slice($results, 0, 2);
        return $results;

    }


    public function getNewsText($cat_results) {


        $anouncements = [
            "Today in ",
            "Next up: ",
            "Coming next: ",
            "Now onto "
        ];

        $catnames = [
            "rt_Business" => "Business News", 
            "rt_Entertainment" => "Entertainment News", 
            "rt_Health" => "Health News",  
            "rt_Politics" => "Politics", 
            "rt_Sports" => "Sports", 
            "rt_US"  => "US News",  
            "rt_World" => "World News",  
            "rt_ScienceAndTechnology" => "Science and Technology News"
        ];

        $text = "It is " . Carbon::now()->format('l jS \\of F Y h:i A') . ", and here are your newsic news: ";

        foreach ($cat_results as $category => $cat_contents) {
            $text .= $anouncements[array_rand($anouncements)] . $catnames[$category] . ". ";
            foreach ($cat_contents as $news) {
                $text .= $news['title'] . ". " . $news['text'] . ". ";
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
