<?
include('kurovo_bot/config.php');
header('Content-Type: text/html; charset=utf-8', true); 

$body = file_get_contents('php://input'); 
$arr = json_decode($body, true); 
$message = $arr['message']['text']; 
$chat_id = $arr['message']['chat']['id']; 
$file_id = $arr['message']['sticker']['file_id'];
$user_id = $arr['message']['from']['id'];
$first_name = $arr['message']['from']['first_name'];
$last_name = $arr['message']['from']['last_name'];
$user_name = $arr['message']['from']['username'];
$data = '';
if ($arr['callback_query']) {
    $user_id = $arr['callback_query']['message']['chat']['id'];
    $callback_query = $arr['callback_query'];
    $data = $callback_query['data'];
    $message_id = $callback_query['message']['message_id'];
}

$ins = $mysqli->query("INSERT INTO `log`( `user_id`, `chat_id`, `message`) VALUES ('".$user_id."', '".$chat_id."', '".$message."')");

function kuku($qwerty){
    // Функция, чтобы быстро воткнуть в код отправку в телегу переменной или просто маякнуть. Тестирую как умею)))
    global $token, $user_id;
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text=!!!'.$qwerty);
}




include('kurovo_bot/user.php'); 
include('kurovo_bot/main_menu.php');

if ($question&&$message){
    include('kurovo_bot/dialog.php');
    exit('ok');
}

if (substr($data, 0, 5)=="/race"||substr($data, 0, 5)=="/dist") {
    include('kurovo_bot/race.php');
}


if ($message=='Эй, бот'||$message=='/menu'||$data=="/start_menu") {
        
        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode("Главное меню.\n\xE2\x9B\x94 - неработающие или криво работающие функции ))");

        if ($data){
            file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
        }
        else 
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$user_id.'&text='.$text.'&reply_markup='.$replyMarkup);
    
   
   }

if ($message=='/race'||$data=='/race') {

    $inline_button1 = array("text"=>"Посмотреть список %E2%9B%94","callback_data"=>"/race_list");
    $inline_button2 = array("text"=>"Создать новый","callback_data"=>"/race_new");

    $inline_keyboard = [[$inline_button1],[$inline_button2]];

    $keyboard=array("inline_keyboard"=>$inline_keyboard);
    $replyMarkup = json_encode($keyboard); 
    $text = urlencode("Ты хочешь создать новый забег или посмотреть те, что есть?");
 
    if ($data){
        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$chat_id_in.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
    }
    else 
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text='.$text.'&reply_markup='.$replyMarkup);

}

if (($message[0]=='!'&&strlen($message)>5)||substr($data, 0, 8)=='/add_run'){
    include('kurovo_bot/add_run.php');
   
}

if ($message[0]=='?'||$message=='кому буст?'||$message=='Кому буст?'||$message=='/who_run'||$message=='/who_run@KurovoBot'||$data=='/who_run'){
    include('kurovo_bot/who_run.php');
}

if ($message[0]=='#'&&strlen($message)>5){
    include('kurovo_bot/add_run_text.php');
}

