<?php
/**
 * JWT Token Handler
 */

class JWT {
    
    public static function encode($payload) {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRY;
        
        $header_encoded = self::base64UrlEncode(json_encode($header));
        $payload_encoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', 
            $header_encoded . '.' . $payload_encoded,
            JWT_SECRET,
            true
        );
        $signature_encoded = self::base64UrlEncode($signature);
        
        return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
    }
    
    public static function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
        
        $signature = hash_hmac('sha256',
            $header_encoded . '.' . $payload_encoded,
            JWT_SECRET,
            true
        );
        $signature_calc = self::base64UrlEncode($signature);
        
        if ($signature_calc !== $signature_encoded) {
            return false;
        }
        
        $payload = json_decode(self::base64UrlDecode($payload_encoded), true);
        
        if (!$payload || isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }
}

?>