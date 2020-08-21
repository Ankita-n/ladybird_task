<?php
header("Access-Control-Allow-Origin: http://localhost/ladybird_assessment/");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

$response = [];
if($_SERVER["REQUEST_METHOD"] != "POST"){
    $response = msg(0,404,'Page Not Found!'); 
}elseif(!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['password']) || empty(trim($_POST['name']))|| empty(trim($_POST['email']))|| empty(trim($_POST['password']))){
    $fields = ['fields' => ['name','email','password']];
    $response = msg(0,422,'Please Fill in all Required Fields!',$fields);
}else{
include_once 'Db_connection.php';
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    
    $database = new Db_connection();
    $db = $database->open_connection();

    $user_query = "select name, email from users where email='$email'";
    $user = $db->query($user_query);
    $result = $user->fetch_assoc();
  
    if (empty($result)) {
        $insert_user = "insert into users (name,email,password) values('$name','$email','$password')";
        $db->query($insert_user);
        $database->close_connection();
        $response = msg(1,201,"You have registered successfully.");
    } else {
        $response = msg(0,422,"Email already in use.");
    }
}
    echo json_encode($response);

?>