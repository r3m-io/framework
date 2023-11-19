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

use Throwable;

use Exception;

class LocateException extends Exception {

    protected $object;
    protected $location;

    public function __construct($message = "", $location=[], $code = 0, Throwable $previous = null) {
        $this->setLocation($location);
        parent::__construct($message, $code, $previous);
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
     * @throws Exception
     */
    public function __toString()
    {
        if(App::is_cli()){
            $string = parent::__toString();
            $location = $this->getLocation();
            $string .= PHP_EOL . 'Locations: ' . PHP_EOL;
            foreach($location as $value){
                $string .= $value . PHP_EOL;
            }
            return $string;
        } else {
            return parent::__toString();
        }
    }
}
