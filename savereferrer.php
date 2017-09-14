<?php

defined('IS_DEVELOPMENT') OR exit('No direct script access allowed');

$json = json_decode($input);

$data['swrve_user_id'] = isset($json->swrve_user_id) ? $json->swrve_user_id : "";
$data['referrer'] = isset($json->referrer) ? $json->referrer : "";

if (trim($data['swrve_user_id']) == "") {
    return array(
        "status" => FALSE,
        "message" => "Error: swrve_user_id is empty"
    );
}
if (trim($data['referrer']) == "") {
    return array(
        "status" => FALSE,
        "message" => "Error: referrer is empty"
    );
}

include("/var/www/mysql-config2.php");
$connection = new PDO(
    "mysql:dbname=mytower2;host=$myhost;port=$myport",
    $myuser, $mypass
);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// create record if not exists
$sql1 = "INSERT INTO referral (swrve_user_id)
    SELECT * FROM (SELECT :user_id1) t WHERE NOT EXISTS (
        SELECT 1 FROM referral WHERE swrve_user_id = :user_id2
    )";
$statement1 = $connection->prepare($sql1);
$statement1->bindParam(":user_id1", $data['swrve_user_id']);
$statement1->bindParam(":user_id2", $data['swrve_user_id']);
$statement1->execute();

// save referrer
$sql2 = "UPDATE referral "
        . "SET referrer = :referrer "
        . "WHERE swrve_user_id = :user_id "
        . "and (referrer is null or referrer <> :referrer1) ";
$statement2 = $connection->prepare($sql2);
$statement2->bindParam(":referrer", $data['referrer']);
$statement2->bindParam(":user_id", $data['swrve_user_id']);
$statement2->bindParam(":referrer1", $data['referrer']);
$statement2->execute();

$data['affected_row'] = $statement2->rowCount();
$data['error'] = 0;
$data['message'] = 'Success';

return $data;