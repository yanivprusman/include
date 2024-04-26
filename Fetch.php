<?php
declare (strict_types=1);
namespace G_H_PROJECTS_INCLUDE;
use G_H_Projects\Enums\JSNodeFormat;
// use App\Enums\JSNodeFormat;
use G_H_Projects\JSNode;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use App\Attributes\Post;
// use App\Container;
use Illuminate\Container\Container;
use Throwable;
use Exception;
use ReflectionClass;

class Fetch {
    public static ?Html $html=null;
    public null|JSNode|array $classNode = null;
    public null|JSNode|array $methodNode = null;
    public null|JSNode|array $singletonArguments = null;
    public null|JSNode|array $methodArguments = null;
    public bool $returnResult = true;
    public bool $returnEcho = true;
    public null|JSNode|array $then = null;
    public $catch = null;
    public string $thenVariableName = 'data';
    public string $catchVariableName = 'error';
    public string $resultVariableName = 'result';
    public string $echoVariableName = 'echo';
    public function __construct(
        public ?Container $container=null,
        public string $uri = '/callMethod',
        public string $class = '',
        public bool $addSlashes = true, //todo define when to add slashes
        public string $method = '',
        public bool $static = false,
        public bool $singleton = false,
        public bool $resultAsUnsortedList = true,
        public bool $classIsAVariable = false,
        public bool $methodIsAVariable = false,
        public bool $echoReturn = true,
        callable $catch = null,
        public array $jsonDecoded = []
    )
    {
        if (!static::$html){
            static::$html = new Html();
        } 
        $calledFromPost = sizeof($jsonDecoded)>0;
        if ($calledFromPost){
            $this->constructFromPost();
            return;
        }
        if ($this->addSlashes){
            if ($this->class){
                $this->class = addslashes($this->class);
            }
        }
        $this->singletonArguments = new JSNode(key:'singletonArguments',jSNodeFormat:JSNodeFormat::KeyWithQuotes);
        $this->methodArguments = new JSNode(key:'methodArguments',jSNodeFormat:JSNodeFormat::KeyWithQuotes);
        $this->then = new JSNode(key:'then',jSNodeFormat:JSNodeFormat::NotVisible,childrenBegin:'',childrenEnd:'',childrenIndent:0,separatorKeyValue_:'',newLineAfterChildrenBegin:false,newLineBeforeChildrenEnd:false,
            noChildrenAsEmmptyArrayForKeyWithotValue:false);
        if ($this->classIsAVariable){
            $classFormat = JSNodeFormat::KeyWithQuotesValueWithoutQuotes;
        }else{
            $classFormat = JSNodeFormat::KeyWithQuotesValueWithQuotes;
        }
        $this->classNode = new JSNode(key:'class',value:$this->class, jSNodeFormat:$classFormat);
        if ($this->methodIsAVariable){
            $methodFormat = JSNodeFormat::KeyWithQuotesValueWithoutQuotes;
        }else{
            $methodFormat = JSNodeFormat::KeyWithQuotesValueWithQuotes;
        }
        $this->methodNode = new JSNode(key:'method',value:$this->method, jSNodeFormat:$methodFormat);
        $this->catch = $catch;
    }
    public function constructFromPost(){// todo check if it is bool property
        foreach ($this->jsonDecoded as $key=>$val){
            if ($val === static::$html->trueBoolString){
                $val = true;
            }elseif ($val === static::$html->falseBoolString){
                $val = false;
            // }
            // if ($this->$key instanceof JSNode || $this->$key===null ){
            //     $result = '$result';
            } 
            // else{
                $this->$key = $val;
            // }
        }
        if ($this->class){
            // $this->class = addslashes($this->class);
        }
    }
    public function addMethodArgument(string $key = '', string $value = '', bool $variable = false){
        $format = $variable ? JSNodeFormat::KeyWithQuotesValueWithoutQuotes : 
            JSNodeFormat::KeyWithQuotesValueWithQuotes;
        $methodArgument = new JSNode(key:$key,value:$value,jSNodeFormat: $format);
        $this->methodArguments->addChild($methodArgument);
    }
    public function addSingletonArgument(string $key = '', string $value = '', bool $variable = false){
        $format = $variable ? JSNodeFormat::KeyWithQuotesValueWithoutQuotes : 
            JSNodeFormat::KeyWithQuotesValueWithQuotes;
        $singletonArgument = new JSNode(key:$key,value:$value,jSNodeFormat: $format);
        $this->singletonArguments->addChild($singletonArgument);
    }
    public function fetchScript(){//what to do with slashes?
        $singleton = $this->singleton ? static::$html->trueBoolString : static::$html->falseBoolString;
        $returnResult = $this->returnResult ? static::$html->trueBoolString : static::$html->falseBoolString;
        $returnEcho = $this->returnEcho ? static::$html->trueBoolString : static::$html->falseBoolString;
        $resultAsUnsortedList = $this->resultAsUnsortedList ? static::$html->trueBoolString : static::$html->falseBoolString;
        $return = $this->echoReturn ? 'return ' : '';
        ?> <script>
            <?php echo $return; ?> fetch(
                '<?php echo $this->uri?>',
                {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/x-www-form-urlencoded'
                    },
                    body:JSON.stringify({
                        <?php echo $this->classNode->string() ?>,
                        <?php echo $this->methodNode->string() ?>,
                        'singleton':'<?php echo $singleton ?>',
                        <?php echo $this->singletonArguments->string() . ',' . PHP_EOL;?>
                        <?php echo $this->methodArguments->string() ;?>,
                        'returnResult':'<?php echo $returnResult;?>',
                        'returnEcho':'<?php echo $returnEcho;?>',
                        'resultAsUnsortedList':'<?php echo $resultAsUnsortedList;?>',
                    })
                }
            )
            .then(<?php echo $this->thenVariableName; ?> => {
                // console.log(< ?php echo $this->thenVariableName; ?>);
                return <?php echo $this->thenVariableName; ?>.json();
            })
            .then(<?php echo $this->thenVariableName; ?> =>{ 
                if ('error' in <?php echo $this->thenVariableName; ?>){
                    throw new Error(<?php echo $this->thenVariableName; ?>.error); 
                } 
                return <?php echo $this->thenVariableName; ?>; })
            <?php echo $this->then->string();
            ?>
            .catch(<?php echo $this->catchVariableName; ?> => {
                <?php
                if (!$this->catch){
                    ?>
                    debug.write(<?php echo $this->catchVariableName; ?>)
                    console.error('', <?php echo $this->catchVariableName; ?>);
                <?php }else{
                    $result = static::$html->parseCodeWithIndentation(function:$this->catch,sliceArray:true);
                    echo $result;
                }?>
            });
        </script>
        <?php
    }
    public function fetch(bool $echo = true, bool $returnResult = true, bool $returnEcho = true,
        bool $resultAsUnsortedList = true, bool $addScriptTags = false):string{
        $this->returnResult = $returnResult;
        $this->returnEcho = $returnEcho;
        $this->resultAsUnsortedList = $resultAsUnsortedList;
        $result = static::$html->parseCodeWithIndentation(function:[$this,'fetchScript'],sliceArray:true);
        if ($addScriptTags) {
            $result = '<script>' . PHP_EOL . $result . '</script>' . PHP_EOL;
        }
        if ($echo){
            echo $result;
        }
        return $result;
    }
    public function addThen(callable $function, bool $echo = false):string{
        $result = '.then(data => {' . PHP_EOL;
        $result .= static::$html->parseCodeWithIndentation(function:$function,sliceArray:true);
        $result .= '})' . PHP_EOL;
        $node = new JSNode(value:$result,jSNodeFormat:JSNodeFormat::ValueWithoutQuotes);
        $this->then->addChild($node);
        $result = $node->string();
        if ($echo){
            echo $result;
        }
        return $result;
    }
    public function result(bool $echo = true):string{
        $result = $this->thenVariableName . '.' . $this->resultVariableName;
        if ($echo){
            echo $result;
        }
        return $result;
    }
    public function echo(bool $echo = true):string{
        $result = $this->thenVariableName . '.' . $this->echoVariableName;
        if ($echo){
            echo $result;
        }
        return $result;
    }
    public function doFetch():ResponseInterface{
        $singletonArguments= [];
        foreach ($this->singletonArguments->children as $child){
            $singletonArguments [$child->key]=$child->value;
        }
        $methodArguments= [];
        foreach ($this->methodArguments->children as $child){
            $methodArguments [$child->key]=$child->value;
        }
        $appEnv = getenv('APP_ENV');
        if(!$appEnv) $appEnv = 'localhost:80';
        $client = new Client([
            'base_uri' => 'http://' . $appEnv,
        ]);
        return $client->request(
            uri:'/callMethod',
            method:'POST',
            options:['json'=>[
                'class'=>$this->class,
                'method'=>$this->method,
                'singletonArguments'=>$singletonArguments,
                'methodArguments'=>$methodArguments,
                ]
            ]);
    }
    #[Post('/callMethod')]
    public function post(){
        try{
            $fetchDecoded = json_decode(file_get_contents('php://input'), true);//bool ascociative array
            $fetch = new Fetch(jsonDecoded:$fetchDecoded);
            ob_start();
            $class_exists_ob = class_exists($fetch->class);
            ob_end_clean();
            if($class_exists_ob){ 
                if(method_exists($fetch->class,$fetch->method)){
                    $class = $fetch->class;
                    if ($fetch->singleton){
                        $class = call_user_func_array([$fetch->class,'getInstance'],$fetch->singletonArguments);
                    }else if(!$fetch->static){
                        $reflectionClass = new ReflectionClass($class);
                        $class = $reflectionClass->newInstanceArgs($fetch->singletonArguments);
                    }
                    ob_start();
                    $result = call_user_func_array([$class,$fetch->method],$fetch->methodArguments);
                    $cleanedBuffer = ob_get_clean();
                    if ($fetch->resultAsUnsortedList){
                        if (is_array($result)){
                            $result = Html::arrayOfUnsortedListNodesFromArray($result);
                            $result = Html::generateTreeFromArrayOfNodesOrStrictArray($result);
                        }
                    }
                    $result = json_encode([$fetch->resultVariableName => $result,
                        $fetch->echoVariableName=>$cleanedBuffer]);
                    echo $result;
                }
            }else{
                throw new Exception('class ' . $fetch->class . ' doesnt exist ');
            }
        } catch (Throwable $e) {
            $result = 'Php: ' .  $e->getMessage() . "\n";
            ob_start();
            var_dump($e);
            $getClean = ob_get_clean();

            echo json_encode(['error'=>$result . $getClean]);
        }
    }
}
