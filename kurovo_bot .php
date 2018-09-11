<?
include('kurovo_bot/config.php');
header('Content-Type: text/html; charset=utf-8', true); 

$body = file_get_contents('php://input'); 
$arr = json_decode($body, true); 
$token = '627688187:AAEXCcdchgdvrteLJH-00VZmVy_0UHRwiiZOUVQ';

if (!$body) { include('kurovo_bot/info.php'); exit(); } // можно убрать и на сервере просто info.php запускать 


$message = $arr['message']['text']; 
$first_name = $arr['message']['chat']['first_name'];
$last_name = $arr['message']['chat']['last_name'];
$user_name = $arr['message']['chat']['username'];
$chat_id = $arr['message']['chat']['id']; //Куда отправлять ответ
$file_id = $arr['message']['sticker']['file_id'];
$user_id = $arr['message']['from']['id'];

include('kurovo_bot/user.php'); //проверка записи в бд, если нет-создание



if (substr($message,0,1)=='!')
{

    $pos = strpos($message, ":");
    
    $hour = substr($message,$pos-2,2);
    $min = substr($message,$pos+1,2); 
    
    strrpos($message, ' ', $pos);
    $dist = substr($message,strrpos($message, ' ', $pos)+1,strlen($message)-1); 
 
    $i=0;
    if (($hour==date("H")&&$min<date("i"))||$hour<date("H")) $i++;

    $run_time = date('Y-m-d H:i:s', mktime($hour, $min, 0, date("m") , date("d")+$i, date("Y")));

 //   file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?&chat_id='.$chat_id.'&text='.$run_time);
    if($user_id==$chat_id) $notice_start = 0; else $notice_start = 1;
    $ins = $mysqli->query("INSERT INTO `run`( `user_id`, `date`, `distance`, `boosts`, `notice_start`) VALUES ('".$user_id."', '".$run_time."', ".$dist.", 0, ".$notice_start.")");
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?&chat_id='.$chat_id.'&text=Записано!');

}

if (substr($message,0,1)=='?')
{
    $sel = $mysqli->query("SELECT `run`.*,`users`.* FROM `run` JOIN `users` ON `run`.`user_id`=`users`.`user_id` WHERE boosts < 30 ORDER BY `distance` DESC");
    // может добавить столбец "вес пробежки" и выводить список, отсортированный по значимости??
    // file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Можно ответить на конкретное сообщение бота числом 0,10,20 или 30, указав тем самым достоверное количество бустов у человека. Те, кому указали 30, в список не попадают.');
        
    for ($i = 0; $i < $sel->num_rows; $i++)
    {
        $sel->data_seek($i);
        $res = $sel->fetch_assoc();

        $day = substr($res['date'], 8, 2);
        $hour = substr($res['date'], 11, 2);
        $min = substr($res['date'], 14, 2);

        $run = "";
        if ($res['link'])
        {
            $run .= $res['run_id']."**<a href='".$res['link']."'>".$res['first_name']."</a> бежит ".$res['distance']." км ";
            $run .= ($day == date("d")+1) ? "завтра в" : "сегодня в";
            $run .= $hour.":".$min."(".$res['boosts']."%)";
            
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$chat_id.'&text='.$run);
        }
            
        else
        {
            $run .= $res['run_id']."**".$res['first_name']." бежит ".$res['distance']." км ";
            $run .= ($day == date("d")+1) ? "завтра в" : "сегодня в";
            $run .= $hour.":".$min."(".$res['boosts']."%)";
            
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text='.$run);
        }
            // Когда все зарегистрируются, можно прописать понятное имя каждому пользователю дополнительным столбцом 
            // и выводить/читать его, но сейчас могут получиться повторы по Еленам/Женям.
    }
}

