 <?

/*  /usr/local/bin/php /home/damihail/public_html/edit.php  - Для запуска по расписанию на сервере */

// для чистки лишних записей из таблицы. Не могу сравнить дату текущую с записью в таблице 'run'((

include('config.php');
 
 
      
$sel = $mysqli->query("SELECT * FROM `run` WHERE date < NOW()");

for ($i = 0; $i < $sel->num_rows; $i++)
{
    $sel->data_seek($i);
    $res = $sel->fetch_assoc();

    $del = $mysqli->query("DELETE FROM `run` WHERE run_id = ".$res['run_id']);
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?&chat_id='.$group_id.'&text=Удалено');
}


$sel = $mysqli->query("SELECT `run`.*, `users`.*, TIMEDIFF(`run`.`date`, NOW()) AS time FROM `run` JOIN `users` ON `run`.`user_id`=`users`.`user_id`");

for ($i = 0; $i < $sel->num_rows; $i++)
{
    $sel->data_seek($i);
    $res = $sel->fetch_assoc();

    $time = explode(':', $res['time']);
    
    if ((($time[0]=='0'&&$time[1]=='59')||($time[0]=='1'&&$time[1]=='00')||($time[0]=='1'&&$time[1]=='01'))&&$res['notice_1h']==0)
    {
        $upd = $mysqli->query("UPDATE `run` SET `notice_1h`=1 WHERE `run_id`=".$res['run_id']);
        $text = '<'.$res['first_name'].'> бежит '.$res['distance'].'км через час';
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?&chat_id='.$group_id.'&text='.$text);
    }
        
     if ($res['notice_start']==0)
    {
        $upd = $mysqli->query("UPDATE `run` SET `notice_start`=1 WHERE `run_id`=".$res['run_id']);
        $text = '<'.$res['first_name'].'> бежит '.$res['distance'].'км через'.$res['time'].' в '.$res['date'];
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?&chat_id='.$group_id.'&text='.$text);
    }   
        
}


        
 
?>