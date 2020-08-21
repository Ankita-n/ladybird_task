<?php
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
}elseif(!isset($_POST['email']) || !isset($_POST['password']) || empty(trim($_POST['email']))|| empty(trim($_POST['password']))){
    $fields = ['fields' => ['email','password']];
    $response = msg(0,422,'Please Fill in all Required Fields!',$fields);
}else{
    include_once 'Db_connection.php';
    
    $email = trim($_POST['email']);
    $password = md5(trim($_POST['password']));
    
    $database = new Db_connection();
    $db = $database->open_connection();
    
    $sql = "select * from users where email = '$email' and password= '$password'";
    $user = $db->query($sql);
    $result = $user->fetch_assoc();
    $database->close_connection();
    if(!empty($user)){
        $users['data'] = array('user_id'=>$result['user_id'],
                        'name'=>$result['name'],
                        'email'=>$result['email'] );
        $response = msg(1,201,"You have successfully logged in.",$users);

    }else{
        $response = msg(1,422,"Invalid Email or Password!");
    } 
}
echo json_encode($response);  
?>