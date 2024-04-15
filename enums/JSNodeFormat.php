<?php
declare (strict_types=1);
namespace G_H_PROJECTS_INCLUDE\Enums;

enum JSNodeFormat:int{
    case KeyWithoutQuotes = 1;
    case KeyWithQuotes = 2;
    case ValueWithoutQuotes = 3;
    case ValueWithQuotes = 4;
    case KeyWithoutQuotesValueWithQuotes = 5;
    case KeyWithoutQuotesValueWithoutQuotes = 6;
    case KeyWithQuotesValueWithQuotes = 7;
    case KeyWithQuotesValueWithoutQuotes = 8;
    case NotVisible = 9;
    public function string(?string $key, ?string $value, string $separatorKey_Value):string{
        if (!$key) $key = '';
        if (!$value) $value = '';
        $result = match($this){
            JSNodeFormat::KeyWithoutQuotes=> (string) $key . $separatorKey_Value,
            JSNodeFormat::KeyWithQuotes=> "'" . (string) $key . "'" . $separatorKey_Value,
            JSNodeFormat::ValueWithoutQuotes=> $value,
            JSNodeFormat::ValueWithQuotes=> "'" . $value . "'",
            JSNodeFormat::KeyWithoutQuotesValueWithQuotes=>(string) $key . $separatorKey_Value . "'" . $value . "'",
            JSNodeFormat::KeyWithoutQuotesValueWithoutQuotes=>(string) $key . $separatorKey_Value . $value ,
            JSNodeFormat::KeyWithQuotesValueWithQuotes=> "'" . (string) $key . "'" .$separatorKey_Value . "'" . $value . "'",
            JSNodeFormat::NotVisible=> '',
            JSNodeFormat::KeyWithQuotesValueWithoutQuotes =>  "'" . (string) $key . "'" . $separatorKey_Value . $value 
        };
        return $result;
    }
}


