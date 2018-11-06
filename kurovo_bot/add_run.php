<?
if ($data){
    
    if ($data=='/add_run'){
        
        $inline_button1 = array("text"=>"Сегодня","callback_data"=>"/add_run_today");
        $inline_button2 = array("text"=>"Завтра","callback_data"=>"/add_run_tomorrow");
        $inline_button3 = array("text"=>"Не, перехотелось бежать","callback_data"=>"/start_menu");

        $sel = $mysqli->query("SELECT * FROM `run` WHERE `user_id`=".$user_id);
        if ($sel->num_rows){
            $sel->data_seek();
            $res = $sel->fetch_assoc();

            $inline_button0 = array("text"=>"Удалить все пробежки %E2%9B%94","callback_data"=>"/del_run");

            $inline_keyboard = [[$inline_button0],[$inline_button1],[$inline_button2],[$inline_button3]];
            $text = urlencode("У тебя уже есть план побегать ".$res['date']."! (а может и ещё какие-то) \n Хочешь удалить пробежки или добавить ещё одну запись?");
        }

        else{
            $inline_keyboard = [[$inline_button1],[$inline_button2],[$inline_button3]];
            $text = urlencode("Когда бежишь?");
        }

        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 

        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
    }

    if ($data=="/add_run_today"||$data=="/add_run_tomorrow"){
     
        if ($data=="/add_run_today"){
            $run_date = date('Y-m-d', mktime(0,0,0,date("m") , date("d"), date("Y")));
        }

        else {
            $run_date = date('Y-m-d', mktime(0,0,0,date("m") , date("d")+1, date("Y")));
        }

        $sel = $mysqli->query("SELECT * FROM `dialog` WHERE `user_id`=".$user_id);
        if ($sel->num_rows>0) {
            $query = "UPDATE `dialog` SET `date_time`='".$run_date."' WHERE `user_id`=".$user_id;
        }
        else {
            $query = "INSERT INTO `dialog`( `user_id`, `date_time`) VALUES (".$user_id.", '".$run_date."')";
        }
        $dialog = $mysqli->query($query);
        
        $inline_keyboard = [];
        $row_button = [];
        $inline_button = [];
        if ($data=="/add_run_today") {
            for ($i=date("H"); $i<24; $i++ ) {
                $inline_button[$i-date("H")] = array("text"=>$i,"callback_data"=>"/add_run_hour_".$i);
            }
        }
        else {
            for ($i=0; $i<date("H")+1; $i++ ) {
                $inline_button[$i] = array("text"=>$i,"callback_data"=>"/add_run_hour_".$i);
            }
        }
        
        $q = 0;
        $dif = 100;
        $buttons = count($inline_button); 
        for ($i = 8; $i > sqrt($buttons); $i--) {
            if ($i>=$button%$i){
                $dif1 = $i - $button%$i;
                if ($dif1 < $dif){
                    $dif = $dif1;
                    $q = $i;
                }
            }
        }

        $row_button = [];
        $row_num = floor(count($inline_button)/$q);

        for ($i=0; $i < $row_num; $i++) {
            
            for ($j=0; $j<$q; $j++){
                $row_button[$j] = $inline_button[$i*$q+$j];
            }
            $inline_keyboard[$i] = $row_button;
        }

        if (count($inline_button)%$q>0) {
            $row_button = [];
            for ($j=0; $j<count($inline_button)%$q; $j++){
                $row_button[$j] = $inline_button[$i*$q+$j];
            }
            $inline_keyboard[count($inline_keyboard)] = $row_button;
        }

        $inline_keyboard[count($inline_keyboard)] = array(array("text"=>"В начало","callback_data"=>"/start_menu"));
        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode("Во сколько(часы)?");
     
        $message_id = $callback_query['message']['message_id'];
        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);

    }

    if (substr($data, 9, 4)=="hour"){

        $run_hour = substr($data, 14);

        $sel = $mysqli->query("SELECT * FROM dialog WHERE user_id=".$user_id);
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();

        $date = date_parse($res['date_time']);

        $date_time = date("Y-m-d H:i:s", mktime($run_hour, 0, 0, $date["month"] , $date["day"], $date["year"]));

        $query = "UPDATE `dialog` SET `date_time`='".$date_time."' WHERE `user_id`=".$user_id;
        $dialog = $mysqli->query($query);
        
        $inline_keyboard = [];
        $row_button = [];
        $inline_button = [];
        
        for ($i=0; $i<12; $i++ ) {
            $inline_button[$i] = array("text"=>$i*5,"callback_data"=>"/add_run_min_".($i*5));
        }
        
        $q = 6;
        $row_button = [];
        $row_num = floor(count($inline_button)/$q);

        for ($i=0; $i < $row_num; $i++) {
            for ($j=0; $j<$q; $j++){
                $row_button[$j] = $inline_button[$i*$q+$j];
            }
            $inline_keyboard[$i] = $row_button;
        }

        if (count($inline_button)%$q>0) {
            $row_button = [];
            for ($j=0; $j<count($inline_button)%$q; $j++){
                $row_button[$j] = $inline_button[$i*$q+$j];
            }
        
            $inline_keyboard[count($inline_keyboard)] = $row_button;
        }
        
        $inline_keyboard[count($inline_keyboard)] = array(array("text"=>"В начало","callback_data"=>"/start_menu"));
        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 

        $sel = $mysqli->query("SELECT * FROM dialog WHERE user_id=".$user_id);
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();

        $text = urlencode($res['date_time']." ___ ".$res['context']."\nВо сколько(минуты)?");

        $message_id = $callback_query['message']['message_id'];
        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);

    }

    if (substr($data, 9, 3)=="min"){

        $run_min = substr($arr['callback_query']['data'], 13);

        $sel = $mysqli->query("SELECT * FROM dialog WHERE user_id=".$user_id);
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();

        $date = date_parse($res['date_time']);

        $date_time = date("Y-m-d H:i:s", mktime($date['hour'], $run_min, 0, $date["month"] , $date["day"], $date["year"]));

        $query = "UPDATE `dialog` SET `date_time`='".$date_time."' WHERE `user_id`=".$user_id;
        $dialog = $mysqli->query($query);
        

        $inline_button1 = array("text"=>"Добавить комментарий","callback_data"=>"/add_run_comment");
        $inline_button2 = array("text"=>"Просто записать","callback_data"=>"/add_run_record");
        $inline_button3 = array("text"=>"Нафиг, не побегу%E2%9B%94","callback_data"=>"/start_menu");
        
        $inline_keyboard = [[$inline_button1],[$inline_button2],[$inline_button3]];
           
        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 

        $sel = $mysqli->query("SELECT * FROM dialog WHERE user_id=".$user_id);
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        
        $text = urlencode($res['date_time']."\nДобавим комментарий или так записать?");

        $message_id = $callback_query['message']['message_id'];
        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
    }

    if ($arr['callback_query']['data']=='/add_run_comment'){

        $query = "UPDATE `dialog` SET `question`='/add_run_comment' WHERE `user_id`=".$user_id;
        $upd = $mysqli->query($query);
        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$user_id.'&message_id='.$message_id.'&text=Ок, в ближайшем сообщении жду от тебя комментарий к пробежке.');
    }

    if ($arr['callback_query']['data']=='/add_run_record'){
        $sel = $mysqli->query("SELECT * FROM dialog WHERE user_id=".$user_id);
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        
        $query = "INSERT INTO `run` (`user_id`, `date`) VALUES (".$user_id.", '".$res['date_time']."')";
        $ins = $mysqli->query($query);

        $del = $mysqli->query("DELETE FROM `dialog` WHERE `user_id`=".$user_id);
     
        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode("Записано! Есть ещё вопросы?");

        $message_id = $callback_query['message']['message_id'];
        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
    
       
    }
}

