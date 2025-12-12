<?php

namespace App\Models;


class EnvatoApi
{
   
    function verifyPurchaseCode($code) {

        $data['purchase_key'] = $code;
        $data['auth_token'] = 'spantiklab_api_54321';
        $url = 'https://envatoapikey.spantiklab.com/verify_key/send_key';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return $response = curl_exec($ch);
          
        //$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // if (curl_errno($ch)) {
        //     throw new \Exception(curl_error($ch));
        // }

        curl_close($ch);

        if ($response == 'success') {
            return $response;

        } else {
            return $response;
            // throw new \Exception("HTTP Error Code : {$httpCode}");
        }
    }
}
