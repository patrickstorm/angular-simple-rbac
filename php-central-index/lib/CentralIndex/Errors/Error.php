<?php
    class CentralIndex_Error extends Exception{
        protected $rawBody;
        protected $jsonBody;

        public function __construct($message=null, $code=null, $rawBody=null, $jsonBody=null){
            if(is_array($message)){
                $message = json_encode($message);
            }
            parent::__construct($message,$code);
            $this->rawBody = $rawBody;
            $this->jsonBody = $jsonBody;
        }

        public function getRawBody(){
            return $this->rawBody;
        }

        public function getJsonBody(){
            return $this->jsonBody;
        }

        public function getValidations(){
            return (array_key_exists('validations', $this->jsonBody)?$this->jsonBody['validations']:false);
        }

        public function getErrorMessage(){
            return (array_key_exists('message', $this->jsonBody)?$this->jsonBody['message']:false);
        }

        public function getError(){
            return (array_key_exists('error', $this->jsonBody)?$this->jsonBody['error']:false);
        }

        public function getDebug(){
            return (array_key_exists('debug', $this->jsonBody)?$this->jsonBody['debug']:false);
        }

    }