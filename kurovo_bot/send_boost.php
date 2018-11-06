<pre>
<?php
//include('config.php');

$sel = $mysqli->query("SELECT * FROM `users` WHERE `user_id`=".$user_id);
$sel->data_seek(0);
$res = $sel->fetch_assoc();

if ($res['jwt'])
{
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=Зашел');
    $ch = curl_init('https://api.squadrunner.co/api/v3/enhancements/add/');
    //curl_setopt($ch, CURLOPT_HEADER, true);
    $headers = array(
       "Origin:https://squadrunner.co",
       "Accept-Encoding:gzip, deflate, br",
       "web-api-key:8C4VfqUwTyn0wx2838HWSXQ1WqZO8R2S",
       "Accept-Language:en-US,en;q=0.9,ru;q=0.8,ru-RU;q=0.7",
       "Authorization:".$res['jwt'],
       "Content-Type:application/json",
       "Accept:application/json, text/plain, */*",
       "Referer:https://squadrunner.co/app/en/app/home",
       "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36",
       "Connection:keep-alive");
                
                                                                
    $payload = json_encode(array(
        "company_id" => "5a1554b82c1350cbd9afbade",
        "enhancement_id" => "5a1562972c1350cbd9cd85bb",
        "from_user_id" => $sender_squad_id,
        "to_user_id" => $recipient_squad_id
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
        exit ();
    }
    curl_close ( $ch );
    echo $file_contents;
    echo $jwt;
    
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text='.curl_error($ch));
    
    
    $jsonArray = json_decode($file_contents,true);
    $txt = '';
    foreach ($jsonArray as &$key) {
         $txt .= $key.'='.$jsonArray[$key];
    }
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text='.$txt);
}

else 
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Токен не найден...');
?>

</pre>