if ($message == 'Удалить'|| $message == 'удалить' || $message == 'delete'|| $message == 'Delete'|| $message == 'Del'|| $message == 'del'||$arr['callback_query']['data']=='/del_run'){
    
    if ($data=='/del_run') {

        $del = $mysqli->query("DELETE FROM `run` WHERE `user_id`=".$user_id); 

        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode("Все пробежки удалены!");

        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
    
    }

    else {
        // при новом формате списка бегущих не работает, удалить, если не будем к старому возвращаться
        $reply_to_message = $arr['message']['reply_to_message']['text']; 
        if (strpos($reply_to_message, '**')){
            $run = explode("**",$reply_to_message);

            $sel = $mysqli->query("SELECT * FROM `run` WHERE `run_id`=".$run[0]);
            $sel->data_seek(0);
            $res = $sel->fetch_assoc();

            if ($user_id==$res['user_id']){
                $upd = $mysqli->query("DELETE FROM `run` WHERE `run_id`=".$run[0]); 
                file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text=Удалено!');
            }
            else {file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text=Нельзя удалить чужую пробежку!'); }
        }
    }   
}  

if ($message == 'Дать буст'){
    
    // при новом формате списка бегущих не работает((( Переделать.
    $reply_to_message = $arr['message']['reply_to_message']['text']; 
    if (strpos($reply_to_message, '**')){

        $run = explode("**",$reply_to_message);
        $sel1 = $mysqli->query("SELECT `users`.`squad_id` FROM `users` JOIN `run` ON `users`.`user_id`=`run`.`user_id` WHERE `run`.`run_id`=".$run[0]);
        $sel1->data_seek(0);
        $res1 = $sel1->fetch_assoc();
        $recipient_squad_id = $res1['squad_id'];
        
        $sel = $mysqli->query("SELECT squad_id FROM users WHERE user_id=".$user_id);
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        $sender_squad_id = $res['squad_id'];

        if ($sender_squad_id==$recipient_squad_id) {
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text=Было бы удобно, да... Но сквад не разрешает дать буст самому себе.');
            exit('ok');
        }
        
        $sel = $mysqli->query("SELECT * FROM boosts WHERE sender_squad_id='".$sender_squad_id."'");
        if ($sel->num_rows==1){
            $sel->data_seek(0);
            $res = $sel->fetch_assoc();
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text=Твой буст созреет только '.$res['boost_death_time']);
            exit('ok');
        }
        
        $sel = $mysqli->query("SELECT * FROM boosts WHERE recipient_squad_id='".$recipient_squad_id."'");
        if ($sel->num_rows==3){
            $sel->data_seek(0);
            $res = $sel->fetch_assoc();
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text='.$res1['first_name'].' '.$res1['last_name'].' не может принять буст. Сейчас 30%');
            exit('ok');
        }
                
        include('kurovo_bot/send_boost.php');
        include('kurovo_bot/request.php');
        
        $sel = $mysqli->query("SELECT * FROM boosts WHERE sender_squad_id='".$user_id."' AND recipient_squad_id='".$recipient_squad_id."'");
        if ($sel->num_rows==1){
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text=Отлично! Буст отправлен!!! Но это не точно...');
        }
        else {
            file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text=Что-то пошло не так. Буст похоже не отправился...');
        }
    }
}  

if (substr($message, strpos($message, 'JWT'), strpos($message, 'JWT')+3)=='JWT'){
    $upd = $mysqli->query("UPDATE users SET jwt='".$message."' WHERE user_id=".$user_id);
}

if ($message == 'буст'||$message == 'Буст'||$message == '/my_boost'||$message == '/my_boost@KurovoBot'||$data=='/my_boost'){

    $sel = $mysqli->query("SELECT squad_id FROM users WHERE user_id=".$user_id);
    $sel->data_seek(0);
    $res = $sel->fetch_assoc();

    $sel = $mysqli->query("SELECT * FROM boosts WHERE sender_squad_id='".$res['squad_id']."'");
    if ($sel->num_rows==1){
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
       
        $date = date_parse($res['boost_death_time']);
        $min = $date['minute']<10 ? '0'.$date['minute'] : $date['minute'];
        $boost .= ($date['day'] != date("d")) ? "завтра в " : "сегодня в ";
        $boost .= $date['hour'].":".$min;

        $text = 'Буст созреет '.$boost;
    }
    else {
        $text = 'Твой буст готов!';
    }

    if ($data=='/my_boost') {

        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode($text);

        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
    
    }

    else {
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text='.$text);
    }
}

if ($message == '/rating'||$message == '/rating@KurovoBot'||$data=='/rating'){

    include('kurovo_bot/rating.php');

    if ($data=='/rating') {

        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode($text);

        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?parse_mode=HTML&chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
    
    }

    else {
     //   $text = urlencode($text);
       // file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$chat_id.'&text=Рейтинг'.$text);


        $url = 'https://api.telegram.org/bot'.$token.'/sendMessage';
        $query_array = array (
            'parse_mode' => 'HTML',
            'chat_id' => $chat_id,
            'text' => $text
        );
        $query = http_build_query($query_array);
        $result = file_get_contents($url . '?' . $query);
    }
}

if ($message=="/quiz"||$message=="/quiz@KurovoBot"||$arr['callback_query']['data']=='/quiz'){
       
    if ($arr['callback_query']['data']=='/quiz') {
        $chat_id = $arr['callback_query']['message']['chat']['id'];
    }

    $url = 'https://api.telegram.org/bot'.$token.'/sendMessage';

    $sel = $mysqli->query("SELECT * FROM `quiz`");
    $sel->data_seek(0);
    $res = $sel->fetch_assoc();
      
    if (date("Y-m-d", strtotime($res['quiz_date']))==date("Y-m-d")) {
        $answer = 'Сегодняшний ответ '.$res['answer'].', '.$res['answer_text'];
    }
    else {
        $answer2 = 'Я пока не знаю ответ, но сейчас посмотрю. Это займет некоторое время...';
        $query_array2 = array (
            'chat_id' => $chat_id,
            'text' => $answer2
        );
        $query2 = http_build_query($query_array2);
        $result2 = file_get_contents($url . '?' . $query2);

        include('kurovo_bot/quiz.php');

        $sel = $mysqli->query("SELECT * FROM `quiz`");
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
              
        if (date("Y-m-d", strtotime($res['quiz_date']))==date("Y-m-d")) {
            $answer = 'Сегодняшний ответ '.$res['answer'].', '.$res['answer_text'];
        }
        else {
            $answer = 'Я хз, пока что никто не ответил...';
        }
    }

    $query_array = array (
        'chat_id' => $chat_id,
        'text' => $answer
    );
    $query = http_build_query($query_array);
    $result = file_get_contents($url . '?' . $query);
 
}

if ($message=='Отправить ответ'||$data=='/quiz_answer'){
   // include ('kurovo_bot/quiz_answer.php');
}

if ($message=='/mission'||$message=='/mission@KurovoBot'||$data=='/mission'){
    
    $sel = $mysqli->query("SELECT *,TIMEDIFF(`end_date`, `start_date`) AS diff FROM `mission` WHERE (`start_date`<NOW() AND `end_date`>NOW())");
    if ($sel->num_rows!=0){
        $sel->data_seek(0);
        $res = $sel->fetch_assoc();
        $text = "Сегодняшняя миссия: \n".$res['name']."\n".$res['points']." points";

        if ($res['mode']!="solo"){
            $text .= "\nКомандная(mode: ".$res['mode'].")";
        }
        $start_date = date("U", strtotime($res['start_date']));
        $end_date = date("U", strtotime($res['end_date']));

        $diff = round(($end_date - $start_date)/(60*60*24));

        $date = date("Y-m-d");
        $end_date = date("Y-m-d", strtotime($res['end_date']));
    
        if ($diff>1&&$end_date!=$date) {
            $text .="\nНа ".$diff." дня, можно и завтра пробежать)) \nНа всякий случай, проверочка: до".strtotime($res['end_date'])." по Парижу.";
        }


    }

    else {
        $text = "Информация о миссии загружается в 4:00 по Мск. Ну, должна по крайней мере...))";
    }


    if ($data=='/mission') {
        $keyboard=array("inline_keyboard"=>$inline_keyboard);
        $replyMarkup = json_encode($keyboard); 
        $text = urlencode($text);
        file_get_contents('https://api.telegram.org/bot'.$token.'/editMessageText?parse_mode=HTML&chat_id='.$user_id.'&message_id='.$message_id.'&text='.$text.'&reply_markup='.$replyMarkup);
    }

    else {
        $text = urlencode($text);
        file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$user_id.'&text='.$text);
    }
}

if ($message=='Справка'||$message=='/help'||$message=='/help@KurovoBot')
{ 
  $text = "Привет, ".$first_name."! 
Этот бот умеет: 
    <b>''!19:00 Коммент''</b> - записать твой план пробежаться, где: 
! - важный символ, отличающий обращение к боту от обычного сообщения,
19:00 - предполагаемое время пробежки. Если время меньше текущего, то план запишется на завтра,
Коммент - Любой текст по желанию.
    <b>?</b>,   <b>Кто бежит?</b>,   <b>Кому буст?</b> - вывести список планирующих побегать одноклубников, у которых количество бустов меньше 30%.
    <b>??</b> - вывести список всех планирующих побегать, независимо от количества бустов.
    Ответив на сообщение бота о своей пробежке словами <b>'Удалить'</b> или <b>'Delete'</b>, можно отменить свой план.
    <b>Буст</b> - получить информацию о своем бусте.
    ";
               
$text2 = urlencode($text);
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$user_id.'&text='.$text2);
}



exit('ok'); 

?>