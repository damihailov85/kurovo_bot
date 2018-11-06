<pre>
<?php
include('config.php');

    $ch = curl_init('https://api.squadrunner.co/api/v3/battle/ranking/');
    //curl_setopt($ch, CURLOPT_HEADER, true);
    $headers = array(
       "Origin:https://squadrunner.co",
       "Accept-Encoding:gzip, deflate, br",
       "web-api-key:8C4VfqUwTyn0wx2838HWSXQ1WqZO8R2S",
       "Accept-Language:en-US,en;q=0.9,ru;q=0.8,ru-RU;q=0.7",
       "Authorization:JWT eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI1YThiZDgxMWQxZmZkMzdlOTFhYTk2MzgiLCJmaXJzdF9uYW1lIjoiRGVuaXMiLCJjb21wYW55X2lkIjoiNWExNTU0YjgyYzEzNTBjYmQ5YWZiYWRlIiwibGFuZ3VhZ2UiOiJlbiIsImVtYWlsIjoiZGFtaWhhaWxvdjg1QGdtYWlsLmNvbSIsImlzX3BybyI6ZmFsc2UsImlzX3Byb19leHBpcmF0aW9uX2RhdGUiOiIyMDE4LTAzLTIwVDA4OjEwOjU3LjE0MFoiLCJpc19wcm9fZXhwaXJhdGlvbl9kYXRlX3RpbWVzdGFtcCI6MTUyMTUzMzQ1Ny4xNCwiaWF0IjoxNTQwOTMxMzExLCJleHAiOjE1NDM1MjMzMTEsImlzcyI6InNxdWFkci5jbyJ9.-sFZPZMBxAKEhvDIrAoYlHyTJxpc-HLBB0z91jreuDU",
       "Content-Type:application/json",
       "Accept:application/json, text/plain, */*",
       "Referer:https://squadrunner.co/app/en/app/home",
       "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36",
       "Connection:keep-alive");
                                                                
    $payload = json_encode(array(
                "company_id"=>"5a1554b82c1350cbd9afbade", 
                "offset"=> "0", 
                "sort"=> "rank_league", 
                "sortAsc"=> "true", 
                "user_id"=> "5a8bd811d1ffd37e91aa9638"
        )); 

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_ENCODING, '');

    $file_contents = curl_exec ( $ch );
    if (curl_errno ( $ch )) {
        echo "Error:";
        echo curl_error ( $ch );
      //  file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id=-229912167&text='.curl_error($ch));
        curl_close ( $ch );
        exit ();
    }
    curl_close ( $ch );

    $jsonArray = json_decode($file_contents,true);
    
    if ($file_contents=='Unauthorized') {
        $unauthorized = 1;
    }
    if (!$file_contents) {
        $squad_empty = 1;
    }
    
   // echo $file_contents;



    $text = '';
    foreach ($jsonArray as $key=>$value) {
        if ($jsonArray[$key]['name']=='Kurovo running club') {
            if ($jsonArray[$key]['rank_league']>29) {
                $text .= $jsonArray[28]['rank_league'].". ".$jsonArray[28]['name']."(".round($jsonArray[28]['points_league']/100).") \n.  .  .\n";
            
            
                $down = ($key - 28)>3 ? 3 : ($key - 28);
                for ($i=$down; $i>0 ; $i--)
                    $text .= $jsonArray[$key-$i]['rank_league'].". ".$jsonArray[$key-$i]['name']."(".round($jsonArray[$key-$i]['points_league']/100).") \n";
                
                $text .=  "<b>".$jsonArray[$key]['rank_league'].". ".$jsonArray[$key]['name']."(".round($jsonArray[$key]['points_league']/100).")</b> \n";
        
                for ($i=1; $i<4 ; $i++)
                    $text .= $jsonArray[$key+$i]['rank_league'].". ".$jsonArray[$key+$i]['name']."(".round($jsonArray[$key+$i]['points_league']/100).") \n";
                
            }     
            else {
                for ($i=3; $i>0 ; $i--)
                    $text .= $jsonArray[$key-$i]['rank_league'].". ".$jsonArray[$key-$i]['name']."(".round($jsonArray[$key-$i]['points_league']/100).") \n";
            
                $text .=  "<b>".$jsonArray[$key]['rank_league'].". ".$jsonArray[$key]['name']."(".round($jsonArray[$key]['points_league']/100).")</b> \n";
    
                for ($i=1; $i<4 ; $i++)
                    $text .=  $jsonArray[$key+$i]['rank_league'].". ".$jsonArray[$key+$i]['name']."(".round($jsonArray[$key+$i]['points_league']/100).") \n";

            }   
        }
    }

    if (!$user_id){
        $text = urlencode($text);
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$group_id.'&text='.$text);
    }




if ($unauthorized||$squad_empty) {
    if ($unauthorized) {
        echo 'Ошибка авторизации в Squad';
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id=-229912167&text=Ошибка авторизации в Squad');
    }
    if ($squad_empty) {
        echo 'Squad вернул пустой ответ';
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id=-229912167&text=Squad вернул пустой ответ');
    }
}
else {
    echo 'OK';
}


?>

</pre>