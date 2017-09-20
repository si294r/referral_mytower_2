<?php

defined('IS_DEVELOPMENT') OR exit('No direct script access allowed');

$swrve_user_id = isset($params[1]) ? $params[1] : "";

if (trim($swrve_user_id) == "") {
    return array(
        "status" => FALSE,
        "message" => "Error: swrve_user_id is empty"
    );
}

$key = "mytower2_" . $swrve_user_id;
$array = apcu_fetch($key);

if ($array === FALSE) {
    
    include("/var/www/mysql-config2.php");
    $connection = new PDO(
        "mysql:dbname=mytower2;host=$myhost;port=$myport",
        $myuser, $mypass
    );
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // create shorten_id if not exists
    $sql1 = "INSERT INTO referral (swrve_user_id)
        SELECT * FROM (SELECT :user_id1) t WHERE NOT EXISTS (
            SELECT 1 FROM referral WHERE swrve_user_id = :user_id2
        )";
    $statement1 = $connection->prepare($sql1);
    $statement1->bindParam(":user_id1", $swrve_user_id);
    $statement1->bindParam(":user_id2", $swrve_user_id);
    $statement1->execute();

    // get shorten_id
    $sql2 = "SELECT shorten_id, swrve_user_id FROM referral WHERE swrve_user_id = :user_id";
    $statement2 = $connection->prepare($sql2);
    $statement2->execute(array(':user_id' => $swrve_user_id));
    $row = $statement2->fetch(PDO::FETCH_ASSOC);

    $array['shorten_id'] = $row['shorten_id'];
    $array['swrve_user_id'] = $row['swrve_user_id'];
    apcu_store($key, $array);
}

return array(
    'shorten_id' => $array['shorten_id'],
    'swrve_user_id' => $array['swrve_user_id'],
    'shorten_url_1' => "http://2.mytower.xyz/".base_convert((int)"{$array['shorten_id']}1" + 100000, 10, 32),
    'shorten_url_2' => "http://2.mytower.xyz/".base_convert((int)"{$array['shorten_id']}2" + 100000, 10, 32),
    'shorten_url_3' => "http://2.mytower.xyz/".base_convert((int)"{$array['shorten_id']}3" + 100000, 10, 32),
    'error' => 0,
    'message' => 'Success'
);