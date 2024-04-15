<?php
declare(strict_types=1);
namespace G_H_PROJECTS_INCLUDE;
require_once __DIR__ . '/../vendor/autoload.php';
use G_H_PROJECTS_INCLUDE\G_h_projects_functions;
class DoSql{
    public static function doSql(string $sql){
        G_h_projects_functions::connectToMysql("php_tutorial");
        $result = G_h_projects_functions::$conn->query($sql);
        if ($result) {
            http_response_code(200); // Success response code
        } else {
            http_response_code(500); // Error response code
        }
        if (!is_bool($result)){
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }
        G_h_projects_functions::$conn->close();
        if (!isset($rows)) $rows = [];
        echo json_encode($rows);
    }
}


