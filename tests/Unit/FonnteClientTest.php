<?php

namespace Tests\Unit;

use App\Services\FonnteClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FonnteClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.fonnte.token' => 'test-token']);
    }

    public function test_sends_message_with_normalized_local_phone_number(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        $sent = (new FonnteClient)->sendMessage('081234567890', 'Halo warga');

        $this->assertTrue($sent);
        Http::assertSent(function ($request) {
            return $request->url() === config('services.fonnte.endpoint')
                && $request['target'] === '6281234567890'
                && $request['message'] === 'Halo warga'
                && $request->hasHeader('Authorization', 'test-token');
        });
    }

    public function test_normalizes_phone_number_already_in_international_format(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        (new FonnteClient)->sendMessage('+62 812-3456-7890', 'Halo');

        Http::assertSent(fn ($request) => $request['target'] === '6281234567890');
    }

    public function test_returns_false_and_skips_request_when_token_is_missing(): void
    {
        config(['services.fonnte.token' => null]);
        Http::fake();

        $sent = (new FonnteClient)->sendMessage('081234567890', 'Halo');

        $this->assertFalse($sent);
        Http::assertNothingSent();
    }

    public function test_returns_false_when_api_responds_with_an_error(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => false], 422)]);

        $sent = (new FonnteClient)->sendMessage('081234567890', 'Halo');

        $this->assertFalse($sent);
    }

    public function test_returns_false_when_the_api_is_unreachable(): void
    {
        Http::fake(['api.fonnte.com/*' => fn () => throw new ConnectionException('timed out')]);

        $sent = (new FonnteClient)->sendMessage('081234567890', 'Halo');

        $this->assertFalse($sent);
    }
}
