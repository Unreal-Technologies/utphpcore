<?php
namespace Utphpcore\Source\Analyzers\PhpAnalyzer;

class Tokens
{
    public const T_OPEN_TAG = 389;
    public const T_NAMESPACE = 339;
    public const T_WHITESPACE = 392;
    public const T_NAME_QUALIFIED = 265;
    public const T_CLASS = 333;
    public const T_INTERFACE = 335;
    public const T_STRING = 262;
    public const T_PUBLIC = 326;
    public const T_FUNCTION = 310;
    public const T_NAME_FULLY_QUALIFIED = 263;
    public const T_VARIABLE = 266;
    public const T_DOUBLE_COLON = 397;
    public const T_CONSTANT_ENCAPSED_STRING = 269;
    public const T_COMMENT = 387;
    public const T_OBJECT_OPERATOR = 384;
    public const T_DOUBLE_ARROW = 386;
    public const T_FOREACH = 297;
    public const T_AS = 301;
    public const T_DOC_COMMENT = 388;
    public const T_LNUMBER = 260;
    public const T_ARRAY = 341;
    public const T_IS_IDENTICAL = 368;
    public const T_IF = 287;
    public const T_INSTANCEOF = 283;
    public const T_CONTINUE = 308;
    public const T_BOOLEAN_OR = 364;
    public const T_RETURN = 313;
    public const T_UNSET = 329;
    public const T_USE = 318;
    public const T_ELSE = 289;
    public const T_NEW = 284;
    public const T_CONST = 312;
    public const T_MINUS_EQUAL = 353;
    public const T_LIST = 340;
    public const T_IS_NOT_IDENTICAL = 369;
    public const T_BREAK = 307;
    public const T_BOOLEAN_AND = 365;
    public const T_ELSEIF = 288;
    public const T_ISSET = 330;
    public const T_EXIT = 286;
    public const T_ENUM = 336;
    public const T_CASE = 304;
    public const T_STATIC = 321;
    public const T_EXTENDS = 337;
    public const T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG = 403;
    public const T_FINAL = 323;
    public const T_ATTRIBUTE = 351;
    public const T_DIR = 345;
    public const T_REQUIRE_ONCE = 276;
    public const T_THROW = 317;
    public const T_TRAIT = 334;
    public const T_ABSTRACT = 322;
    public const T_INCLUDE = 272;
    public const T_CLONE = 285;
    public const T_IMPLEMENTS = 338;
    public const T_ECHO = 291;
    public const T_PRIVATE = 324;
    public const T_METHOD_C = 348;
    public const T_FOR = 295;
    public const T_INC = 375;
    public const T_ELLIPSIS = 399;
    public const T_DEC = 376;
    public const T_CONCAT_EQUAL = 356;
    public const T_IS_SMALLER_OR_EQUAL = 370;
    public const T_PROTECTED = 325;
    public const T_WHILE = 293;
    public const T_PLUS_EQUAL = 352;
    public const T_TRY = 314;
    public const T_CATCH = 315;
    public const T_SWITCH = 302;
    public const T_DEFAULT = 305;
    public const T_IS_NOT_EQUAL = 367;
    public const T_STRING_CAST = 379;
    public const T_IS_EQUAL = 366;
    public const T_CLASS_C = 346;
    public const T_NULLSAFE_OBJECT_OPERATOR = 385;
    public const T_MATCH = 306;
    public const T_EVAL = 274;
    public const T_PRINT = 280;
    
    /**
     * @param int $value
     * @return string|null
     * @throws \Utphpcore\Data\Exceptions\NotImplementedException
     */
    public static function getToken(int $value): ?string
    {
        $rc = new \ReflectionClass(__CLASS__);
        
        foreach($rc -> getConstants() as $k => $v)
        {
            if($v === $value)
            {
                return __CLASS__.'::'.$k;
            }
        }
        
        throw new \Utphpcore\Data\Exceptions\NotImplementedException('Undefined value: '.$value.' ('.token_name($value).'?)');
    }
}