if ($message == 10||$message == 20||$message == 30)
{
    $reply_to_message = $arr['message']['reply_to_message']['text']; 
    if ($reply_to_message)
    {
        $run = explode("**",$reply_to_message);
        $upd = $mysqli->query("UPDATE `run` SET `boosts`=".$message." WHERE `run_id`=".$run[0]);
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Спасибо!');
    }
}    
    
if ($message == 'Удалить'|| $message == 'удалить' || $message == 'delete')
{
    $reply_to_message = $arr['message']['reply_to_message']['text']; 
    $run = explode("**",$reply_to_message);
    
    $sel = $mysqli->query("SELECT * FROM `run` WHERE `run_id`=".$run[0]);
    $sel->data_seek(0);
    $res = $sel->fetch_assoc();

    if ($user_id==$res['user_id'])
    {
        $upd = $mysqli->query("DELETE FROM `run` WHERE `run_id`=".$run[0]);
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Удалено!');
    }
    else file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Нельзя удалить чужую пробежку!');
}  


/*
if ($message=='/start'||$message=='/help')
{   // если здесь(в коде) часть текста перенести на новую строку, то в телеге вылезает подчеркивание...
    $text = $first_name."! Здесь можно посмотреть, кто из куровчан нуждается в бустах (жми /who_run или выбери в списке, который вылезет по нажатию [ / ] в поле ввода сообщения; дальше просто следуй подсказкам). А также тут можно сообщить о  своей планируемой пробежке(жми /my_run или.. ну, ты знаешь ), чтобы и тебя не обделили!";
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?&chat_id='.$chat_id.'&text=Привет, '.$text);
}

// Ну вот как-то так только смог организовать. Телега же должна понимать, на какой вопрос я ей отвечаю. 
// Такой вот "как бы айди" вопроса записывается в таблицу (users - last_question).

if ($lq == 0)
{
    if ($message == '/my_run')
    {    
        // Добавить предварительную проверку на наличие запланированных пробежек
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Когда?(укажи время для сегодняшней пробежки через ":". Для завтрашней добавь после времени "завтра"');
        $mysqli->query("UPDATE `users` SET `last_question`=1 WHERE `user_id`=".$chat_id);
    }

    if ($message == '/my_run_del'||$message == '/my_run_edit')
    {
        // Что если есть две записи?
        $upd = $mysqli->query("UPDATE `users` SET `last_question`=5 WHERE `user_id`=".$chat_id);
        $sel = $mysqli->query("SELECT * FROM `run` WHERE `user_id`='".$chat_id."'");
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text='.$res['date'].'-'.$res['distance'].'км. Удалить эту запланированную пробежку?(Да/Нет)');
    }
   
    if ($message == '/who_run')
    {
        $sel = $mysqli->query("SELECT `run`.*,`users`.* FROM `run` JOIN `users` ON `run`.`user_id`=`users`.`user_id` WHERE boosts < 30 ORDER BY `distance` DESC");
        // может добавить столбец "вес пробежки" и выводить список, отсортированный по значимости??
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Можно ответить на конкретное сообщение бота числом 0,10,20 или 30, указав тем самым достоверное количество бустов у человека. Те, кому указали 30, в список не попадают.');
        
        for ($i = 0; $i < $sel->num_rows; $i++)
        {
            $sel->data_seek($i);
            $res = $sel->fetch_assoc();

            $day = substr($res['date'], 8, 2);
            $hour = substr($res['date'], 11, 2);
            $min = substr($res['date'], 14, 2);

            $run = "";
            if ($res['link'])
            {
            $run .= $res['user_id']."**<a href='".$res['link']."'>".$res['first_name']."</a> бежит ".$res['distance']." км ";
            $run .= ($day == date("d")+1) ? "завтра в" : "сегодня в";
            $run .= $hour.":".$min."(".$res['boosts']."%)";
            
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$chat_id.'&text='.$run);
            }
            
            else
            {
            $run .= $res['user_id']."**".$res['first_name']." бежит ".$res['distance']." км ";
            $run .= ($day == date("d")+1) ? "завтра в" : "сегодня в";
            $run .= $hour.":".$min."(".$res['boosts']."%)";
            
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text='.$run);
            }
            // Когда все зарегистрируются, можно прописать понятное имя каждому пользователю дополнительным столбцом 
            // и выводить/читать его, но сейчас могут получиться повторы по Еленам/Женям.
        }
    }

    if ($message == 10||$message == 20||$message == 30)
    {
        $reply_to_message = $arr['message']['reply_to_message']['text']; 
        $runner = explode("**",$reply_to_message);
        $upd = $mysqli->query("UPDATE `run` SET `boosts`=".$message." WHERE `user_id`=".$runner[0]);
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Готово!');
    }

}

if ($lq == 1) // время пробежки
{
    $time = explode(":", $message);
    $hours = $time[0];
    $min = substr($time[1], 0, 2);
    $day = date("d");
    if (strlen($time[1])>2) $day++;
    
    $run_time = date("d.m H:i", mktime($hours, $min, 0, date("m")  , $day, date("Y")));
    $run_time2 = date("Y-m-d H:i", mktime($hours, $min, 0, date("m")  , $day, date("Y")));    
    $ins = $mysqli->query("INSERT INTO `run`( `user_id`, `date`) VALUES ('".$chat_id."', '".$run_time2."')");
    $upd = $mysqli->query("UPDATE `users` SET `last_question`=2 WHERE `user_id`=".$chat_id);
    
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Дистанция?(целое число запланированных километров)');
}

if ($lq == 2) // Дистанция
{
    $upd = $mysqli->query("UPDATE `run` SET `distance`=".$message." WHERE `user_id`=".$chat_id);
    $upd = $mysqli->query("UPDATE `users` SET `last_question`=3 WHERE `user_id`=".$chat_id);
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Темп?(примерно, ближайшее ожидаемое целое число. пригодится, если будем приоритеты расставлять)');
}

if ($lq == 3)  // Темп
{
    $upd = $mysqli->query("UPDATE `run` SET `pace`=".$message." WHERE `user_id`=".$chat_id);
    $upd = $mysqli->query("UPDATE `users` SET `last_question`=4 WHERE `user_id`=".$chat_id);
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Бусты есть?(Если известно, укажи 10/20/30, иначе отправь 0)');
}

if ($lq == 4) //Бусты
{
    $upd = $mysqli->query("UPDATE `run` SET `boosts`=".$message." WHERE `user_id`=".$chat_id);
    $upd = $mysqli->query("UPDATE `users` SET `last_question`=0 WHERE `user_id`=".$chat_id);
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Спасибо, записано! Удачи! Легких ног! И т.п.');
    $sel = $mysqli->query("SELECT * FROM `stickers` WHERE `emotion`='zbs'");
    $num = rand(0, 11);
    $sel->data_seek($num);
    $res = $sel->fetch_assoc();

    file_get_contents('https://api.telegram.org/bot'.$token.'/sendSticker?chat_id='.$chat_id.'&sticker='.$res['sticker_id']);
}

if ($lq == 5) //Удаление пробежки
{
    if ($message == 'Да')
    {
        $upd = $mysqli->query("DELETE FROM `run` WHERE `user_id`=".$chat_id);
        $upd = $mysqli->query("UPDATE `users` SET `last_question`=0 WHERE `user_id`=".$chat_id);
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Удалено');
        //Добавить грустный/обиженный стикер
    }
    else
    {
        $upd = $mysqli->query("UPDATE `users` SET `last_question`=0 WHERE `user_id`=".$chat_id);
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text=Ок, не удаляем');
    }
}
*/


// ну да, ни разу не использовано))
function sendMessage($chat_id, $message, $replyMarkup) {
  $json = file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text='.$message.'&reply_markup='.$replyMarkup);
}

exit('ok'); //в интернетах вроде пишут, что телеге этот ОК очень нужен. Без него не проверял)

?>