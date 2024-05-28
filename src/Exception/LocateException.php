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
    protected $debug_trace;

    public function __construct($message = "", $location=[], $code = 0, Throwable $previous = null) {
        $this->setLocation($location);
        $this->setTrace();
        if($code === 0){
            $code = 404;
        }
        parent::__construct($message, $code, $previous);
    }

    public function getLocation(){
        return $this->location;
    }

    public function setLocation($location=[]){
        $this->location = $location;
    }

    public function getDebugTrace(){
        return $this->debug_trace;
    }

    public function setDebugTrace(){
        $debug = debug_backtrace(1);
        $this->debug_trace = $debug;
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
            $trace = $this->getDebugTrace();
            $string .= PHP_EOL . 'Trace: ' . PHP_EOL;
            foreach($trace as $value){
                if(
                    array_key_exists('file', $value) &&
                    array_key_exists('line', $value) &&
                    array_key_exists('function', $value)
                ){
                    $string .= $value['file'] . ' (' . $value['line'] . ') ' . $value['function'] .  PHP_EOL;
                }
            }
            return $string;
        } else {
            return parent::__toString();
        }
    }
}
