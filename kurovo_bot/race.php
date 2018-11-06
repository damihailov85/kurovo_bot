<?php

$callback_query = $arr['callback_query'];
$data = $callback_query['data'];
$message_id = $callback_query['message']['message_id'];

$chat_id_in = $callback_query['message']['chat']['id'];

$sel_dialog = $mysqli->query("SELECT * FROM `dialog` WHERE `user_id`=".$chat_id_in);

if ($sel_dialog->num_rows) {
    $sel_dialog->data_seek(0);
    $res_dialog = $sel_dialog->fetch_assoc();

    $question = $res_dialog['question'];
    $context = $res_dialog['context'];
    $context_value = $res_dialog['context_value'];
    
}

$inline_button1 = array("text"=>"1","callback_data"=>"dist_1");
$inline_button2 = array("text"=>"3","callback_data"=>"dist_3");
$inline_button3 = array("text"=>"5","callback_data"=>"dist_5");
$inline_button4 = array("text"=>"Десяка","callback_data"=>"dist_10");
$inline_button5 = array("text"=>"20","callback_data"=>"dist_20");
$inline_button6 = array("text"=>"Половинка","callback_data"=>"dist_21,1");
$inline_button7 = array("text"=>"25","callback_data"=>"dist_25");
$inline_button8 = array("text"=>"30","callback_data"=>"dist_30");
$inline_button9 = array("text"=>"35","callback_data"=>"dist_35");
$inline_button10 = array("text"=>"40","callback_data"=>"dist_40");
$inline_button11 = array("text"=>"Марафон","callback_data"=>"dist_42,2");
$inline_button12 = array("text"=>"50","callback_data"=>"dist_50");
$inline_button13 = array("text"=>"60","callback_data"=>"dist_60");
$inline_button14 = array("text"=>"70","callback_data"=>"dist_70");
$inline_button15 = array("text"=>"Сотка","callback_data"=>"dist_100"); 
$inline_button16 = array("text"=>"Другая...","callback_data"=>"dist_another");       
$inline_button16 = array("text"=>"Готово!","callback_data"=>"/dist_stop");
$inline_keyboard = [
                    [$inline_button1,$inline_button2,$inline_button3],
                    [$inline_button4,$inline_button6],
                    [$inline_button8,$inline_button9],
                    [$inline_button11],
                    [$inline_button14,$inline_button15],
                    [$inline_button16]
                ];

$keyboard_distance=array("inline_keyboard"=>$inline_keyboard);


