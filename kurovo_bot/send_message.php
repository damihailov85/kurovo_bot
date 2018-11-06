<?php

function sendMessage($method, $chat_id, $message, $replyMarkup) {

    

    $json = file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$user_id.'&text='.$message.'&reply_markup='.$replyMarkup);
  }



?>