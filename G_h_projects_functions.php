<?php
declare (strict_types=1);
namespace G_H_PROJECTS_INCLUDE;
require_once __DIR__ . '/vendor/autoload.php';
// define ('getDefinedVars', <<<'defined'
//     $allVars = get_defined_vars();
//     $myVars = [];
//     foreach($allVars as $key=>$var){
//       if(strpos($key, '_') !== 0){
//         $myVars [$key] = $var;
//       }
//     }
//     echo "<pre>";
//     print_r($myVars);
//     echo"</pre>";
// defined);
$output=array();
use PDO;
use PDOException;
use ReflectionClass;
use Reflection;
use style;
use App\Node;

class G_h_projects_functions{
    public static \mysqli $conn;
    public static function pretty_var_dump($x){
        echo "<pre style='font-size:18px'>\n";
        var_dump($x);
        echo "</pre>";
    }
    public static function setPlayList(){
        // include "D:/125_xampp/htdocs/include/globals.php";
        // global $conn;
        $url = 'https://www.youtube.com/watch?v=sVbEyFZKgqk&list=PLr3d3QYzkw2xabQRUpcZ_IBk9W50M9pe-&index=1';
        $playList=static::getPlayList($url);
        $keys=array_keys($playList);
        static::connectToMysql("php_tutorial");
        foreach($keys as $row){
            $value="https://www.youtube.com/watch?v=" . $playList[$row] . "sVbEyFZKgqk&list=PLr3d3QYzkw2xabQRUpcZ_IBk9W50M9pe-&index=" . $row;
            $sql = "UPDATE playList SET link = '" . $value . "' WHERE `index`='" . $row ."'";
            $result = static::$conn->query($sql);
        }    
    }
    public static function getPlayList(string $url):array{
        $str = file_get_contents($url);
        $playList = [];
        $offset=0;
        while(preg_match('/(?<="url":"\/watch\?v=).*?(?=\\\u0026)/',$str,$matches,PREG_OFFSET_CAPTURE,$offset)){
            $offset = $matches[0][1]+1;
            if(strlen($matches[0][0])>11){
                continue;
            }
            preg_match('/(?<=index=)\d+/',$str,$indexMatches,offset:$offset);
            
            if (array_search($matches[0][0],$playList,true)===false){
                $playList[$indexMatches[0]]=$matches[0][0];
            }
        }
        ksort($playList);
        return $playList;
    }
    public static function adjustableText():string{
        return '<textarea style="resize: both; overflow: auto; width: 200px; height: 100px;" placeholder="Type here"></textarea>';
    }
    public static function script($script){
        echo "<script>\n" . $script . "\n</script>";
    }
    public static function connectToMysqlPdo(){
        global $pdo;
        global $dbname;
        global $username;
        global $password;
        $servername = "localhost";
        $username = "id18285095_root";
        $password = "e)%Cejdq3+a5)=i&";
        $dbname = "id18285095_loadtabledb";
        $servername = "localhost";
        $username = "root";
        $password = "root";
        $dbname = "loadTableDB";    
        $dsn = "mysql:host=localhost;dbname=$dbname";
        try {
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            return ("Connection failed: " . $e->getMessage() . "dsn: $dsn, username:$username, password:$password");
        }
    }
    // function createDatabaseDocumentTable($title){
    // }
    public static function createDB($DBName){
        // include "globals.php";
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$DBName'";
        $result = mysqli_query(static::$conn, $query);
        if (!$result) {
            die('Error checking if the database exists: ' . mysqli_error(static::$conn));
        }
        if (mysqli_num_rows($result) == 0) {
            // Create the database
            $sql = "CREATE DATABASE $DBName";
            $result = mysqli_query(static::$conn, $sql);
            if (!$result) {
                die('Error creating database: ' . mysqli_error(static::$conn));
            } else {
                echo 'Database created successfully.';
            }
        } else {
            echo 'Database already exists.';
        }
    }
    public static function createTableIfDoesntExist($tableName,$str){
        // include "globals.php";
        $sql = "CREATE TABLE IF NOT EXISTS $tableName ($str);";
        $result = static::$conn->query($sql);
        if ($result === TRUE) {
            // echo "Table created successfully";
        } else {
            echo "Error creating table: " . static::$conn->error;
        }
    }
    public static function dropTable($tableName){
        // include "globals.php";
        $sql = "drop TABLE IF EXISTS $tableName;";
        $result = static::$conn->query($sql);
    }
    public static function createMainDatabaseDocumentTableIfItDoesntExist(){
        global $dbname;
        global $username;
        global $password;
        global $databaseDocument;
        $dsn = "mysql:host=localhost;dbname=$dbname;charset=utf8mb4"; // ▼ check if databaseDocument table exists ▼
        $pdo = new PDO($dsn, $username, $password);
        $databaseDocument = "databaseDocument";
        $sql = "SHOW TABLES LIKE :table_name;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':table_name', $databaseDocument);
        if (!$stmt->execute()){ //                                             ▼ 
            $error = $stmt->errorInfo();
            echo "Error executing query: {$error[2]} ({$error[0]})";
        }else{
            // echo "good pdo query";
        };
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row){}else{ //                         ▼ create databaseDocument if doesnt exist ▼
            $sql= "CREATE TABLE $databaseDocument (
                title VARCHAR(50) NOT NULL ,
                documentee VARCHAR(50) NOT NULL ,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (title, documentee),
                CHECK (title <> ''),
                CHECK (documentee <> ''))";
            $stmt = $pdo->prepare($sql);
            if (!$stmt->execute()){}
        }
    }
    public static function addPropertyToElement($element, $property) {
        $pos = strpos($element, '>');
        if ($pos !== false) {
            $element = substr_replace($element, ' ' . $property, $pos, 0);
        }
        return $element;
    }
    public static function addOptionToSelect($selectHtml, $optionHtml) {
        $newSelectHtml = preg_replace('/<select([^>]*)>/', "<select$1>" . $optionHtml, $selectHtml, 1);
        return $newSelectHtml;
    }    
    public static function createSelectFromSql($sql){
        $str="<select>";
        // global $conn;
        $result=mysqli_query(static::$conn,$sql);
        if($result){
            while($row=mysqli_fetch_row($result)){
                $str .= "<option value='" . $row[0] . "'>" .$row[0] . "</option>\n";
            }
        }
        $str .= "</select>";
        return $str;
    }   
    public static function connectToMysql($DBName){
        // global $dbname;
        global $username;
        global $password;
        $servername = "localhost";
        $username = "id18285095_root";
        $password = "e)%Cejdq3+a5)=i&";
        // $dbname = "id18285095_loadtabledb";
        $servername = "localhost";
        $username = "root";
        $password = "root";
        // $dbname = "loadTableDB";
        $servername = "host.docker.internal";
        // global $conn;
        static::$conn = mysqli_connect($servername, $username, $password, $DBName);
        // $this->conn = mysqli_connect($servername, $username, $password, $DBName);
        if (!static::$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }   
    } 
    public static function doAlert($str){
        ?>
        <script>
            alert("<?php echo $str;?>");
        </script>
        <?php
    }
    public static function doAlertEscape($str){
        $str = json_encode ($str);
        $str = substr($str,1,-1);
        static::doAlert($str);
    }
    public static function step($str){
        global $output;
        static $step=0;
        $output["step_" . ++$step]=$str;
    }
    private ?G_h_projects_functions $instance;
    public static function printClassInfo(string $className,bool $onlySelfDeclared = true,bool $printFullyQualifiedName = false) {
        // echo '<pre>';
        $reflectionClass = new ReflectionClass($className);
        echo "Class: ";
        if ($printFullyQualifiedName){
            echo $className . "\n";
        }else{
            $parts = explode('\\', $className);
            $baseName = end($parts);
            echo $baseName  . "\n";
        }
        $properties = $reflectionClass->getProperties();
        echo "Properties:\n";
        $selfDeclaredProperties = array_filter($properties, function ($property) use ($className) {
            return $property->getDeclaringClass()->getName() === $className;
        });
        if ($onlySelfDeclared){
            foreach ($selfDeclaredProperties as $property) {
                echo "\t" . implode(' ', Reflection::getModifierNames($property->getModifiers())) . ' ' . $property->getName() . "\n";
            }
        }else{
            foreach ($properties as $property) {
                echo "\t" . implode(' ', Reflection::getModifierNames($property->getModifiers())) . ' ' . $property->getName() . "\n";
            }
        }
        $methods = $reflectionClass->getMethods();
        $selfDeclaredMethods = array_filter($methods, function ($method) use ($className) {
            return $method->getDeclaringClass()->getName() === $className;
        });
        echo "Methods:\n";
        if ($onlySelfDeclared){
            foreach ($selfDeclaredMethods as $method) {
                echo "\t" . implode(' ', Reflection::getModifierNames($method->getModifiers())) . ' ' . $method->getName() . "\n";
            }    
        }else{
            foreach ($methods as $method) {
                echo "\t" . implode(' ', Reflection::getModifierNames($method->getModifiers())) . ' ' . $method->getName() . "\n";
            }
        }
        // echo '</pre>';
    }
    public static function getClassInfo(string $class,bool $onlySelfDeclared = true,bool $printFullyQualifiedName = false):array {
        $reflectionClass = new ReflectionClass($class);
        $result = [];
        $str = 'Class: ';
        if ($printFullyQualifiedName){
            $str .= $class;
        }else{
            $parts = explode('\\', $class);
            $baseName = end($parts);
            $str .= $baseName;
        }
        $result[]= $str; 
        $properties = $reflectionClass->getProperties();
        // $result[] = 'Properties:';
        $selfDeclaredProperties = array_filter($properties, function ($property) use ($class) {
            return $property->getDeclaringClass()->getName() === $class;
        });
        $children = [];
        if ($onlySelfDeclared){
            foreach($selfDeclaredProperties as $property){
                // todo: $result []= implode(' ', Reflection::getModifierNames($property->getModifiers())) . ' ' . $property->getName() . "\n";
                // $result[]=$selfDeclaredProperties;
                $str = implode(' ', Reflection::getModifierNames($property->getModifiers())) . ' ' . $property->getName();
                $children[]= $str;
            }
        }else{
            foreach($properties as $property){
                // todo: $result []= implode(' ', Reflection::getModifierNames($property->getModifiers())) . ' ' . $property->getName() . "\n";
                // $result[]=$selfDeclaredProperties;
                $str = implode(' ', Reflection::getModifierNames($property->getModifiers())) . ' ' . $property->getName();
                $children[]= $str;
            }
        }
        $result ['properties']= $children;
        $methods = $reflectionClass->getMethods();
        $selfDeclaredMethods = array_filter($methods, function ($method) use ($class) {
            return $method->getDeclaringClass()->getName() === $class;
        });
        // $result[]='Methods:';
        $children = [];
        if ($onlySelfDeclared){
            foreach ($selfDeclaredMethods as $method) {
                $str = implode(' ', Reflection::getModifierNames($method->getModifiers())) . ' ' . $method->getName();
                $children[]= $str;
            }    
        }else{
            foreach ($methods as $method) {
                $str = implode(' ', Reflection::getModifierNames($method->getModifiers())) . ' ' . $method->getName();
                $children[]= $str;
            }
        }
        $result ['methods']= $children;
        return $result;
    }
    public function echo(){
        ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script> 
        function postFormData(url, data) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            document.body.appendChild(form);
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = data[key];
                    form.appendChild(input);
                }
            }
            form.submit();
        } 
        function equalizeWidth(id_of_elemnt_to_change, id_of_element_to_change_to){
            // alert("equalize id: "+id_of_elemnt_to_change + " id:" +id_of_element_to_change_to);
            var element_1 = document.getElementById(id_of_elemnt_to_change);
            var jquery_selector = "#" + id_of_element_to_change_to;
            var paddingAndBorderWidth = $(jquery_selector).outerWidth(true) - $(jquery_selector).width();
            element_1.style.width = ($(jquery_selector).width() + paddingAndBorderWidth) + 'px';
        }
        </script>
    <?php }
    public static function objectToArray($x):array{
        $result = json_encode($x);
        $result = json_decode($result,true);
        return $result;
    }
}


