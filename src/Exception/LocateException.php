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

use Throwable;

use Exception;

class LocateException extends Exception {

    protected $object;
    protected $location;

    public function __construct(App $object, $message = "", $location=[], $code = 0, Throwable $previous = null) {
        $this->setLocation($location);
        $this->setObject($object);
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
     * @throws Exception
     */
    public function __toString()
    {
        $object = $this->object();
        if($object){
            $object->config('exception.locate', '{{config(\'project.dir.host\')}}{{string.uppercase.first(host.subdomain())}}/{{string.uppercase.first(host.domain())}}/{{string.uppercase.first(host.extension())}}/View/Exception/Locate.tpl');
            $object->set('exception.message', $this->getMessage());
            $object->set('exception.code', $this->getCode());
            $object->set('exception.file', $this->getFile());
            $object->set('exception.line', $this->getLine());
            $object->set('exception.line', $this->getLine());
            $object->set('exception.previous', $this->getPrevious());
            $object->set('exception.location', $this->getLocation());
            $parse = new Parse($object, $object->data());
            $url = $parse->compile($object->config('exception.locate'), $object->data());
            if(File::exist($url)){
                $object->logger('FileRequest')->exception('Locate', [ $url ]);
                $read = File::read($url);
                return $parse->compile($read, $object->data());
            } else {
                throw new Exception('Exception file (' . $url . ') not found...');
            }
        } else {
            $string = parent::__toString();
            $location = $this->getLocation();
            $string .= PHP_EOL . 'Locations: ' . PHP_EOL;
            foreach($location as $value){
                $string .= $value . PHP_EOL;
            }
            if(App::is_cli()){
                $output = [];
                $output[] = $string;
            } else {
                $output = [];
                $output[] = '<pre>';
                $output[] = $string;
                $output[] = '</pre>';
            }

            return implode(PHP_EOL, $output);
        }
    }
}
