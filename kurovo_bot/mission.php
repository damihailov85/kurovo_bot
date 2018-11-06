<pre>
<?php
include('config.php');

if ($jwt)
{
    $ch = curl_init('https://api.squadrunner.co/api/v3/missions/missionrunner/');

    $headers = array(
           "Origin:https://squadrunner.co",
           "Accept-Encoding:gzip, deflate, br",
           "web-api-key:8C4VfqUwTyn0wx2838HWSXQ1WqZO8R2S",
           "Accept-Language:en-US,en;q=0.9,ru;q=0.8,ru-RU;q=0.7",
           $jwt,
           "Content-Type:application/json",
          "Accept:application/json, text/plain, */*",
          "Referer:https://squadrunner.co/app/en/app/home",
          "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36",
         "Connection:keep-alive");
                             
    $payload = json_encode(array(
                "company_id"=> "5a1554b82c1350cbd9afbade",
                "user_id" => "5a8bd811d1ffd37e91aa9638"
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
        curl_close ( $ch );
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text='.curl_error($ch));
    
        exit ();
    }
    curl_close ( $ch );
     
        
    $jsonArray = json_decode($file_contents,true);

    $mission_id = $jsonArray['mission']['_id'];

    $text = "Сегодняшняя миссия: \n".$jsonArray['mission']['name']."\n".$jsonArray['mission']['reward']." points";

    if ($jsonArray['mission']['missionTemplate_id']['mode']!="solo"){
        $text .= "\nКомандная(mode: ".$jsonArray['mission']['missionTemplate_id']['mode'].")";
    }

    $start_date = date("U", strtotime($jsonArray['mission']['start_date']));
    $end_date = date("U", strtotime($jsonArray['mission']['end_date']));
    
    $diff = round(($end_date - $start_date)/(60*60*24));

    $date = date("Y-m-d");
    $end_date = date("Y-m-d", strtotime($jsonArray['mission']['end_date']));

    if ($diff>1&&$end_date!=$date) {
        $text .="\nНа ".$diff." дня, можно и завтра пробежать)) \nНа всякий случай, проверочка: до".strtotime($jsonArray['mission']['end_date'])." по Парижу.";
    }
        
    $text = urlencode($text);
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id=412298116&text='.$text);
  //  file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$group_id.'&text='.$text);
    
    $sel = $mysqli->query("SELECT * FROM `mission` WHERE `start_date`<NOW() AND `end_date`>NOW()");
    if ($sel->num_rows==0) {
        $query = "INSERT INTO `mission` (`start_date`,`end_date`,`type`,`condition`,`name`,`value`,`points`, `duration`, `mode`) 
                                 VALUES ('".$jsonArray['mission']['start_date']."',
                                         '".$jsonArray['mission']['end_date']."',
                                         '".$jsonArray['mission']['missionTemplate_id']['valueType']."',
                                         '".$jsonArray['mission']['missionTemplate_id']['condition']."',
                                         '".$jsonArray['mission']['name']."',
                                          ".$jsonArray['mission']['value'].",
                                          ".$jsonArray['mission']['reward'].",
                                          ".$diff.",
                                         '".$jsonArray['mission']['missionTemplate_id']['mode']."')";
        echo $query;
        $ins = $mysqli->query($query);
    }


    $sel = $mysqli->query("SELECT * FROM `users`");
    for ($i=0; $i<$sel->num_rows; $i++){
        $sel->data_seek($i);
        $res = $sel->fetch_assoc();
        if ($res['jwt']!='0'&&$jsonArray['mission']['missionTemplate_id']['mode']=="solo"){
            
            $ch = curl_init('https://api.squadrunner.co/api/v3/missions/subscribe/');

            $headers = array(
           "Origin:https://squadrunner.co",
           "Accept-Encoding:gzip, deflate, br",
           "web-api-key:8C4VfqUwTyn0wx2838HWSXQ1WqZO8R2S",
           "Accept-Language:en-US,en;q=0.9,ru;q=0.8,ru-RU;q=0.7",
           "Authorization: ".$res['jwt'],
           "Content-Type:application/json", 
           "Accept:application/json, text/plain, */*",
           "Referer:https://squadrunner.co/app/en/app/home",
           "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36",
           "Connection:keep-alive");
                         
            $payload = json_encode(array(
                    "user_id" => $res['squad_id'], 
                    "mission_id" => $mission_id
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
                file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text='.curl_error($ch));
                curl_close ( $ch );
                exit ();
            }
            curl_close ( $ch );
            echo $file_contents."</br>";
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text='.$res['user_id']."\n".$res['squad_id']."\n".$res['jwt']);
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text='.$file_contents);
        }
    }
    
}

else 
file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text=Токен не найден...');

?>
</pre>