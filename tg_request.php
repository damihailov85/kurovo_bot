<?

$token_kurovo = 'TOKEN1';
$token_my = 'TOKEN2';
$token_kurovo_test = 'TOKEN3';


$sFile = file_get_contents("https://api.telegram.org/bot".$token_kurovo."/getWebhookInfo"); 

//$sFile = file_get_contents("https://api.telegram.org/bot".$token_kurovo."/setWebhook?url=https://damihailov85.website/kurovo_bot.php");

//$sFile = file_get_contents("https://api.telegram.org/bot".$token_kurovo."/setWebhook?certificate=1.pem");


echo $sFile;


?>
