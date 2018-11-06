<?php



$inline_button1 = array("text"=>"Кому буст?","callback_data"=>"/who_run");
$inline_button2 = array("text"=>"Мой буст","callback_data"=>"/my_boost");
$inline_button3 = array("text"=>"Quizz%E2%9B%94","callback_data"=>"/quiz");
$inline_button4 = array("text"=>"Рейтинг","callback_data"=>"/rating");
$inline_button45 = array("text"=>"Миссия","callback_data"=>"/mission");


$sel = $mysqli->query("SELECT * FROM `run` WHERE `user_id`=".$user_id);
if ($sel->num_rows==1) {
    $inline_button5 = array("text"=>"Моя пробежка","callback_data"=>"/add_run");
}
else {
    $inline_button5 = array("text"=>"Хочу побегать","callback_data"=>"/add_run");
}

$inline_button6 = array("text"=>"Забеги%E2%9B%94","callback_data"=>"/race");


/*
$sel = $mysqli->query("SELECT * FROM `users` WHERE `user_id`=".$user_id);
$sel->data_seek(0);
$res = $sel->fetch_assoc();
if ($res['jwt']!='0') {

    $inline_button7 = array("text"=>"Отправить ответ","callback_data"=>"/quiz_answer");
    $inline_button8 = array("text"=>"Дать буст%E2%9B%94","callback_data"=>"/send_boost");

    $inline_keyboard = [
        [$inline_button1, $inline_button2],
        [$inline_button3, $inline_button4],
        [$inline_button5],
        [$inline_button6],
        [$inline_button7],
        [$inline_button8]
    ];

}

else 
    */
    $inline_keyboard = [
        [$inline_button1, $inline_button2],
        [$inline_button3, $inline_button4],
        [$inline_button45],
        [$inline_button5],
        [$inline_button6]
    ];



?>