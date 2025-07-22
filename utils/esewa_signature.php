<?php


class EsewaSignature {

    private static $secretKey = '8gBm/:&EnhH.1/q';

    public static function generateSignature($data, $signed_field_names) {
        $fields = explode(',', $signed_field_names);
        $signing_string = '';
        foreach ($fields as $i => $field) {
            $field = trim($field);
            if (!isset($data[$field])) return false;
            if ($i > 0) $signing_string .= ',';
            $signing_string .= $field . '=' . $data[$field];
        }
        $hash = hash_hmac('sha256', $signing_string, self::$secretKey, true);
        $signature = base64_encode($hash);
        // Debug
        error_log('eSewa signing string: ' . $signing_string);
        error_log('eSewa generated signature: ' . $signature);
        return $signature;
    }

    // Verify signature
    public static function verifySignature($data, $signed_field_names, $signature) {
        $expected = self::generateSignature($data, $signed_field_names);
        return hash_equals($expected, $signature);
    }
} 