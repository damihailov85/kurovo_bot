<pre>
<?php
include('config.php');

$sel = $mysqli->query("SELECT * FROM `users` WHERE `user_id`>0");
    
for ($i = 0; $i < $sel->num_rows; $i++){ 
    $sel->data_seek($i);
    $res = $sel->fetch_assoc();

    if ($res['squad_id']=='5af46fd23eb95947d1abf760'||$res['squad_id']=='5a8c313fd1ffd37e91aeea37'){
        $team = 1;
        $start_date = date("Y-m-d H:i:s", strtotime('2018-10-09'));
    }
    else {
        $team = 0;
        $start_date = date("Y-m-d H:i:s", strtotime('2018-10-13'));
    }
    $stop_date = date("Y-m-d H:i:s", strtotime('2018-10-15'));

    $ch = curl_init('https://api.squadrunner.co/api/v3/runner/runs/');
    //curl_setopt($ch, CURLOPT_HEADER, true);
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
                                                                
    $payload = json_encode(array("company_id" => "5a1554b82c1350cbd9afbade", 
                                "offset" => 0, 
                                "sort" => "start_time", 
                                "sortAsc" => false, 
                                "user_id" => $res['squad_id'])); 

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_ENCODING, '');

    $file_contents = curl_exec ( $ch );
    if (curl_errno ( $ch )) {
        echo "Error:";
        echo curl_error ( $ch );
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id=-229912167&text='.curl_error($ch));
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
    
    for ($j=0; $j<10; $j++) {
        foreach ($jsonArray[$j] as $key => $value) {
            if ($key=='activity_type'&&$value=='running') {

                //   echo $jsonArray[$j]['distance']."-".$jsonArray[$j]['start_time']."<br />";
                $date = date("Y-m-d H:i:s", strtotime($jsonArray[$j]['start_time']));
                $distance = round($jsonArray[$j]['distance']/1000, 1);

                $query = "SELECT * FROM `battle` WHERE `distance`=".$distance." AND `date`='".$date."' AND `user_id`=".$res['user_id'];
                $sel2 = $mysqli->query($query);
                            
                if ($sel2->num_rows==0&&$date>$start_date&&$date<$stop_date){
                    echo $query;
                    echo "<br/>".$sel2->num_rows."<br/><br/>";   
                
                    $ins = $mysqli->query("INSERT INTO `battle` (`user_id`, `team`, `distance`, `date`) 
                                                        VALUES('".$res['user_id']."', '".$team."', ".$distance.", '".$date."')");
                }
            } 

        }
    }
    
    /*    
    2018-10-06T11:31:16.000Z
    
    @$sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `sender_squad_id`='".$boost["_id"]."'");
        if ($sel2->num_rows==0){

            $sq_time =  date("Y-m-d H:i:s", strtotime($boost["profile"]["boost_regenerated"]));
            
            $ins = $mysqli->query("INSERT INTO `boosts` (`sender_squad_id`, `recipient_squad_id`, `boost_death_time`) 
                                                 VALUES('".$boost["_id"]."', '".$jsonArray["_id"]."', '".$sq_time."')");
        }
    
*/    
}

/*
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
*/
?>

</pre>