switch($data){
    case "/race_list":
        $sel = $mysqli->query("SELECT * FROM `race` WHERE `date`>NOW() ORDER BY `date` ");
        $inline_keyboard = [];
        $inline_row = [];
        if ($sel->num_rows) {
            for ($i=0; $i < $sel->num_rows; $i++) {
                $sel->data_seek($i);
                $res = $sel->fetch_assoc();

                $inline_button = array("text"=>$res['date']." : ".$res['name'],"callback_data"=>"/race_view_".$res['race_id']);
                $inline_row[0] = [$inline_button];
                $inline_keyboard[$i]=$inline_row[0];
            }
        }

        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode("Список забегов");
 

        
        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$chat_id_in.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);


        break;

    case "/race_new":

        $inline_button1 = array("text"=>"Название ","callback_data"=>"/race_new_name");
        $inline_button2 = array("text"=>"Место","callback_data"=>"/race_new_place");
        $inline_button3 = array("text"=>"Дата","callback_data"=>"/race_new_date");
        $inline_button4 = array("text"=>"Описание","callback_data"=>"/race_new_description");
    
        $inline_keyboard = [[$inline_button1,$inline_button2],[$inline_button3,$inline_button4]];
    
        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode("Добавляем новый забег. С чего начнем?");
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text='.$text.'&reply_markup='.$replyMarkup);
        break;

    case "/race_new_name":

        $ins = $mysqli->query("INSERT INTO `dialog` (`question`, `user_id`) VALUE ('".$data."', ".$chat_id_in.")");

        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text=Введи название забега'); 
        break;

    case "/race_new_place":
        $ins = $mysqli->query("INSERT INTO `dialog` (`question`, `user_id`) VALUE ('".$data."', ".$chat_id_in.")");
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text=Где проходит?'); 
        break;

    case "/race_new_date":
        $ins = $mysqli->query("INSERT INTO `dialog` (`question`, `user_id`) VALUE ('".$data."', ".$chat_id_in.")");
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text=Введи только дату!(время старта добавим чуть позже)'); 
        break;

    case "/race_new_description":
        $ins = $mysqli->query("INSERT INTO `dialog` (`question`, `user_id`) VALUE ('".$data."', ".$chat_id_in.")");
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text=Введи описание забега'); 
        break;

    case "/race_new_ready":
        $sel = $mysqli->query("SELECT * FROM `race` WHERE `author_id`=".$chat_id_in." AND ready=0");
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        $upd = $mysqli->query("UPDATE `race` SET `ready`=true WHERE `author_id`=".$chat_id_in);
        $text = urlencode("Отлично! Забег ".$res['name']."(".$res['date'].") создан!");

        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text='.$text);
        break;

    case "/race_new_ready_add_distance":
        $sel = $mysqli->query("SELECT * FROM `race` WHERE `author_id`=".$chat_id_in." AND ready=0");
        $upd = $mysqli->query("UPDATE `race` SET `ready`=1 WHERE `author_id`=".$chat_id_in);

        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        $ins = $mysqli->query("INSERT INTO `dialog` (`question`, `user_id`, `context`) VALUE ('race_id', ".$chat_id_in.", ".$res['race_id'].")");

        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text='.$res['race_id']);

        $inline_button1 = array("text"=>"Поехали!","callback_data"=>"/race_distance");
        
        $inline_keyboard = [[$inline_button1]];

        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
 
        $text = urlencode($res['name']."(".$res['date'].") \nЖми кнопку и давай добавим дистанции\n(да, неудобный промежуточный пункт, пока не понял, как избежать без бардака в коде)");

        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text='.$text.'&reply_markup='.$replyMarkup);
        break;

    case "/race_distance":

        $replyMarkup = json_encode($keyboard_distance); 

        $text = urlencode("Выбери дистанцию или нажми 'Готово!' для завершения");

        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text='.$text.'&reply_markup='.$replyMarkup);

        break;

    case "/dist_stop":
        $upd = $mysqli->query("DELETE FROM `dialog` WHERE `user_id`=".$chat_id_in);
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text=Отлично! Информация о забеге записана.');
 
        break;
}

$responce = explode("_",$data);

if ($responce[0]=='dist'){
    $dist = substr($data, 5);
    if ($dist!='another'){
        $sel = $mysqli->query("SELECT * FROM `race_distance` WHERE `distance`='".$dist."'");
        if($sel->num_rows){
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text=Такая дистанция уже записана!');
            $replyMarkup = json_encode($keyboard_distance); 
            $text = urlencode("Выбери дистанцию или нажми 'Готово!' для завершения");
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text='.$text.'&reply_markup='.$replyMarkup);

        }
        else {
            $ins = $mysqli->query("INSERT INTO `race_distance` (`distance`, `race_id`) VALUES ('".$dist."', ".$context.")");
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text=Записано!');
            $replyMarkup = json_encode($keyboard_distance); 
            $text = urlencode("Выбери дистанцию или нажми 'Готово!' для завершения");
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text='.$text.'&reply_markup='.$replyMarkup);

        }
    }
}


if (substr($data, 0,11)=="/race_view_"){
    $race = substr($data, 11);
 
    if ($race=='dist'){
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text=Тут будет перечень дистанций с возможностью подцепиться');
    }
    else if ($race=='who'){
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id_in.'&text=Перечень тех, кто участвует в забеге');
    }
    else {
        
        $inline_button1 = array("text"=>"Дистанции%E2%9B%94","callback_data"=>"/race_view_dist");
        $inline_button2 = array("text"=>"Кто бежит?%E2%9B%94","callback_data"=>"/race_view_who_");
        $inline_button3 = array("text"=>"Редактировать%E2%9B%94","callback_data"=>"/race_view_edit");
        $inline_button4 = array("text"=>"К списку забегов","callback_data"=>"/race_list");

        $inline_keyboard = [[$inline_button1],[$inline_button2],[$inline_button3],[$inline_button4]];

        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 


        $sel = $mysqli->query("SELECT * FROM `race` WHERE `race_id`=".$race);
        $inline_keyboard = array();

        $sel->data_seek(0);
        $res = $sel->fetch_assoc();

        $text = urlencode($res['date'].",".$res['place']."\n".$res['name']."\n".$res['description']);

        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$chat_id_in.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);

    }
}


function save_question() {
    // записываем вопрос в dialog
    // для определения контехта 
}










?>