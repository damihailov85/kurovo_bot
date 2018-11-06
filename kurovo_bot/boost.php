<?
include('config.php');

$sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['squad_id']."'");
    if($sel2->num_rows==0) $boosts = 0; 
    else $boosts = $sel2->num_rows*10;

$sel2 = $mysqli->query("SELECT * FROM `boosts` WHERE `recipient_squad_id`='".$res['squad_id']."' AND `boost_death_time`>".$res['date']);
    if($sel2->num_rows==0) $boosts2 = 0; 
    else $boosts2 = $sel2->num_rows*10;

    if ($boosts2 == 30&&substr($message,0,2)!='??')
        continue; 

$boost_text = "сейчас: ".$boosts."%; когда побежит будет: ".$boosts2."%";

echo $boost_text;

?>