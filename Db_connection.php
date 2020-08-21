<?php
class Db_connection
{

    protected $servername = "localhost";

    protected $username = "root";

    protected $password = "";

    protected $dbname = "ladybird";

    protected $con;

    /* Function for opening connection */
    public function open_connection()
    {
        try {
            $this->con = new mysqli($this->servername, $this->username, $this->password,$this->dbname);
            return $this->con;
        } catch (Exception $e) {
            echo "There is some problem in database connection: " . $e->getMessage();
        }
    }

    /* Function for closing connection */
    public function close_connection()
    {
        $this->con = $this->con->close();
    }
}
?>