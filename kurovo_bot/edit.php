 <?

include('config.php');

$sel = $mysqli->query("SELECT * FROM `run` WHERE date < NOW()");

for ($i = 0; $i < $sel->num_rows; $i++){
    $sel->data_seek($i);
    $res = $sel->fetch_assoc();
    $del = $mysqli->query("DELETE FROM `run` WHERE run_id = ".$res['run_id']);
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

        $run = "";

        $runner_name = "<b>".$res['first_name']."</b>";

        if ($res['distance']>0) $runner_dist = " бежит ".$res['distance']." км ";
        else $runner_dist = " бежит ";

        $sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['squad_id']."'");
        if($sel2->num_rows==0) $boosts = 0; 
        else $boosts = $sel2->num_rows*10;
        $sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['squad_id']."' AND `boost_death_time`>'".$res['date']."'");
        if($sel2->num_rows==0) $boosts2 = 0; 
        else $boosts2 = $sel2->num_rows*10;

        $boost_text = "сейчас: ".$boosts."%; когда побежит будет: <b>".$boosts2."%</b>";

        $run .= $res['run_id']."**".$runner_name." бежит ";
        $run .= " через час ";
        $run .= "(".$boost_text.").".$res['description'];
            
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$group_id.'&text='.$run);

    }
        
    if ($res['notice_start']==0)
    {
        $upd = $mysqli->query("UPDATE `run` SET `notice_start`=1 WHERE `run_id`=".$res['run_id']);
        $date = date_parse($res['date']);
        
        $min = $date['minute']<10 ? '0'.$date['minute'] : $date['minute'];
        
        $cur_date = date();
        $date_dif = $res['date'] - $cur_date;
        $date_dif1 = date_parse($date_dif);
        $run = "";

        $runner_name = "<b>".$res['first_name']." ".$res['last_name']."</b>";

        $sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['squad_id']."'");
        if($sel2->num_rows==0) $boosts = 0; 
        else $boosts = $sel2->num_rows*10;
        
        $sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['squad_id']."' AND `boost_death_time`>'".$res['date']."'");
        if($sel2->num_rows==0) $boosts2 = 0; 
        else $boosts2 = $sel2->num_rows*10;

        $boost_text = "сейчас: ".$boosts."%; когда побежит будет: <b>".$boosts2."%</b>";

        $run .= $res['run_id']."**".$runner_name." бежит ";
        $run .= ($date['day'] != date("d")) ? "завтра в " : "сегодня в ";
        $run .= $date['hour'].":".$min." (".$boost_text.")".$res['description'];
        
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$group_id.'&text='.$run);

        // !!! Заменить айди на групповой '.$group_id.' 412298116 - мой

    }   
        
}

$sel = $mysqli->query("SELECT * FROM `boosts` WHERE `boost_death_time`<NOW()");

for ($i = 0; $i < $sel->num_rows; $i++)
{

    $sel->data_seek($i);
    $res = $sel->fetch_assoc();
    $del = $mysqli->query("DELETE FROM `boosts` WHERE boost_id = ".$res['boost_id']);
    
    $sel3 = $mysqli->query("SELECT * FROM `users` WHERE `squad_id`='".$res['sender_squad_id']."'");
    $sel3->data_seek(0);
    $res3 = $sel3->fetch_assoc();  

    
    // в личку бустодателю. OK. '.$res3['user_id'].'
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$res3['user_id'].'&text=Хай! У тебя созрел буст! Может быть поделишься с кем-нибудь?');


    $sel3 = $mysqli->query("SELECT `run`.*,`users`.* FROM `run` JOIN `users` ON `run`.`user_id`=`users`.`user_id` WHERE `users`.`squad_id`='".$res['recipient_squad_id']."' ORDER BY `date`");
    
    if ($sel3->num_rows > 0){
        $sel3->data_seek(0);
        $res3 = $sel3->fetch_assoc();
        
        $sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['recipient_squad_id']."'");
        if($sel2->num_rows==0) $boosts = 0; 
        else $boosts = $sel2->num_rows*10;

        $sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['recipient_squad_id']."' AND `boost_death_time`>'".$res3['date']."'");
        if($sel2->num_rows==0) $boosts2 = 0; 
        else $boosts2 = $sel2->num_rows*10;

        $boost_text = "сейчас: ".$boosts."%; когда побежит будет: <b>".$boosts2."%</b>";
    

        // это в группу '.$group_id.'

        $date = date_parse($res3['date']);
        $min = $date['minute']<10 ? '0'.$date['minute'] : $date['minute'];
        
        // Это что?
        $cur_date = date();
        $date_dif = $res3['date'] - $cur_date;
        $date_dif1 = date_parse($date_dif);
        
        $run = " бежит ";

        $runner_name = "<b>".$res3['first_name']." ".$res3['last_name']."</b>";

        if ($res['distance']>0) $runner_dist = " ".$res3['distance']." км ";
        else $runner_dist = " ";

        $run .= ($date['day'] != date("d")) ? "завтра в " : "сегодня в ";
        $run .= $date['hour'].":".$min." ( ".$boost_text."). ".$description;
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$group_id.'&text='.$res3['run_id'].'**(сгорел буст)'.$runner_name.' '.$run);

    }
    
}
echo 'ok';
?>