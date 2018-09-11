<?
// для сервера - /usr/local/bin/php /home/damihail/public_html/kurovo_bot/info.php
// проверял, получится ли сервером по расписанию рассылку делать. получится.
file_get_contents('https://api.telegram.org/bot591789822:AAHMK__6zVhylmZ7cw493m0kATfzIe5e0GE/sendMessage?chat_id=-229912167&text=Запущеный по расписанию(каждую минуту) файл info.php!');

?>