<?php 
/**
 * Expected input:
 *  action: "transaction"
 *  sqlTransactionArray:{sql,variables} sql-string, variables-array
 *  commit:true/false
 */
if (true){
    $input = json_decode(file_get_contents('php://input'),true);
    include_once '../include/g_h_projects_functions.php';
    connectToMysqlPdo();
    ob_clean();
    try{
        $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        $pdo->beginTransaction();
        $output= array();
        if((isset($input["action"])) && ($input["action"]=="transaction") && (isset($input["sqlTransactionArray"]))){
            $sqlTransactionArray = $input["sqlTransactionArray"];
            $output["originalCommit"]=$input["commit"];
            try {
                $commit=true;
                $arrayOfResults=array();
                foreach($sqlTransactionArray as $sqlTransaction){
                    $sql=$sqlTransaction["sql"];
                    $arrayOfResults["sql"]=$sql;
                    $stmt = $pdo->prepare($sql);
                    if(isset($sqlTransaction["variables"])){
                        $variables=$sqlTransaction["variables"];
                        $arrayOfResults["variables"]=$variables;
                        if($variables){
                            foreach ($variables as $key => $value) {
                                $stmt->bindValue(':' . $key, $value);
                            }
                        }
                    }
                    try{ 
                        $isInTransaction= $pdo->inTransaction();
                        $result = $stmt->execute();
                        $isInTransaction= $pdo->inTransaction();
                        $arrayOfResults["executeResult"]= $result;
                        $arrayOfResults["executeRowCount"]= $stmt->rowCount();
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $arrayOfResults["fetchAllResult"] = $result;
                        $output[]=$arrayOfResults;
                    }catch (PDOException $e){
                        $output[]=["error"=>$e->getMessage(),"data"=>$arrayOfResults];
                        $commit=false;
                    }
                }
                if ($commit){
                    if(count($sqlTransactionArray)>0){
                        $output["commitable"]=true;
                    }else{
                        $output["commitable"]=false;
                        $output["empty"]=true;
                    }
                    if($input["commit"]) {
                        $pdo->commit();
                    }else{
                        $pdo->rollback();
                    }
                }else{
                    $output["commitable"]=false;
                    $pdo->rollback();
                }
                try{
                    // $pdo->rollback();
                }catch(Exception $e){
                }
                ob_clean();
                $response = json_encode($output);
                echo $response;
            }catch(PDOException $e) {
                ob_clean();
                $pdo->rollback();
                $output["error"]= $e->getMessage();
                $response = json_encode($output);
                echo $response;
            }
        }else{
            $pdo->rollback();
        }
    }catch (PDOException $e){
        // $output["rollback"]++;
        try{
            $pdo->rollback();
        }
        catch (Exception $e) {
            
        };
        $json = ob_get_clean();
        $data = array(
            "error" => $e->getMessage(),
            "echo" => json_encode($json)
        );
        echo json_encode($data);
        exit();
    } 
}
function afu(){
    // Create a backup of the database
    $dbname = 'my_database';
    $backupFile = '/path/to/backup.sql';

    $sql = "SET FOREIGN_KEY_CHECKS=0;\n";
    $sql .= file_get_contents($backupFile); // Load SQL dump from file
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

    file_put_contents($backupFile, $sql);


    $dbname = 'my_database';
    $backupFile = '/path/to/backup.sql';

    $sql = file_get_contents($backupFile); // Load SQL dump from file
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

}
?>