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
}elseif(!isset($_POST['book_id']) || !isset($_POST['sheet_id']) || !isset($_POST['cell_index']) || empty(trim($_POST['book_id']))|| empty(trim($_POST['sheet_id'])) || empty(trim($_POST['cell_index']))){
    $fields = ['fields' => ['book_id','sheet_id','cell_index']];
    $response = msg(0,422,'Please Fill in all Required Fields!',$fields);
}else{
    include_once 'Db_connection.php';
    
    $book_id = trim($_POST['book_id']);
    $sheet_id = trim($_POST['sheet_id']);
    $cell_index = trim($_POST['cell_index']);
    
    $database = new Db_connection();
    $db = $database->open_connection();
    
    $sql = "select book_id,sheet_id,cell_index,cell_value from cells where book_id = '$book_id' and sheet_id= '$sheet_id' and cell_index in($cell_index)";
    $cell_data = $db->query($sql);
    //print_r($cell_data);
   // $result = $cell_data->fetch_assoc();
   
   // $database->close_connection();
    if(!empty($cell_data)){
        $cell_info['data'] = [];
        while($cell_result = mysqli_fetch_assoc($cell_data)){
        $cell_info['data'][] = array('book_id'=>$cell_result['book_id'],
                        'sheet_id'=>$cell_result['sheet_id'],
                        'cell_index'=>$cell_result['cell_index'],
                        'cell_value'=>$cell_result['cell_value']);
        }
        $database->close_connection();
        $response = msg(1,201,"Get cell data successfully.",$cell_info);

    }else{
        $response = msg(1,422,"No data available!");
    } 
}
echo json_encode($response);  
?>