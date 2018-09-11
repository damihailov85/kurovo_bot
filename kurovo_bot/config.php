<?php
$mysqli = new mysqli("26.176.40.68", "login", "f0CsI34h3u", "base_name");
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$token = '181232150:AAEXCcf0CsI34h3u-VZmVy_0f0CsI34h3u';

$group_id = '-123417667';

if (!$mysqli->set_charset("utf8")) { printf("Ошибка при загрузке набора символов utf8: %s\n", $mysqli->error); exit(); }
 header("Content-Type: text/html; charset=utf-8");
?>