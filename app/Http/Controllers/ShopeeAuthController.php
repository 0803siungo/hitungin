<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\MarketplaceShop;
use App\Services\ShopeeApiService;

class ShopeeAuthController extends Controller
{
    public function redirect()
    {
        $shopee = new ShopeeApiService();
        return redirect()->away($shopee->getAuthUrl());
    }

    public function shopeeCallback(Request $request)
    {
        $shopee = new ShopeeApiService();

        $code = $request->get('code');
        $shop_id = $request->get('shop_id');

        $tokenData = $shopee->getAccessToken($code, $shop_id);
        Log::info('tokenData', $tokenData);

        // Simpan access_token, refresh_token, expired_at ke DB
        $shop = MarketplaceShop::where('shop_id', $shop_id)
            ->first();

        if ($shop) {
            $shop->update([
                'access_token' =>
                    $tokenData['access_token'],
                'refresh_token' =>
                    $tokenData['refresh_token'],
                'expired_at' =>
                    $tokenData['expired_at']
            ]);
        } else {
            $shop = new MarketplaceShop();
            $shop->fill([
                'platform' =>
                    'Shopee',
                'shop_id' =>
                    $shop_id,
                'access_token' =>
                    $tokenData['access_token'],
                'refresh_token' =>
                    $tokenData['refresh_token'],
                'token_expired_at' =>
                    Carbon::now()->addHours(3),
            ]);
        }
        // Lalu get shop info
        $shopInfo = $shopee->getShopInfo($tokenData['access_token'], $shop_id);
        Log::info('shopInfo', $shopInfo);

        // Update ke tabel marketplace_shops (shop_id, shop_name, dll)
        $shop->fill([
            'shop_name' => $shopInfo['shop_name']
        ]);

        $shop->save();

    }
}
