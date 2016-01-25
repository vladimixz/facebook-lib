<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Component for working with facebook API
 */
class FbLib extends Component{
    
    /**
     * id facebook application
     * @var string 
     */
    public $appId;
    
    /**
     * secret facebook application
     * @var string 
     */
    public $appSecret;
    
    /**
     * callback url
     * @var string 
     */
    public $redirectUri;
    
    /**
     * get Oauth Code
     * @return string
     */
    public function getOauthCode() {
        $url = 'https://www.facebook.com/dialog/oauth'
                . '?app_id=' . $this->appId
                . '&redirect_uri=' . $this->redirectUri
                . '&response_type=code'
                . '&scope=email';
        return Yii::$app->getResponse()->redirect($url);
    }
    
    /**
     * get Token
     * @param string $code
     * @return string
     */
    public function getToken($code) {
        $url = 'https://graph.facebook.com/oauth/access_token'
                . '?client_id=' . $this->appId
                . '&redirect_uri=' . $this->redirectUri
                . '&client_secret=' . $this->appSecret
                . '&code=' . $code;
        $contents = $this->sendQuery($url);
        if (json_decode($contents)) {
            throw new \Exception(json_decode($contents)->error->message);
        } else {
            $patern = ['/access_token=/', '/&expires=.*$/'];
            return preg_replace($patern, ['', ''], $contents);
        }
    }
    
    /**
     * get profile data
     * @param string $token
     * @return string
     */
    public function getData($token) {
        $url = 'https://graph.facebook.com/me'
                . '?fields=name,email'
                . '&access_token=' . $token;
        $contents = $this->sendQuery($url);
        $result = (array) json_decode($contents);
        unset($result['id']);
        return $result;
    }
    
    /**
     * get request
     * @param string $url
     * @return string
     */
    public function sendQuery($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $contents = curl_exec($ch);
        curl_close($ch);
        return $contents;
    }
}

