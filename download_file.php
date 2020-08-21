<?php

function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

$response = [];
if($_SERVER["REQUEST_METHOD"] != "GET"){
    $response = msg(0,404,'Page Not Found!'); 
}elseif(!isset($_GET['book_id']) || empty(trim($_GET['book_id']))){
    $fields = ['fields' => ['book_id']];
    $response = msg(0,422,'Please Fill in all Required Field!',$fields);
}else{
    include_once 'Db_connection.php';
    
    $book_id = trim($_GET['book_id']);
    
    $database = new Db_connection();
    $db = $database->open_connection();
    
    $sql = "select book_id,name from books where book_id = '$book_id'";
    $book_data = $db->query($sql);
    $result = $book_data->fetch_assoc();
    $database->close_connection();
    $filepath  = 'uploads/' . $result['name'];
        if(file_exists($filepath)) {
            $response = msg(1,201,"File download successfully.");
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($filepath));
           header('Cache-Control: must-revalidate');
           header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            echo readfile($filepath);
            //$response = msg(1,201,"File download successfully.");
        } else {
            $response = msg(1,422,"File not exist in folder!");
        }
}
echo json_encode($response);  
?>