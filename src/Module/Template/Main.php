<?php
/**
 * @author          Remco van der Velde
 * @author          R3m\Io\Module\Parse\Build
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 * @note            Auto generated file, do not modify!
 */
namespace R3m\Io\Module\Template;

use stdClass;

use R3m\Io\App;

use R3m\Io\Module\Data;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Parse\Token;

use Exception;
use ErrorException;

class Main {
    private $object;
	private $parse;
	private $storage;

	public function __construct(Parse $parse, Data $storage){
	    set_time_limit(600);
		$this->parse($parse);
        $this->object($parse->object());
		$this->storage($storage);
	}

    public function object(App $object=null): ?App
    {
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject(App $object=null): void
    {
        $this->object= $object;
    }

    private function getObject(): ?App
    {
        return $this->object;
    }

	public function parse(Parse $parse=null): ?Parse
    {
	    if($parse !== null){
	        $this->setParse($parse);
	    }
	    return $this->getParse();
	}

	private function setParse(Parse $parse=null): void
    {
	    $this->parse = $parse;
	}

	private function getParse(): ?Parse
    {
	    return $this->parse;
	}

	public function storage(Data $storage=null): ?Data
    {
	    if($storage !== null){
	        $this->setStorage($storage);
	    }
	    return $this->getStorage();
	}

	private function setStorage(Data $storage=null): void
    {
	    $this->storage = $storage;
	}

	private function getStorage(){
	    return $this->storage;
	}

	protected function assign_min_equal($variable1=null, $variable2=null): int | float
    {
	    $variable1 += 0;
	    $variable2 += 0;
	    return $variable1 - $variable2;
	}

	protected function assign_plus_equal($variable1=null, $variable2=null): int | float | string
    {
        if(is_string($variable1)){
            return $variable1 . $variable2;
        } else {
            $variable1 += 0;
            $variable2 += 0;
            return $variable1 + $variable2;
        }
	}

	protected function assign_dot_equal($variable1=null, $variable2=null): string
    {
        $variable1 = (string) $variable1;
        $variable2 = (string) $variable2;
        return $variable1 . $variable2;
	}

	protected function assign_plus_plus($variable=0): int | float
    {
	    $variable += 0;
	    $variable++;
	    return $variable;
	}

	protected function assign_min_min($variable=0): int | float
    {
	    $variable += 0;
	    $variable--;
	    return $variable;
	}

	protected function value_plus_plus($variable=0): int | float
    {
	    $variable += 0;
	    $variable++;
	    return $variable;
	}

	protected function value_min_min($variable=0): int |  float
    {
	    $variable += 0;
	    $variable--;
	    return $variable;
	}

	protected function plus_plus_assign($variable=0): int |  float
    {
	    $variable += 0;
	    ++$variable;
	    return $variable;
	}

	protected function min_min_assign($variable=0): int |  float
    {
	    $variable += 0;
	    --$variable;
	    return $variable;
	}

	protected function plus_plus_value($variable=0): int |  float
    {
	    $variable += 0;
	    ++$variable;
	    return $variable;
	}

	protected function min_min_value($variable=0): int |  float
    {
	    $variable += 0;
	    --$variable;
	    return $variable;
	}

	protected function value_plus($variable1=null, $variable2=null) : int |  float | string
{
        $type1 = getType($variable1);
        $type2 = getType($variable2);
        if(
            $type1 == Token::TYPE_STRING ||
            $type2 == Token::TYPE_STRING
        ){
            return (string) $variable1 . (string) $variable2;
        } else {
            $variable1 += 0;
            $variable2 += 0;
            return $variable1 + $variable2;
        }
	}

	protected function value_minus($variable1=null, $variable2=null): int |  float
    {
        try {
            $variable1 += 0;
            $variable2 += 0;
            return $variable1 - $variable2;
        }
        catch(ErrorException | Exception $exception){
            trace();
//            ddd($this->object()->data());
        }
        return 0;
	}

	protected function value_multiply($variable1=null, $variable2=null): int |  float
    {
        d($variable1);
        d($variable2);
        $variable1 += 0;
        $variable2 += 0;
        d($variable1);
        d($variable2);
        return $variable1 * $variable2;
	}

	protected function value_divide($variable1=null, $variable2=null): int |  float
    {
	    $variable1 += 0;
	    $variable2 += 0;
	    if($variable2 != 0){
	        return $variable1 / $variable2;
	    } else {
	        return INF;
	    }
	}

	protected function value_modulo($variable1=null, $variable2=null): int |  float
    {
	    return $variable1 % $variable2;
	}

	protected function value_smaller($variable1=null, $variable2=null): bool
    {
	    return $variable1 < $variable2;
	}

	protected function value_smaller_equal($variable1=null, $variable2=null): bool
    {
	    return $variable1 <= $variable2;
	}

	protected function value_smaller_smaller($variable1=null, $variable2=null): bool | int
    {
	    return $variable1 << $variable2;
	}

	protected function value_greater($variable1=null, $variable2=null): bool
    {
	    return $variable1 > $variable2;
	}

	protected function value_greater_equal($variable1=null, $variable2=null): bool
    {
	    return $variable1 >= $variable2;
	}

	protected function value_greater_greater($variable1=null, $variable2=null): bool | int
    {
	    return $variable1 >> $variable2;
	}

	protected function value_not_equal($variable1=null, $variable2=null): bool
    {
	    return $variable1 != $variable2;
	}

	protected function value_not_identical($variable1=null, $variable2=null): bool
    {
	    return $variable1 !== $variable2;
	}

	protected function value_equal($variable1=null, $variable2=null): bool
    {
	    return $variable1 == $variable2;
	}

	protected function value_identical($variable1=null, $variable2=null): bool
    {
	    return $variable1 === $variable2;
	}

	protected function cache_write($url){
	    if(opcache_is_script_cached($url) === false){
	        opcache_compile_file($url);
	    }
	}

	protected function cache_invalidate($url){
	    if(opcache_is_script_cached($url) === true){
	        opcache_invalidate($url, true);
	    }
	}
}
