<?php
//Kurovo Running Club
$group_id = 'group';
//KurovoBotWorkGroup
$workgroup_id = 'workgroup'; 

$token = 'TOKEN';

$mysqli = new mysqli("сервер", "логин", "пароль", "БД");

if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$workgroup_id.'&text=Не удалось подключиться к MySQL');
    exit();
}

$sel = $mysqli->query("SELECT `JWT` FROM `JWT`");
    $sel->data_seek(0);
    $res = $sel->fetch_assoc();
    $jwt = $res['JWT'];

if (!$mysqli->set_charset("utf8")) { 
    printf("Ошибка при загрузке набора символов utf8: %s\n", $mysqli->error); 
    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?parse_mode=HTML&chat_id='.$workgroup_id.'&text=Ошибка при загрузке набора символов utf8');
    exit(); 
}
// header("Content-Type: text/html; charset=utf-8");

?>