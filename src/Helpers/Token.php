<?php

namespace LeafyTech\Core\Helpers;

use LeafyTech\Core\Application;

class Token
{
    /**
     * Headers for JWT.
     *
     * @var array
     */
    private array $headers;

    /**
     * Secret for JWT.
     *
     * @var string
     */
    private string $secret;

    /**
     * Default Time to expire (seconds)
     *
     * @var int|float
     */
    private int $defaultExp = (60 * 60 * 8);

    /**
     * Errors of validations
     *
     * @var string
     */
    private string $message = '';


    public function __construct()
    {
        $this->headers = [
            'alg' => 'HS512',
            'typ' => 'JWT'
        ];

        $this->secret = Application::$app->config->app['key'];
    }

    /**
     * Generate JWT using a payload.
     *
     * @param array $payload
     * @return string
     */
    public function generate(array $payload): string
    {
        $headers        = self::encode(json_encode($this->headers));
        $payload["exp"] = time() + (new static)->defaultExp;
        $payload["nbf"] = time();

        $payload        = self::encode(json_encode($payload));
        $signature      = hash_hmac('SHA256', "$headers.$payload", $this->secret, true);
        $signature      = self::encode($signature);

        return "$headers.$payload.$signature";
    }

    /**
     * Check if JWT is valid, return true | false.
     *
     * @param string $jwt
     * @return boolean
     */
    public function isValid(string $jwt): bool
    {
        $token = explode('.', $jwt);

        if (!isset($token[1]) && !isset($token[2])) {
            $this->message = 'Header and payload is not set';
            return false;
        }

        $headers         = base64_decode($token[0]);
        $payload         = base64_decode($token[1]);
        $clientSignature = $token[2];

        if (!json_decode($payload)) {
            $this->message = 'Cannot convert access token to JSON';
            return false;
        }

        if ((json_decode($payload)->exp - time()) < 0) {
            $this->message = 'Token expired';
            return false;
        }

        if (!isset(json_decode($payload)->iss)) {
            $this->message = 'Invalid token';
            return false;
        }

        if (!isset(json_decode($payload)->aud)) {
            $this->message = 'Invalid token';
            return false;
        }

        $base64_header = self::encode($headers);
        $base64_payload = self::encode($payload);

        $signature = hash_hmac('SHA256', $base64_header . "." . $base64_payload, $this->secret, true);
        $base64_signature = self::encode($signature);

        if($base64_signature === $clientSignature) {
            return true;
        } else {
            $this->message = 'Invalid token';
            return false;
        }

    }

    /**
     * @param array $headers
     * @return Token
     */
    public function setHeaders(array $headers): Token
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param int $defaultExp
     * @return Token
     */
    public function setDefaultExp(int $defaultExp): Token
    {
        $this->defaultExp = $defaultExp;
        return $this;
    }

    /**
     * Encode JWT using base64.
     *
     * @param string $str
     * @return string
     */
    protected static function encode(string $str): string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}