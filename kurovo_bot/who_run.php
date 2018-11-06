<?

$sel = $mysqli->query("SELECT `run`.*,`users`.* FROM `run` LEFT JOIN `users` ON `run`.`user_id`=`users`.`user_id` ORDER BY `date`");

$text = "";
if ($sel->num_rows == 0){
  $text = $message=='??' ? 'Никто не планирует бегать' : 'Никто из планирующих бежать (если такие вообще есть) не нуждается в бустах';
} 
else
  for ($i = 0; $i < $sel->num_rows; $i++)
  {
    $sel->data_seek($i);
    $res = $sel->fetch_assoc();

    $sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['squad_id']."'");
    if($sel2->num_rows==0) $boosts = 0; 
    else $boosts = $sel2->num_rows*10;
  
    $sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['squad_id']."' AND `boost_death_time`>'".$res['date']."'");
    if($sel2->num_rows==0) $boosts2 = 0; 
    else $boosts2 = $sel2->num_rows*10;

    if ($boosts2 == 30&&substr($message,0,2)!='??'&&$message!='/who_run'&&$message!='/who_run@KurovoBot')
      continue; 

    $boost_text = "\xE2\x9A\xA1 ".$boosts."%; будет: <b>".$boosts2."%</b>";

    $date = date_parse($res['date']);
    $min = $date['minute']<10 ? '0'.$date['minute'] : $date['minute'];
    $cur_date = date();
    $date_dif = $res['date'] - $cur_date;
    $date_dif1 = date_parse($date_dif);

    if ($res['link']) $runner_name = "<a href='".$res['link']."'>".$res['first_name']." ".$res['last_name']."</a>";
    else $runner_name = "<b>".$res['first_name']." ".$res['last_name']."</b>";

    if ($res['distance']>0) $runner_dist = " бежит ".$res['distance']." км ";
    else $runner_dist = " ";

    $text .= $runner_name.$runner_dist;
    $text .= ($date['day'] != date("d")) ? "завтра в " : " в ";
    $text .= $date['hour'].":".$min.". "." \n".$boost_text."\n ".$res['description']."\n ______________________";
    $text .= "\n";
  }

  if ($arr['callback_query']['data']=='/who_run') {
    $keyboard=array("inline_keyboard"=>$inline_keyboard);
    $replyMarkup = json_encode($keyboard); 
    $text = urlencode($text);
    file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?parse_mode=HTML&chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
  }

else {
  $text = urlencode($text);
  file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$user_id.'&text='.$text);
}

?>