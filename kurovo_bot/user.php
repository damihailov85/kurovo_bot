<?
if ($arr['message']['text']) {

    $sel = $mysqli->query("SELECT * FROM `users` WHERE `user_id`='".$user_id."'");
    $q = $sel->num_rows;

    if (!$q) {

        if ($user_name) $un = $user_name; else $un = '';
        if ($first_name) $fn = $first_name; else $fn = '';
        if ($last_name) $ln = $last_name; else $ln = '';
        $ins = $mysqli->query("INSERT INTO `users`( `user_id`, `user_name`, `first_name`, `last_name`) 
                                VALUES ('".$user_id."', '".$un."','".$fn."', '".$ln."')");

        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Ты зарегистрирован(а), '.$fn.'!');
    }

    else {
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        // file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text='.$res['user_name'].'/'.$res['first_name'].'/'.$res['last_name'].'/'.$res['user_id'] );
        if ($user_name!=$res['user_name']) 
            $upd = $mysqli->query("UPDATE `users` SET `user_name`='".$user_name."' WHERE `user_id`=".$user_id);
        if ($first_name!=$res['first_name']) $fn = $first_name; else $fn = '';
            $upd = $mysqli->query("UPDATE `users` SET `first_name`='".$first_name."' WHERE `user_id`=".$user_id);
        if ($last_name!=$res['last_name']) $ln = $last_name; else $ln = '';
           $upd = $mysqli->query("UPDATE `users` SET `last_name`='".$last_name."' WHERE `user_id`=".$user_id);

        $sel_dialog = $mysqli->query("SELECT * FROM `dialog` WHERE `user_id`='".$user_id."'");
        if ($sel_dialog->num_rows) {
            $sel_dialog->data_seek(0);
            $res_dialog = $sel_dialog->fetch_assoc();
    
            $question = $res_dialog['question'];
            $context = $res_dialog['context'];
            $context_value = $res_dialog['context_value'];
        }
    }
}
?>