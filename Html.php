<?php
namespace G_H_PROJECTS_INCLUDE;
use PDO;
use PDOException;
use App\UnsortedListNode;
use App\TransferedJSObject;
use G_H_PROJECTS_INCLUDE\JSNode;
use App\Enums\JSNodeFormat;
class Html{
    public static ?Html $instance = null;
    public int $indent;
    public string $arrayElementSeparator = ', ';
    public array $indentSequences = ['{', '(', '[', '<script', '<head>', '<select'];
    public array $unIndentSequences = ['}', ')', ']', '</script>', '<\/script>', '</head>', '</select>'];
    public string $trueBoolString = 'true';
    public string $falseBoolString = 'false';
    public function __construct(private ?PDO $pdo=null)
    {
        $this->indent = 4;
    }
    public static function getInstance(?PDO $pdo = null){
        if(!static::$instance){
            static::$instance = new Html($pdo);
        }
        return static::$instance;
    }
    public function createHtmlTableFromSql(string $sql){
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                return "<p>No data found.</p>";
            }
            $htmlTable = '<table border="1"><thead><tr>';
            foreach ($rows[0] as $key => $value) {
                $htmlTable .= "<th>$key</th>";
            }
            $htmlTable .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                $htmlTable .= '<tr>';
                foreach ($row as $value) {
                    $htmlTable .= "<td>$value</td>";
                }
                $htmlTable .= '</tr>';
            }
            $htmlTable .= '</tbody></table>';
            echo $htmlTable;
            return $htmlTable;
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
        echo "Table";
    }
    public static function generateTreeFromStrictArray(array $data):string {
        $string = '';
        $string .= '<ul>';
        foreach ($data as $node) {
            $string .= '<li>' . $node['name'];
            if (isset($node['children']) && !empty($node['children'])) {
                $string .= static::generateTreeFromStrictArray($node['children']);
            }
            $string .= '</li>';
        }
        $string .= '</ul>';
        return $string;
    }
    public static function generateTreeFromArrayOfNodes(array $data): string {
        $string = '';
        $string .= '<ul>';
        foreach ($data as $node) {
            $string .= '<li>' . $node->name;
            if (isset($node->children) && !empty($node->children)) {
                $string .= static::generateTreeFromArrayOfNodes($node->children);
            }
            $string .= '</li>';
        }
        $string .= '</ul>';
        return $string;
    }
    public static function generateTreeFromArrayOfNodesOrStrictArray(array $data): string {
        $strictArray = is_array($data[0]) ? true : false;
        $string = '';
        $string .= '<ul>';
        foreach ($data as $node) {
            $children = $strictArray ? (isset($node['children'])?$node['children']:null) : $node->children;
            $string .= '<li>';
            if ($strictArray){
                $string .= $node['name'];
            }else{
                $string .= $node->name;
            } 
            if (isset($children) && !empty($children)){
                $string .= static::generateTreeFromArrayOfNodesOrStrictArray(
                    is_array($node) ? $node['children'] : $node->children);
            }
            $string .= '</li>';
        }
        $string .= '</ul>';
        return $string;
    }
    public static function arrayOfUnsortedListNodesFromArray(array $data):array{
        $nodes=[];
        foreach($data as $dataNode){
            if (!is_array($dataNode)){
                $node = new UnsortedListNode($dataNode);
                $nodes[]=$node;
            }else{
                $parent = end($nodes);
                $parent->children = static::arrayOfUnsortedListNodesFromArray($dataNode);
            }
        }
        return $nodes;
    }
    public static function getObWithOneConsecutiveSpace():string{
        $str = ob_get_clean();
        $str = preg_replace('/[ \t]+/', ' ', $str);
        ob_start();
        return $str;
    }
    public function button(
        string $id='', 
        string $content = '',//change to caption/text
        callable $onClickFunction = null, 
        bool $disabled = false, 
        bool $echo = true):string{
        $disabledAttribute = $disabled ? ' disabled' : '';
        $js = '';
        $result = '';
        $result .= "<button id='$id'$disabledAttribute>$content</button>" . PHP_EOL;;
        $result .= '<script>' . PHP_EOL;
        $js .= "var button = get('$id');" . PHP_EOL;
        if (is_array($onClickFunction)){
            $functionName = $onClickFunction[1];
        }elseif (is_string($onClickFunction)){
            $functionName = $onClickFunction;
        }
        $jsFunctionName = $id . 'OnClickFunction';
        $js .= 'function ' . $jsFunctionName . '(){' . PHP_EOL;
        ob_start();
        call_user_func($onClickFunction);
        $functionBody = ob_get_clean();
        $functionBodyLines = explode("\n", $functionBody);
        if ((count($functionBodyLines)>0) &&
            (strpos($functionBodyLines[0], '<script>') !== false)) { //String contains the substring
            $functionBodyLines = array_slice($functionBodyLines, 1, -1);
        }
        $functionBody = implode("\n", $functionBodyLines);
        $js .= $functionBody . PHP_EOL;
        $js .= '}' . PHP_EOL;
        $js .= "button.on('click', $jsFunctionName);" . PHP_EOL;
        $result .= $js;
        $result .= '</script>' . PHP_EOL;;
        $result = $this->parseCodeWithIndentation($result,sliceArray:false);
        if ($echo){
            echo $result;
        }
        return $result;
    }
    public function script(){
        echo '<script>';
    }
    public function _script(){
        echo '</script>';
    }
    public function array(array|object $array):string{
        $result = '{';
        foreach ($array as $key=>$value){
            $result .= $key . ':';
            if (is_array($value)||is_object($value)){
                $result .= $this->array($value); 
                $result .=  ', ';
            }else{
                $result .= $value . ', ';
            }
        }
        $lastCharacter = substr($result, -2);
        if ($lastCharacter === ', '){
            $result = substr($result, 0, -2);
        }
        $result .= '}';
        return $result;
    }
    public function arrayStringKeyValues(array|object $array):string{
        $result = '{';
        foreach ($array as $key=>$value){
            $result .= "'" . $key . "'" . ':';
            if (is_array($value)){
                $result .= $this->array($value); 
                $result .=  ', ';
            }else{
                $result .= "'" . $value . "'" . ', ';
            }
        }
        $lastCharacter = substr($result, -2);
        if ($lastCharacter === ', '){
            $result = substr($result, 0, -2);
        }
        $result .= '}';
        return $result;
    }
    public function arrayWithNewLine(array|object $array, int $indent = 0): string{
        $result = '{' . "\n";
        $count = count((array) $array);
        $current = 0;
        foreach ($array as $key => $value) {
            $current++;
            $result .= str_repeat(' ', $indent + $this->indent) . $key . ':';
            if (is_array($value) || is_object($value)) {
                $result .= $this->arrayWithNewLine($value, $indent + $this->indent);
                if ($current !== $count) {
                    $result .= ',' . "\n";
                } else {
                    $result .= "\n";
                }
            } else {
                if ($current !== $count) {
                    $result .= $value . ',' . "\n";
                } else {
                    $result .= $value . "\n";
                }
            }
        }
        $result .= str_repeat(' ', $indent) . '}';
        return $result;
    }    
    public function turnParsedCodeWithIndentationArrayToString(
        array $parsedArray,
        int $indentSize = 4):string{
        $result = '';
        foreach ($parsedArray as $index => $statement) {
            $statementIndentation = $statement['indentation']>0?$statement['indentation'] : 0;
            $indentationString = str_repeat(' ', $statementIndentation * $indentSize);
            $result .= $indentationString . $statement['statement'] . "\n";
        }
        return $result;
    }
    public function parseCodeWithIndentation(string $code = '', callable $function = null, bool $sliceArray = false, bool $returnString = true):array|string{
        if (is_callable($function)){
            ob_start();
            call_user_func($function);
            $code = ob_get_clean();
        }
        $lines = explode("\n", $code);
        $parsedStatements = [];
        $currentIndex = 0;
        $indentationLevel = 0;
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (!empty($trimmedLine)) {
                $parsedStatements[$currentIndex]['statement'] = $trimmedLine;
                $parsedStatements[$currentIndex]['indentation'] = $indentationLevel;
                $shoudIndentCount = 0;
                foreach ($this->indentSequences as $indentSequence){
                    $shoudIndentCount += substr_count($trimmedLine,$indentSequence);
                }
                foreach ($this->unIndentSequences as $unIndentSequence){
                    $shoudIndentCount -= substr_count($trimmedLine,$unIndentSequence);
                }
                $indentationLevel += $shoudIndentCount <=> 0;
                if ($parsedStatements[$currentIndex]['indentation'] > $indentationLevel) {
                    $parsedStatements[$currentIndex]['indentation'] = $indentationLevel;
                }
                $currentIndex++;
            }
        }
        if ($sliceArray){
            $parsedStatements = array_slice($parsedStatements, 1, -1);
        }
        if ($returnString){
            return $this->turnParsedCodeWithIndentationArrayToString($parsedStatements);
        }
        return $parsedStatements;
    }
    public function escapeBackslash(string $str):string {
        return str_replace('\\', '\\\\', $str);
    }
    public function arrayKeyValueToText(array $array):array{
        $arrayText=[];
        foreach ($array as $key=>$value){
            if (is_array($value)) {
                $arrayText["'$key'"] = $this->arrayKeyValueToText($value);
            } else {
                $arrayText["'$key'"] = "'$value'";
            }
        }
        return $arrayText;
    }
    public function arrayValueToText(array|object $array):array{
        $arrayText=[];
        foreach ($array as $key=>$value){
            if (is_array($value)){
                $arrayText["$key"]=$this->arrayValueToText($value);
            }else{
                $arrayText["$key"]="'$value'";
            }
        }
        return $arrayText;
    }
    public function formatStringRepresentationOfArray($arrayString, int $indent = 0):string {
        $arrayString = trim($arrayString);
        if ($arrayString[0] === '[' && $arrayString[strlen($arrayString) - 1] === ']') {
            $arrayString = substr($arrayString, 1, -1);
        }
        $formattedString = str_replace($this->arrayElementSeparator, ",\n", $arrayString);
        return $formattedString;
    }
    public function string(string $str):string{    
        $result = "'$str'";
        return $result;
    }
    public function createNewResizableDiv(string $string = 'data.result', bool $pre = false, string $after = ''){
        $pre = $pre? $this->trueBoolString : $this->falseBoolString;
        ?><script>
            createNewResizableDiv({data:<?php echo $string?>, pre:<?php echo $pre?>, after: '<?php echo $after?>' });
            return data;
        </script>
        <?php
    }
    public function dataResultAndEchoAsUnsortedList():string{
        return '\'<ul> <li> result: \' + data.result + \' </li> <li> echo: <ul> \' + data.echo + \' </ul ></ul> </li>\'';
    }
    public function createSelectFromArray(string $id = '', array $array = [], bool $echo = true):string{
        $str="<select id='$id'>";
        foreach($array as $option){
            $str .= "<option value='" . $option . "'>" . $option . "</option>\n";
        }
        $str .= "</select>";
        if ($echo){
            echo $str;
        }
        return $str;
    }
}


