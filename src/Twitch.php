<?php

namespace Twitch;

use \InvalidArgumentException;

class Twitch
{
    /**
     * This is your clientID & secret
     */
    protected static $api_key;
    protected static $api_secret;
    protected static $version = '3';

    /**
     * This connections scope
     */
    public static $scope;

    const baseURI = "https://api.twitch.tv/kraken/";

    /**
     * This is the token that is returned when a user authenticates
     * through the OAuth2 method.
     */
    protected static $access_token;

    public static function getClientId()
    {
        return static::$api_key;
    }

    public static function setClientId($api_key)
    {
        if (!is_string($api_key)) {
            throw new InvalidArgumentException("setClientId only accepts strings.");
        }
        static::$api_key = $api_key;
    }

    public static function getClientSecret()
    {
        return static::$api_secret;
    }

    public static function setClientSecret($api_secret)
    {
        if (!is_string($api_secret)) {
            throw new InvalidArgumentException("setClientSecret only accepts strings.");
        }
        static::$api_secret = $api_secret;
    }

    public static function getAccessToken()
    {
        return static::$access_token;
    }

    /**
     * You obtain an access token when you follow the authentication process
     * https://github.com/justintv/Twitch-API/blob/master/authentication.md
     */
    public static function setAccessToken($access_token, $client = null)
    {
        if (!is_string($access_token)) {
            throw new InvalidArgumentException("setAccessToken only accepts strings.");
        }

        static::$access_token = $access_token;


        $headers = [
            'Client-ID' => Twitch::getClientId(),
            'Accept' => 'application/vnd.twitchtv.v' . Twitch::version() . '+json',
            'Authorization' => 'OAuth ' . Twitch::getAccessToken()
        ];

        $client = $client ?: (new \GuzzleHttp\Client([
            'headers' => $headers,
            'base_uri' => Twitch::baseURI
        ]));

        $request = $client->request('GET');

        $access_token_validation = json_decode($request->getBody());

        if (!$access_token_validation->token->valid) {
            throw new \Exception("This access token is not valid. Please confirm you have authorized the user correctly.", 401);
        }

        static::$scope = new Scope;
        static::$scope->addScopes($access_token_validation->token->authorization->scopes);
    }

    public static function version($version = null)
    {
        if (!empty($version)) {
            self::$version = $version;
        }

        return self::$version;
    }
}
