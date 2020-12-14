<?php

namespace PHPRest;

use Exception;
use \PHPRest\Constants\Config as Config;
use \PHPRest\Constants\Method as Method;

/**
 * Abstract API class that handles the requests and endpoint related functions
 *
 * @author Christopher Smit <christopher@mincdevelopment.co.za>
 * @copyright 2020 Minc Development (Pty) Ltd
 * @since Available since version 1.0
 */
	
abstract class CoreAPI 
{
    /**
     * @property method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';

    /**
     * @property endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $endpoint = '';

    /**
     * @property verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = '';

    /**
     * @property args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>/<arg1>
     */
    protected $args = Array();

    /**
     * @property headers
     * Headers passed with a request
     */
    protected $headers = Array();

    /**
     * @property body
     * Body passed with a request
     */
    protected $body = null;

    /**
     * @property file
     * Stores the input of the PUT request
     */
    protected $file = null;

    /**
     * Called when this class is initialised
     * Allow for CORS, assemble and pre-process the data
     */
    public function __construct($request) {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        if (array_key_exists('endpoint', $request)) {
            $this->endpoint = $request['endpoint'];
            unset($request['endpoint']);
        }
        
        if (array_key_exists('verb', $request)) {
            $this->verb = $request['verb'];
            unset($request['verb']);
        }
        
        $this->args = $request;

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == Method::METHOD_POST && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == Method::METHOD_DELETE) {
                $this->method = Method::METHOD_DELETE;
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == Method::METHOD_PUT) {
                $this->method = Method::METHOD_PUT;
            } else {
                throw new \Exception("Unexpected Header");
            }
        }

        $this->headers = $this->getallheaders(); //apache_request_headers();
        
        switch($this->method) {
        case Method::METHOD_DELETE:
            $this->request = $this->_cleanInputs($_GET);
            $this->file = file_get_contents("php://input");
        case Method::METHOD_POST:
            $this->request = $this->_cleanInputs($_POST);
            $this->body = file_get_contents("php://input");
            break;
        case Method::METHOD_GET:
            $this->request = $this->_cleanInputs($_GET);
            $this->body = file_get_contents("php://input");
            break;
        case Method::METHOD_PUT:
            $this->request = $this->_cleanInputs($_GET);
            $this->file = file_get_contents("php://input");
            break;
        default:
            $this->_response('Invalid Method', 405);
            break;
        }
    }
    
    public function processAPI() {
        if (method_exists($this, $this->endpoint)) {
            return $this->_response($this->{$this->endpoint}($this->args));
        }
        
        if (empty($this->endpoint)) {
            $this->endpoint = "{No Endpoint Specified}";
        }
        
        return $this->_response([Config::RESPONSE_STATUS_KEY => false, Config::RESPONSE_MESSAGE_KEY => "Endpoint not recognized: ".$this->endpoint], 404);
    }

    public function throwAPIError($message, $status) {
        $error = [
            Config::RESPONSE_STATUS_KEY => false,
            Config::RESPONSE_MESSAGE_KEY => $message
        ];

        if (Config::IS_SANDBOX) {
            $error["WARNING"] = Config::SANDBOX_WARNING_MESSAGE;
        }

        return $this->_response($error, $status);
    }

    private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        
        if (Config::IS_SANDBOX) {
            $error["WARNING"] = Config::SANDBOX_WARNING_MESSAGE;
        }
        
        return json_encode($data);
    }

    private function _cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function _requestStatus($code) {
        $status = [  
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            412 => 'Precondition Failed',
            415 => 'Unsupported Media Type',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
        ]; 
        return ($status[$code]) ? $status[$code] : $status[500]; 
    }

    private function getallheaders() {
        $headers = [];
        $copy_server = [
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        ];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }
        if (!isset($headers['Authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }
        return $headers;
    }

    protected function getBasicAuthCredentials() 
    {
        if (Config::BASIC_AUTHENTICATION_ENABLED) {
            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $username = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];
            } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic') === 0) {
                    list($username, $password) = explode(':', base64_decode($_SERVER['HTTP_AUTHORIZATION']));
                }
            } else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                list($username, $password) = explode(':' , base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));
            } else {
                throw new Exception('401 Unauthorized', 401);
            }
            return [$username, $password];
        } else return null;
    }
}