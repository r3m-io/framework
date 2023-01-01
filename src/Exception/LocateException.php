<?php
/**
 * @author          Remco van der Velde
 * @since           10-02-2021
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Exception;

use Exception;
use Throwable;
use R3m\Io\App;

class LocateException extends Exception {

    protected $object;
    protected $location;

    public function __construct($message = "", $location=[], $code = 0, Throwable $previous = null) {
        $this->setLocation($location);
        parent::__construct($message, $code, $previous);
    }

    public function object($object=null){
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject(App $object){
        $this->object = $object;
    }

    private function getObject(){
        return $this->object;
    }

    public function getLocation(){
        return $this->location;
    }

    public function setLocation($location=[]){
        $this->location = $location;
    }

    public function __toString()
    {
        $object = $this->object();
        if($object){
            ddd($object->config());
        } else {
            $string = parent::__toString(); // TODO: Change the autogenerated stub
            $location = $this->getLocation();
            $string .= PHP_EOL . 'Locations: ' . PHP_EOL;
            foreach($location as $value){
                $string .= $value . PHP_EOL;
            }
            $output = [];
            $output[] = '<pre>';
            $output[] = $string;
            $output[] = '</pre>';
            return implode(PHP_EOL, $output);
        }
    }
}