else {
    $pos = strpos($message, ":");
    $hour = substr($message,$pos-2,2);
    if ($hour[0]!='0'&&$hour[0]!='1'&&$hour[0]!='2')
        $hour[0] = '0';
    $min = substr($message,$pos+1,2); 
    
    if (($min[0]*10 + $min[1])>59||($hour[0]*10 + $hour[1])>23){
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?&chat_id='.$chat_id.'&text=Непонятный формат времени!');
        exit();
    }
    
    $description = substr($message,strpos($message, ":")+3);

    $i=0;
    if (($hour==date("H")&&$min<date("i"))||$hour<date("H")) $i++;

    $run_time = date('Y-m-d H:i:s', mktime($hour, $min, 0, date("m") , date("d")+$i, date("Y")));


    if ($arr['message']['entities'][0]['user']) {
        $id = $arr['message']['entities'][0]['user']['id'];
    }

    else if ($arr['message']['entities'][0]){

        $pos3 = strpos($message, "@");
        $user = substr($message,$pos3+1);
        $sel = $mysqli->query("SELECT * FROM `users` WHERE `user_name`='".$user."'");
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        $id = $res['user_id'];
    }
    else {
        $id = $user_id;
    }
 
    if($user_id==$chat_id) $notice_start = 0; else $notice_start = 1;
    $query = "INSERT INTO `run`( `user_id`, `date`, `distance`, `description`, `notice_start`) VALUES ('".$id."', '".$run_time."', 0, '".$description."', ".$notice_start.")";
    $ins = $mysqli->query($query);
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?&chat_id='.$user_id.'&text=Ok!');
}

?>