<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\BakongService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use KHQR\BakongKHQR;
use Tests\TestCase;

class BakongPaymentTest extends TestCase
{
    use RefreshDatabase;

    private const CHECK_URL = 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5';

    protected User $user;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'bakong.account_id' => 'test_merchant@bkrt',
            'bakong.token' => 'test-token',
            'bakong.base_url' => 'https://api-bakong.nbc.gov.kh',
            'bakong.merchant_name' => 'BookVerse Test',
            'bakong.currency' => 'USD',
            'bakong.qr_lifetime' => 15,
            'bakong.allow_simulation' => true,
        ]);

        $this->user = $this->makeUser('customer@example.com');
        $this->order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-BK-' . strtoupper(substr(uniqid(), -6)),
            'subtotal' => 25.00,
            'delivery_fee' => 1.50,
            'discount' => 0.00,
            'total_amount' => 26.50,
            'status' => 'pending',
            'shipping_address' => '123 Norodom Blvd, Phnom Penh',
            'delivery_method' => 'Standard Courier',
        ]);
    }

    private function makeUser(string $email): User
    {
        return User::create([
            'name' => 'John Customer',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'active',
        ]);
    }

    private function makePendingPayment(): Payment
    {
        return Payment::create([
            'order_id' => $this->order->id,
            'transaction_id' => 'TXN-BAKONG-ABC123',
            'payment_method' => 'BAKONG_KHQR',
            'bank_provider' => 'BAKONG',
            'amount' => 26.50,
            'currency' => 'USD',
            'status' => 'pending',
            'qr_string' => '000201...',
            'md5_hash' => 'd41d8cd98f00b204e9800998ecf8427e',
        ]);
    }

    private function fakeBakongPaid(float $amount = 26.50, string $toAccount = 'test_merchant@bkrt'): void
    {
        Http::fake([
            self::CHECK_URL => Http::response([
                'responseCode' => 0,
                'responseMessage' => 'Getting transaction successfully.',
                'errorCode' => null,
                'data' => [
                    'hash' => 'abc',
                    'fromAccountId' => 'payer@aclb',
                    'toAccountId' => $toAccount,
                    'currency' => 'USD',
                    'amount' => $amount,
                ],
            ], 200),
        ]);
    }

    private function fakeBakongNotFound(): void
    {
        Http::fake([
            self::CHECK_URL => Http::response([
                'responseCode' => 1,
                'responseMessage' => 'Transaction could not be found. Please check and try again.',
                'errorCode' => 1,
                'data' => null,
            ], 200),
        ]);
    }

    public function test_generated_khqr_is_valid_and_carries_expiry()
    {
        Http::fake(['*' => Http::response([], 500)]); // account lookup + deeplink unavailable

        $result = app(BakongService::class)->generateKhqr($this->order);

        $this->assertTrue(BakongKHQR::verify($result['qr_string'])->isValid);
        $this->assertSame(md5($result['qr_string']), $result['md5']);

        $decoded = BakongKHQR::decode($result['qr_string'])->data;
        $this->assertSame('test_merchant@bkrt', $decoded['bakongAccountID']);
        $this->assertSame('26.50', $decoded['transactionAmount']);
        $this->assertSame('840', $decoded['transactionCurrency']);

        // Tag 99: 00 = created ms, 01 = expires ms, 15 minutes apart
        $this->assertMatchesRegularExpression('/^0013(\d{13})0113(\d{13})$/', $decoded['timestamp']);
        preg_match('/^0013(\d{13})0113(\d{13})$/', $decoded['timestamp'], $m);
        $this->assertEqualsWithDelta(15 * 60 * 1000, (int) $m[2] - (int) $m[1], 2000);
    }

    public function test_generate_qr_stores_pending_bakong_payment()
    {
        Http::fake(['*' => Http::response([], 500)]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/payments/generate-qr', ['order_id' => $this->order->id]);

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'data' => [
                'bank_provider' => 'BAKONG',
                'amount' => 26.5,
                'bakong_account_id' => 'test_merchant@bkrt',
                'lifetime_minutes' => 15,
                'can_simulate' => true,
            ],
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'payment_method' => 'BAKONG_KHQR',
            'bank_provider' => 'BAKONG',
            'status' => 'pending',
            'md5_hash' => $response->json('data.md5'),
        ]);
    }

    public function test_generate_qr_rejects_paid_order()
    {
        $this->makePendingPayment()->update(['status' => 'paid', 'paid_at' => now()]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/payments/generate-qr', ['order_id' => $this->order->id])
            ->assertStatus(409)
            ->assertJson(['status_code' => 'ALREADY_PAID']);
    }

    public function test_generate_qr_rejects_another_users_order()
    {
        $this->actingAs($this->makeUser('other@example.com'), 'sanctum')
            ->postJson('/api/v1/payments/generate-qr', ['order_id' => $this->order->id])
            ->assertStatus(404);
    }

    public function test_check_status_marks_paid_when_bakong_confirms()
    {
        $this->makePendingPayment();
        $this->fakeBakongPaid();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/payments/{$this->order->id}/check-status")
            ->assertStatus(200)
            ->assertJson(['is_paid' => true, 'status' => 'paid']);

        Http::assertSent(fn ($r) => $r->url() === self::CHECK_URL
            && $r['md5'] === 'd41d8cd98f00b204e9800998ecf8427e'
            && $r->hasHeader('Authorization', 'Bearer test-token'));

        $this->assertDatabaseHas('payments', ['order_id' => $this->order->id, 'status' => 'paid']);
        $this->assertDatabaseHas('orders', ['id' => $this->order->id, 'status' => 'processing']);
    }

    public function test_check_status_pending_when_transaction_not_found()
    {
        $this->makePendingPayment();
        $this->fakeBakongNotFound();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/payments/{$this->order->id}/check-status")
            ->assertStatus(200)
            ->assertJson(['is_paid' => false]);

        $this->assertDatabaseHas('payments', ['order_id' => $this->order->id, 'status' => 'pending']);
    }

    public function test_check_status_rejects_wrong_amount()
    {
        $this->makePendingPayment();
        $this->fakeBakongPaid(1.00);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/payments/{$this->order->id}/check-status")
            ->assertStatus(400)
            ->assertJson(['is_paid' => false, 'status' => 'verification_failed']);

        $this->assertDatabaseHas('payments', ['order_id' => $this->order->id, 'status' => 'pending']);
    }

    public function test_check_status_rejects_payment_to_other_account()
    {
        $this->makePendingPayment();
        $this->fakeBakongPaid(26.50, 'someone_else@bkrt');

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/payments/{$this->order->id}/check-status")
            ->assertStatus(400);

        $this->assertDatabaseHas('payments', ['order_id' => $this->order->id, 'status' => 'pending']);
    }

    public function test_simulate_success_only_for_own_order()
    {
        $this->makePendingPayment();

        $this->actingAs($this->makeUser('other@example.com'), 'sanctum')
            ->postJson("/api/v1/payments/{$this->order->id}/simulate-success")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/payments/{$this->order->id}/simulate-success")
            ->assertStatus(200);

        $this->assertDatabaseHas('payments', ['order_id' => $this->order->id, 'status' => 'paid']);
    }

    public function test_simulate_success_disabled_when_config_off()
    {
        config(['bakong.allow_simulation' => false]);
        $this->makePendingPayment();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/payments/{$this->order->id}/simulate-success")
            ->assertStatus(403);

        $this->assertDatabaseHas('payments', ['order_id' => $this->order->id, 'status' => 'pending']);
    }

    public function test_store_cannot_mark_bakong_payment_paid_directly()
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/payments', ['order_id' => $this->order->id, 'payment_method' => 'BAKONG_KHQR'])
            ->assertStatus(422);
    }
}
