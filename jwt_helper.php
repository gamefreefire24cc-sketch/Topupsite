<?php
// FILE: jwt_helper.php
class JWT {
    // এই পাসওয়ার্ডটি আপনার ওয়েবসাইটের সিক্রেট। এটি কেউ জানবে না।
    private static $secret_key = 'OfferTopup_Secret_Key_2026_!@#$';

    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        $base64UrlHeader = self::base64url_encode($header);
        $base64UrlPayload = self::base64url_encode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64url_encode($signature);
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($token) {
        $tokenParts = explode('.', $token);
        if (count($tokenParts) != 3) return false;
        
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];

        $base64UrlHeader = self::base64url_encode($header);
        $base64UrlPayload = self::base64url_encode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        
        if (self::base64url_encode($signature) === $signature_provided) {
            return json_decode($payload, true);
        }
        return false;
    }
}
?>