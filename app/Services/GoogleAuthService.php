<?php

namespace App\Services;

use Google_Client;
use Google_Service_Oauth2;
use App\Config\EnvLoader;
use Exception;

class GoogleAuthService
{
    private Google_Client $client;

    public function __construct()
    {
        // Ensure Env variables are loaded if not already
        if (empty($_ENV['GOOGLE_CLIENT_ID'])) {
            EnvLoader::load(__DIR__ . '/../../.env'); 
        }

        $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
        $redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';

        if (!$clientId || !$clientSecret || !$redirectUri) {
             throw new Exception("Google OAuth credentials not set.");
        }

        $this->client = new Google_Client();
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setRedirectUri($redirectUri);
        $this->client->addScope("email");
        $this->client->addScope("profile");
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function handleCallback(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            throw new Exception("Google Login failed: " . $token['error']);
        }
        $this->client->setAccessToken($token['access_token']);

        $google_oauth = new Google_Service_Oauth2($this->client);
        $info = $google_oauth->userinfo->get();

        return [
            'email' => $info->email,
            'google_id' => $info->id,
            'name' => $info->name,
            'picture' => $info->picture
        ];
    }
}
