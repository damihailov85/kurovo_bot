<pre>
<?php
include('config.php');

$sel = $mysqli->query("SELECT * FROM `users`");
for($i=0;$i<$sel->num_rows; $i++){
    $sel->data_seek($i);
    $res = $sel->fetch_assoc();

    if ($res['jwt']){
        $sel2 = $mysqli->query("SELECT * FROM `quiz`");
        $sel2->data_seek(0);
        $res2 = $sel2->fetch_assoc();

        if (date("Y-m-d", strtotime($res2['quiz_date']))==date("Y-m-d")) {

            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text=Ответ есть. Пробую отправить...');
            $ch = curl_init('https://api.squadrunner.co/api/v3/quizz/saverunnerquizz/');

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
                "answer" => $res2['answer'],
                "quizz_id" => $res2['quiz_id'],
                "user_id" => $res['squad_id']
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
                file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text=Error: '.curl_error($ch));
                curl_close ( $ch );
                exit ();
            }
            curl_close ( $ch );
    
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text='.$res['user_id']."\n".$res['squad_id']."\n".$res['jwt']);
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id=412298116&text='.$file_contents);

            $jsonArray = json_decode($file_contents,true);
            if ($jsonArray['good_answer']==true)
                file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$res['user_id'].'&text=Правильный ответ отправлен');
            if ($jsonArray['good_answer']==false){
                file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$res['user_id'].'&text='.$jsonArray['good_answer']);       

                file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$res['user_id'].'&text=Кажется, отправился неверный ответ.. Почему??');       
            }
        }
        else {
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$res['user_id'].'&text=Ещё нет ответа...');
        }
    }
}
?>

</pre>