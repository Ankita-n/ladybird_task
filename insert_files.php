<?php
//use Phppot\DataSource;
//use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
include_once 'vendor/autoload.php';
function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

$response = [];
$error = [];
$success = [];
if($_SERVER["REQUEST_METHOD"] != "POST"){
    $response = msg(0,404,'Page Not Found!'); 
}elseif(!isset($_FILES['import_files']['name'])){
    $fields = ['fields' => ['import_files']];
    $response = msg(0,422,'Please Fill in all Required Fields!',$fields);
}else{
    include_once 'Db_connection.php';

    $database = new Db_connection();
    $db = $database->open_connection();
   
    $allowedFileType = [
        'application/octet-stream',
        'application/vnd.ms-excel',
        'text/xlsx'
    ];
    foreach($_FILES['import_files']['tmp_name'] as $key => $value){
    if (in_array($_FILES["import_files"]["type"][$key], $allowedFileType)) {
        $file_new_name = time().'_'.$_FILES['import_files']['name'][$key];
        $path = 'uploads/' . $file_new_name;
        move_uploaded_file($_FILES['import_files']['tmp_name'][$key], $path);

        if (file_exists($path)) {
        $insert_book = "insert into books (name) values('$file_new_name')";
            if ($db->query($insert_book) === TRUE) {
                $book_id = $db->insert_id;

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $reader->setReadDataOnly(TRUE);
                $spreadsheet = $reader->load($path);
                $all_sheet = $reader->listWorksheetNames($path);

                foreach ($all_sheet as $worksheet_name) {
                    $insert_sheet = "insert into sheets (book_id,name) values('$book_id','$worksheet_name')";
                    if ($db->query($insert_sheet) === TRUE) {
                        $sheet_id = $db->insert_id;
                        $worksheet = $spreadsheet->getSheetByName($worksheet_name);
                        $highestRow = $worksheet->getHighestRow(); // e.g. 10
                        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
                
                        for ($row = 1; $row <= $highestRow; ++$row) {
                            for ($col = 'A'; $col <= $highestColumn; ++$col) {
                                $cell_index = $col.$row;
                                $cell_value = $worksheet->getCell($col.$row)->getValue();
                                if(!empty($cell_value)){
                                    $insert_cell = "insert into cells (book_id,sheet_id,cell_index,cell_value) values('$book_id','$sheet_id','$cell_index','$cell_value')";
                                    $db->query($insert_cell);
                                }
                            
                            }
                        }
                    }
                }
            }
                $success[] = $_FILES['import_files']['name'][$key].' file uploaded successfully';
        }else{
            $error[] = $_FILES['import_files']['name'][$key].' file not uploaded';
        } 
    }else{
        $error[] = $_FILES['import_files']['name'][$key].' file type not valid';
    }
}
    
    if(!empty($error)){
        $message_arr['error_desc'] = array_merge($success,$error);
        $response = msg(0,422,"Something wrong",$message_arr);
    }else{
        $response = msg(1,202,"file uploaded successfully");
    }
}
echo json_encode($response);  
?>