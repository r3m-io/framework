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

use R3m\Io\App;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;

use Exception;
use Throwable;

class PluginNotFoundException extends Exception {

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

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    public function __toString()
    {
        $string = parent::__toString();
        $location = $this->getLocation();
        if(is_array($location)){
            $string .= PHP_EOL . 'Locations: ' . PHP_EOL;
            foreach($location as $value){
                $string .= $value . PHP_EOL;
            }
        }
        if(App::is_cli()){
            return $string;
        }
        $output = [];
        $output[] = '<pre>';
        $output[] = $string;
        $output[] = '</pre>';
        return implode(PHP_EOL, $output);

    }
}
