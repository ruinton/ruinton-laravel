<?php


namespace Ruinton\Helpers\Sender;


class SMSSender
{
    protected $templateName = '';

    public function __construct()
    {
    }

    public function setTemplate($templateName)
    {
        $this->templateName = $templateName;
    }

    public static function Ghasedak($receptor, $template, $params) {
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
                    "apikey: db38122e42dca8b6d69cd712ceba9f11bbe4f9209ffe4e14d82e645fc23f5281",
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

    public function sendSMSWithGhasedakIO($receptor, $token)
    {
        $curl = curl_init();
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
                CURLOPT_POSTFIELDS => "type=1&receptor=$receptor&template=".$this->templateName."&param1=$token",
                CURLOPT_HTTPHEADER => array(
                    "apikey: db38122e42dca8b6d69cd712ceba9f11bbe4f9209ffe4e14d82e645fc23f5281",
                    "cache-control: no-cache",
                    "content-type: application/x-www-form-urlencoded",
                )
            )
        );
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
//             echo "cURL Error #:" . $err;
            return false;
        } else {
            return true;
        }
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
                    "apikey: db38122e42dca8b6d69cd712ceba9f11bbe4f9209ffe4e14d82e645fc23f5281",
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
