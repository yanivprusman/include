<?php
declare(strict_types=1);
namespace G_H_Projects;
// use App\Enums\JSNodeFormat;
use G_H_Projects\Enums\JSNodeFormat;
class JSNode{
    public $children = [];
    public static int $spacesInIndent = 4;
    public function __construct(
        public int $indent = 0, 
        public int $childrenIndent = 1,
        public ?string $key = null,
        public null|string|JSNode $value = null,
        public string $childrenBegin = "{",
        public string $childrenEnd = "}",
        public string $separatorKey_Value = ':',
        public string $separatorKeyValue_ = ",",
        public JSNodeFormat $jSNodeFormat = JSNodeFormat::NotVisible,
        public bool $newLineBeforeChildrenBegin = false,
        public bool $newLineAfterChildrenBegin = true,
        public bool $newLineBeforeChildrenEnd = true,
        public bool $newLineAfterChildrenEnd = false,
        public bool $newLineAfterSeparatorKeyValue_ = true,
        public bool $noChildrenAsEmmptyArrayForKeyWithotValue = true
        ) {
    }
    public function addChild(JSNOde $node):JSNOde {
        $node->indent = $this->childrenIndent;
        if($node->key){
            $this->children[$node->key] = $node;
        }else{
            $this->children[]= $node;
        }
        return $this;
    }
    public function expandIndent():string{
        $result = str_repeat(' ', $this->indent*static::$spacesInIndent);
        return $result;
    }
    public function string(int $indent = 0):string{
        $newLineAfterSeparatorKeyValue_ = $this->newLineAfterSeparatorKeyValue_ ? "\n":'';
        $newLineBeforeChildrenBegin = $this->newLineBeforeChildrenBegin ? "\n":'';
        $newLineAfterChildrenBegin = $this->newLineAfterChildrenBegin ? "\n":'';
        $newLineBeforeChildrenEnd = $this->newLineBeforeChildrenEnd ? "\n":''; 
        $newLineAfterChildrenEnd = $this->newLineAfterChildrenEnd ? "\n":''; 
        $result ='';
        if ($this->value instanceof static){
            $value = $this->value->string();
        }else{
            $value = (string)$this->value;
        }
        $result .= $this->expandIndent();
        $result .= $this->jSNodeFormat->string(key:$this->key,value:$this->value,separatorKey_Value:$this->separatorKey_Value);
        $childrenString = '';
        if (sizeof($this->children)>0){
            $childrenString .= $newLineBeforeChildrenBegin . $this->childrenBegin . $newLineAfterChildrenBegin;
            foreach($this->children as $child){
                $childrenString .= $child->string();// why separator?
                $childrenString .= $this->separatorKeyValue_ . $newLineAfterSeparatorKeyValue_;
            }
            $lengthOfStringToTrimAtEnd = strlen($this->separatorKeyValue_) + strlen
            ($newLineAfterSeparatorKeyValue_);
            if ($lengthOfStringToTrimAtEnd!=0){
                $childrenString = substr($childrenString, 0, -$lengthOfStringToTrimAtEnd);
            }
            $childrenString .= $newLineBeforeChildrenEnd . $this->expandIndent() . $this->childrenEnd . $newLineAfterChildrenEnd;
            $befores = $newLineBeforeChildrenBegin . $this->childrenBegin . $newLineAfterChildrenBegin . $newLineBeforeChildrenEnd . $this->expandIndent() . $this->childrenEnd . $newLineAfterChildrenEnd;
            if (strlen($childrenString)===strlen($befores)){
                $childrenString = '{}';
            }
        }else{
            if ($this->noChildrenAsEmmptyArrayForKeyWithotValue){
                if (($this->jSNodeFormat == JSNodeFormat::KeyWithoutQuotes)
                    ||($this->jSNodeFormat == JSNodeFormat::KeyWithQuotes)){
                    $childrenString = '{}';
                }
            }
        }
        $result .= $childrenString;
        if ($result === $this->expandIndent()){
            $result = '';
        }
        return $result;
    }
    public function propagateIndent(){
        foreach($this->children as $child){
            $child->indent = $this->indent + $this->childrenIndent;
            if (sizeof($child->children)>0){
                $child->propagateIndent();
            }
        }
    }
    public static function toAsocArray(JSNode $jSNode):array{
        $arr = [];
        if ($jSNode->value instanceof static){
            $arr[$jSNode->key]= static::toAsocArray($jSNode->value);
        }else{
            $arr[$jSNode->key]= $jSNode->value;
        }
        foreach ($jSNode->children as $child){
            $arr['children'][] = static::toAsocArray($child);
        }
        return $arr;
    }
    public static function fromAsocArray(array $arr):JSNode{
        $jSNode = new JSNode();
        // if ($arr)
        return $jSNode;
    }
}



