<?php

function race_update($column2) 
{
    global $token, $user_id, $mysqli, $message; 

    $sel = $mysqli->query("SELECT * FROM `race` WHERE `author_id`=".$user_id." AND `ready`=0");
    if ($sel->num_rows){
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        $upd = $mysqli->query("UPDATE `race` SET `".$column2."`='".$message."' WHERE `race_id`=".$res['race_id']);
    }
    else {
        $ins = $mysqli->query("INSERT INTO `race` (`".$column2."`, `author_id`) VALUE ('".$message."', ".$user_id.")");
    }

    $sel = $mysqli->query("SELECT * FROM `race` WHERE `author_id`=".$user_id." AND `ready`=0");
    $sel->data_seek(0);
    $res = $sel->fetch_assoc();

    $inline_button1 = array("text"=>"Название","callback_data"=>"/race_new_name");
    $inline_button2 = array("text"=>"Место","callback_data"=>"/race_new_place");
    $inline_button3 = array("text"=>"Дата","callback_data"=>"/race_new_date");
    $inline_button4 = array("text"=>"Описание","callback_data"=>"/race_new_description");
  

    if ($res['date']&&$res['name']) {
        $inline_button5 = array("text"=>"Готово!","callback_data"=>"/race_new_ready");
        $inline_button6 = array("text"=>"Перейти к добавлению дистанций","callback_data"=>"/race_new_ready_add_distance");
        $inline_keyboard = [[$inline_button1,$inline_button2],[$inline_button3,$inline_button4],[$inline_button5], [$inline_button6]];
    }
    else
        $inline_keyboard = [[$inline_button1,$inline_button2],[$inline_button3,$inline_button4]];

    $keyboard=array("inline_keyboard"=>$inline_keyboard);
    $replyMarkup = json_encode($keyboard); 
 
    $text = urlencode("Дата: ".$res['date'].",\nНазвание: ".$res['name'].",\nМесто: ".$res['place'].", \nОписание: ".$res['description']);

    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text='.$text.'&reply_markup='.$replyMarkup);

    $upd = $mysqli->query("DELETE FROM `dialog` WHERE `user_id`=".$user_id);

}

/*
$column = substr($question,10);
file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text='.$column);
race_update($column);
*/

switch($question){
    case "/race_new_name":
        $column = 'name';
        race_update($column);
        break;

    case "/race_new_place":
        $column = 'place';
        race_update($column);
        break;

    case "/race_new_date":
        $date = date_parse($message);
        $message =  $date['year']."-".$date['month']."-".$date['day'];
        $column = 'date';
        race_update($column);
        break;

    case "/race_new_description":
        $column = 'description';
        race_update($column);
        break;

    case "/add_run_comment":
        $sel = $mysqli->query("SELECT * FROM `dialog` WHERE `user_id`=".$user_id);
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();

        $query = "INSERT INTO `run` (`user_id`, `date`, `description`) VALUES (".$user_id.", '".$res['date_time']."', '".$message."')";
        $ins = $mysqli->query($query);
        $upd = $mysqli->query("DELETE FROM `dialog` WHERE `user_id`=".$user_id);

        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode("Отлично, пробежка записана. Есть ещё вопросы?)");

        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text='.$text.'&reply_markup='.$replyMarkup);
        break;
}


?>