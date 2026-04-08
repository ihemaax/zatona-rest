<?php

namespace Tests\Unit\Services;

use App\Services\WpSenderXService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WpSenderXServiceTest extends TestCase
{
    public function test_phone_normalization_from_ten_digits_to_plus_twenty_format(): void
    {
        $service = app(WpSenderXService::class);

        $this->assertSame('+201200000000', $service->normalizePhone('1200000000'));
        $this->assertSame('+201200000000', $service->normalizePhone('+201200000000'));
    }

    public function test_verify_otp_uses_same_international_phone_format(): void
    {
        config([
            'services.wpsenderx.enabled' => true,
            'services.wpsenderx.api_key' => 'test-key',
            'services.wpsenderx.base_url' => 'https://backendapi.wpsenderx.com/api',
        ]);

        Http::fake([
            'backendapi.wpsenderx.com/api/otp/verify' => Http::response(['status' => 'success'], 200),
        ]);

        $service = app(WpSenderXService::class);
        $result = $service->verifyOtp('1200000000', '123456');

        $this->assertTrue((bool) ($result['ok'] ?? false));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://backendapi.wpsenderx.com/api/otp/verify'
                && $request['recipient'] === '+201200000000'
                && $request['otp_code'] === '123456';
        });
    }
}
