<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ShopeeApiService
{
    protected $partnerId;
    protected $partnerKey;
    protected $redirectUrl;
    protected $baseUrl;

    public function __construct()
    {
        $this->partnerId = config('services.shopee.partner_id');
        $this->partnerKey = config('services.shopee.partner_key');
        $this->redirectUrl = config('services.shopee.redirect_url') . '/dev';
        $this->baseUrl = 'https://partner.test-stable.shopeemobile.com';
    }

    // Step 1: Generate redirect URL for OAuth
    public function getAuthUrl()
    {
        $timestamp = time();
        $path = '/api/v2/shop/auth_partner';
        $baseString = $this->partnerId . $path . $timestamp;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);

        $redirect = urlencode($this->redirectUrl);
        return "{$this->baseUrl}{$path}?partner_id={$this->partnerId}&timestamp={$timestamp}&sign={$sign}&redirect={$redirect}";
    }

    // Step 2: Exchange code to access_token (callback)
    public function getAccessToken($code, $shop_id)
    {
        $timestamp = time();
        $path = '/api/v2/auth/token/get';
        $body = [
            'code' => $code,
            'shop_id' => (int) $shop_id,
            'partner_id' => (int) $this->partnerId,
        ];

        $baseString = $this->partnerId . $path . $timestamp;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);

        $url = $this->baseUrl . $path . "?partner_id={$this->partnerId}&timestamp={$timestamp}&sign={$sign}";

        $response = Http::post($url, $body);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Shopee getAccessToken failed: " . $response->body());
    }

    // Step 3: Refresh access_token (if needed)
    public function refreshAccessToken($refresh_token, $shop_id)
    {
        $timestamp = time();
        $path = '/api/v2/auth/access_token/get';
        $body = [
            'refresh_token' => $refresh_token,
            'shop_id' => (int) $shop_id,
            'partner_id' => (int) $this->partnerId,
        ];

        $baseString = $this->partnerId . $path . $timestamp;
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);

        $url = $this->baseUrl . $path . "?partner_id={$this->partnerId}&timestamp={$timestamp}&sign={$sign}";

        $response = Http::post($url, $body);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Shopee refreshAccessToken failed: " . $response->body());
    }

    public function getValidAccessToken($marketplaceShop)
    {
        // $marketplaceShop = instance MarketplaceShop (model)
        $now = now();

        // Jika access_token masih valid (lebih dari 2 menit sebelum expiry), langsung pakai
        if ($marketplaceShop->access_token && $marketplaceShop->token_expired_at && $now->lt($marketplaceShop->token_expired_at->subMinutes(2))) {
            return $marketplaceShop->access_token;
        }

        // Jika tidak, refresh token ke Shopee
        $result = $this->refreshAccessToken($marketplaceShop->refresh_token, $marketplaceShop->shop_id);

        // Shopee response: access_token, refresh_token, expire_in
        $marketplaceShop->access_token = $result['access_token'];
        $marketplaceShop->refresh_token = $result['refresh_token'];
        $marketplaceShop->token_expired_at = now()->addSeconds($result['expire_in']);
        $marketplaceShop->save();

        return $result['access_token'];
    }


    // Step 4: Get shop info (requires valid access_token)
    public function getShopInfo($access_token, $shop_id)
    {
        $timestamp = time();
        $path = '/api/v2/shop/get_shop_info';
        $sign = hash_hmac('sha256', $this->partnerId . $path . $timestamp . $access_token . $shop_id, $this->partnerKey);

        $url = $this->baseUrl . $path .
            "?partner_id={$this->partnerId}" .
            "&timestamp={$timestamp}" .
            "&sign={$sign}" .
            "&shop_id={$shop_id}" .
            "&access_token={$access_token}";

        $response = Http::get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Shopee getShopInfo failed: " . $response->body());
    }
}
