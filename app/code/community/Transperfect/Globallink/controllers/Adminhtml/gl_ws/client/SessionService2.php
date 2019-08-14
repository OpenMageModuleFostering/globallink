<?php
/* © 2013 Translations.com, a TransPerfect company 
 * Translations.com, Inc., its affiliates and its licensors (collectively, “Translations.com”) own all right, title and interest, 
 * including but not limited to all intellectual property rights, in and to this software and all associated documentation, updates, 
 * new releases and work product, if any (collectively, the “Software”), in both object code and source code formats.  
 * 
 * By making use of the Software, you agree 
 * (i)	to reproduce the copyright, trademark and other proprietary notices contained on or in the Software as delivered to you (including this Notice)
 *      on any reproductions you cause to be made of the Software and further agree not to remove any such notices (including this Notice) from the Software or any copies thereof; 
 * (ii) the Software shall not be further licensed, sold or otherwise transferred by you, except if otherwise approved in writing by an authorized Translations.com representative; 
 * (iii) not to release the results of any benchmark testing of the Software or use any trademark, logo or proprietary notice of Translations.com (except as required by this Notice or law) without Translations.com’s prior written approval; and 
 * (iv) you shall not modify, change nor create any derivative works of the Software.  
 * Any derivative works of the Software created by you, your employees, agents, or contractors, including any and all modifications or changes to the Software, in any format whatsoever, shall be the exclusive property of Translations.com.
*/

if (!class_exists("login")) {

    class login {

        public $username; // string
        public $password; // string

    }

}

if (!class_exists("loginResponse")) {

    class loginResponse {

        public $return; // string

    }

}

if (!class_exists("logout")) {

    class logout {

        public $userId; // string

    }

}

if (!class_exists("logoutResponse")) {

    class logoutResponse {

        public $return; // string

    }

}

/**
 * SessionService2 class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class SessionService2 extends SoapClient {

    private static $classmap = array(
        'login' => 'login',
        'loginResponse' => 'loginResponse',
        'logout' => 'logout',
        'logoutResponse' => 'logoutResponse',
    );

    public function SessionService2($wsdl = "http://localhost:8080/pd4/services/SessionService2?wsdl", $options = array()) {
        foreach (self::$classmap as $key => $value) {
            if (!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }
        parent::__construct($wsdl, $options);
    }

    /**
     *  
     *
     * @param logout $parameters
     * @return logoutResponse
     */
    public function logout(logout $parameters) {
        return $this->__soapCall('logout', array($parameters), array(
            'uri' => 'http://impl.services2.service.ws.projectdirector.gs4tr.org',
            'soapaction' => ''
                )
        );
    }

    /**
     *  
     *
     * @param login $parameters
     * @return loginResponse
     */
    public function login(login $parameters) {
        return $this->__soapCall('login', array($parameters), array(
            'uri' => 'http://impl.services2.service.ws.projectdirector.gs4tr.org',
            'soapaction' => ''
                )
        );
    }

}

?>
