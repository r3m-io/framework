<?php
/**
 * @author          Remco van der Velde
 * @since           04-01-2019
 * @copyright       (c) Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *  -    all
 */
namespace R3m\Io\Module;

use R3m\Io\App;

use stdClass;

use Exception;

use R3m\Io\Exception\ObjectException;

class Response {
    const TYPE_CLI = 'cli';
    const TYPE_JSON = 'json';
    const TYPE_HTML = 'html';
    const TYPE_OBJECT = 'object';
    const TYPE_OBJECT_LINE = 'object-line';
    const TYPE_FILE = 'file';
    const STATUS_OK = 200;
    const STATUS_CREATED = 201;
    const STATUS_ACCEPTED = 202;
    const STATUS_NON_AUTHORITATIVE_INFORMATION = 203;
    const STATUS_NO_CONTENT = 204;
    const STATUS_RESET_CONTENT = 205;
    const STATUS_PARTIAL_CONTENT = 206;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_PAYMENT_REQUIRED = 402;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_METHOD_NOT_ALLOWED = 405;
    const STATUS_NOT_ACCEPTABLE = 406;
    const STATUS_PROXY_AUTHENTICATION_REQUIRED = 407;
    const STATUS_REQUEST_TIMEOUT = 408;
    const STATUS_CONFLICT = 409;
    const STATUS_GONE = 410;
    const STATUS_LENGTH_REQUIRED = 411;
    const STATUS_PRECONDITION_FAILED = 412;
    const STATUS_CONTENT_TOO_LARGE = 413;
    const STATUS_URI_TOO_LONG = 414;
    const STATUS_UNSUPPORTED_MEDIA_TYPE = 415;
    const STATUS_RANGE_NOT_SATISFIABLE = 416;
    const STATUS_EXPECTATION_FAILED = 417;
    const STATUS_MISDIRECT_REQUEST = 421;
    const STATUS_UNPROCESSABLE_CONTENT = 422;
    const STATUS_LOCKED = 423;
    const STATUS_FAILED_DEPENDENCY = 424;
    const STATUS_UPGRADE_REQUIRED = 426;
    const STATUS_PRECONDITION_REQUIRED = 428;
    const STATUS_TOO_MANY_REQUESTS = 429;
    const STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const STATUS_UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_NOT_IMPLEMENTED = 501;
    const STATUS_BAD_GATEWAY = 502;
    const STATUS_SERVICE_UNAVAILABLE = 503;
    const STATUS_GATEWAY_TIMEOUT = 504;
    const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
    const STATUS_VARIANT_ALSO_NEGOTIATES = 506;
    const STATUS_INSUFFICIENT_STORAGE = 507;
    const STATUS_LOOP_DETECTED = 508;
    const STATUS_NETWORK_AUTHENTICATION_REQUIRED = 511;

    private $data;
    private $type;
    private $status;
    private $header;

    public function __construct($data='', $type='', $status=Response::STATUS_OK, $headers=[]){
        $this->data($data);
        $this->type($type);
        $this->status($status);
        $this->header($headers);
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function output(App $object, Response $response): mixed
    {
        $type = $response->type();
        if($type === null &&  $object->data(App::CONTENT_TYPE) === App::CONTENT_TYPE_JSON){
            $type = Response::TYPE_OBJECT;
        }
        elseif($type === null){
            $type = Response::TYPE_FILE;
        }
        $status = $response->status();
        if($type === Response::TYPE_CLI){
            //left blank
        } else {
            if(!Handler::header('has', 'Status')){
                Handler::header('Status: ' . $status, $status, true);
            }
            if(!Handler::header('has', 'Content-Type')){
                switch($type){
                    case Response::TYPE_OBJECT :
                    case Response::TYPE_JSON :
                        Handler::header('Content-Type: application/json', null, true);
                        break;
                    case Response::TYPE_HTML :
                        Handler::header('Content-Type: text/html', null, true);
                        break;
                    case Response::TYPE_FILE :
                        break;
                }
            }
            $header = $response->header();
            if(is_array($header)){
                foreach($header as $value){
                    Handler::header($value,null, true);
                }
            }
        }

        switch($type){
            case Response::TYPE_JSON :
                if(is_string($response->data())){
                    return trim($response->data(), " \t\r\n");
                } else {
                    try {
                        return Core::object($response->data(), Core::OBJECT_JSON);
                    }
                    catch (Exception $exception){
                        return $exception;
                    }
                }
            case Response::TYPE_OBJECT :
            case Response::TYPE_OBJECT_LINE :
                $json = new stdClass();
                $json->html = $response->data();
                if($object->data('method')){
                    $json->method = $object->data('method');
                } else {
                    $json->method = $object->request('method');
                }
                if($object->data('target')){
                    $json->target = $object->data('target');
                } else {
                    $json->target = $object->request('target');
                }
                $append_to = $object->data('append-to');
                if(empty($append_to)){
                    $append_to = $object->data('append.to');
                }
                if(empty($append_to)){
                    $append_to = $object->request('append-to');
                }
                if(empty($append_to)){
                    $append_to = $object->request('append.to');
                }
                if($append_to){
                    if(empty($json->append)){
                        $json->append = new stdClass();
                    }
                    $json->append->to = $append_to;
                }
                $json->script = $object->data(App::SCRIPT);
                $json->link = $object->data(App::LINK);
                if($type === Response::TYPE_OBJECT_LINE){
                    return Core::object($json, Core::OBJECT_JSON_LINE);
                } else {
                    return Core::object($json, Core::OBJECT_JSON);
                }
            case Response::TYPE_CLI :
                $data = $response->data();
                if(is_array($data)){
                    $data = implode(PHP_EOL, $data);
                }
                if(
                    (
                        $status >= Response::STATUS_BAD_REQUEST &&
                        $status < Response::STATUS_INTERNAL_SERVER_ERROR
                    ) ||
                    (
                        $status >= Response::STATUS_INTERNAL_SERVER_ERROR &&
                        $status <= Response::STATUS_HTTP_VERSION_NOT_SUPPORTED
                    )
                ){
                    echo Cli::tput('color', Cli::COLOR_RED);
                    echo 'ERROR' . PHP_EOL;
                    echo str_repeat('_', Cli::tput('width')) . PHP_EOL;
                    echo $data;
                    echo Cli::tput('reset');
                } else {
                    echo $data;
                }
                return null;
            case Response::TYPE_FILE :
            case Response::TYPE_HTML :
                return $response->data();
        }
        return null;
    }

    public function data($data=null): mixed
    {
        if($data !== null){
            $this->setData($data);
        }
        return $this->getData();
    }

    private function setData($data=null): void
    {
        $this->data = $data;
    }

    private function getData(): mixed
    {
        return $this->data;
    }

    public function type($type=null): ?string
    {
        if($type !== null){
            $this->setType($type);
        }
        return $this->getType();
    }

    private function setType($type=null): void
    {
        $this->type = $type;
    }

    private function getType(): ?string
    {
        return $this->type;
    }

    public function status($status=null): ?int
    {
        if($status !== null){
            $this->setStatus($status);
        }
        return $this->getStatus();
    }

    private function setStatus($status=null): void
    {
        $this->status = $status;
    }

    private function getStatus(): ?int
    {
        return $this->status;
    }

    public function header($header=null): ?array
    {
        if($header !== null){
            $this->setHeader($header);
        }
        return $this->getHeader();
    }

    private function setHeader($header=null): void
    {
        $this->header = $header;
    }

    private function getHeader(): ?array
    {
        return $this->header;
    }
}