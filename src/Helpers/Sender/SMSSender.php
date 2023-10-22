<?php


namespace Ruinton\Helpers\Sender;

use Exception;

class SMSSender
{
    protected $templateName = '';
    protected $apiKey = '';

    public function __construct()
    {
        $this->apiKey = env('SMS_API_KEY', '');
    }

    public function setTemplate($templateName)
    {
        $this->templateName = $templateName;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public static function Ghasedak($receptor, $template, $params, $apiKey) {
        $curl = curl_init();
        $paramString = '';
        foreach ($params as $key => $param) {
            $paramString .= '&param'.($key+1).'='.($param);
        }
        curl_setopt_array($curl,
            array(
                CURLOPT_URL => "https://api.ghasedak.me/v2/verification/send/simple ",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
//                CURLOPT_HTTPS_VERSION => CURL_https_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "type=1&receptor=$receptor&template=".$template.$paramString,
                CURLOPT_HTTPHEADER => array(
                    "apikey: ".$apiKey,
                    "cache-control: no-cache",
                    "content-type: application/x-www-form-urlencoded",
                )
            )
        );
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response = json_decode($response, true);
            if($response['result']['code'] === 200) {
                return true;
            }
        }
        return false;
    }

    public function sendSMSWithGhasedakIO($receptor, $token, ...$params)
    {
        $curl = curl_init();
        $paramString = '';
        foreach ($params as $key => $param) {
            $paramString .= '&param'.($key+2).'='.(str_replace (' ', ' ', $param));
        }
        $token = str_replace ( ' ', ' ', $token);
        curl_setopt_array($curl,
            array(
                CURLOPT_URL => "https://api.ghasedak.me/v2/verification/send/simple ",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_HTTPS_VERSION => CURL_https_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "type=1&receptor=$receptor&template=".$this->templateName."&param1=$token".$paramString,
                CURLOPT_HTTPHEADER => array(
                    "apikey: ".$this->apiKey,
                    "cache-control: no-cache",
                    "content-type: application/x-www-form-urlencoded",
                )
            )
        );
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response = json_decode($response, true);
            // try {
                if($response['result']['code'] === 200) {
                    return true;
                }
            // } catch(Exception $e) {}
        }
        return false;
    }

    public function sendSimpleSMSWithGhasedakIO($receptor, $lineNumber, $message, $id)
    {
        $curl = curl_init();
        curl_setopt_array($curl,
            array(
                CURLOPT_URL => "https://api.ghasedak.me/v2/sms/send/simple ",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_HTTPS_VERSION => CURL_https_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "message=$message&receptor=$receptor&linenumber=".$lineNumber."&checkid=".$id,
                CURLOPT_HTTPHEADER => array(
                    "apikey: ".$this->apiKey,
                    "cache-control: no-cache",
                    "content-type: application/x-www-form-urlencoded",
                )
            )
        );
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            print_r($response);
            $response = json_decode($response, true);
            try {
                if($response['result']['code'] === 200) {
                    return true;
                }
            } catch(Exception $e) {}
        }
        return false;
    }

    public function sendVoiceWithGhasedakIO($receptor, $message)
    {
        $curl = curl_init();
        curl_setopt_array($curl,
            array(
                CURLOPT_URL => "https://api.ghasedak.me/v2/voice/send/simple ",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "message=$message&receptor=$receptor",
                CURLOPT_HTTPHEADER => array(
                    "apikey: ".$this->apiKey,
                    "cache-control: no-cache",
                    "content-type: application/x-www-form-urlencoded",
                )
            )
        );
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
             echo "cURL Error #:" . $err;
            return false;
        } else {
            echo "Response:" . $response;
            return true;
        }
    }
}
