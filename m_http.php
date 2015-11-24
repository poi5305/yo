<?php

class Http {

    const API_KEY = "AIzaSyDdO-34eZuBuZlVLavpsQcyD6NRmLgJ6IE";
    const GCM_URL = "https://gcm-http.googleapis.com/gcm/send";

    public function send($to, $msg) {
        $headers = array();
        $headers[] = "Content-Type:application/json";
        $headers[] = "Authorization:key=" . self::API_KEY;

        $posts = array(
            "to" => $to,
            "data" => array(
                "msg" => $msg
            )
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, self::GCM_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($posts));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}

//$test = new Http();
//$test->send("token", "test msg");

?>