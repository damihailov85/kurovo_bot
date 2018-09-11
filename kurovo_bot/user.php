<?

$sel = $mysqli->query("SELECT * FROM `users` WHERE `user_id`='".$chat_id."'"); // да, тут путаница с переменными. с личном общении айди чата  = айди юзера
$q = $sel->num_rows;

if (!$q) {
    // единственное обязательное поле в телеге - имя, не логин.
    if ($user_name) $un = $user_name; else $un = '';
    if ($first_name) $fn = $first_name; else $fn = '';
    if ($last_name) $ln = $last_name; else $ln = '';
    $ins = $mysqli->query("INSERT INTO `users`( `user_id`, `user_name`, `first_name`, `last_name`) 
                            VALUES ('".$user_id."', '".$un."','".$fn."', '".$ln."')");
    $lq=0;
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Ты зарегистрирован(а), '.$fn.'!');
}

else {
    $sel->data_seek(0);
    $res = $sel->fetch_assoc();
    // file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text='.$res['user_name'].'/'.$res['first_name'].'/'.$res['last_name'].'/'.$res['user_id'] );
    $lq = $res['last_question'];
}

?>