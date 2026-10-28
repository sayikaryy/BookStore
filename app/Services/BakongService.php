<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BakongService
{
    private string $accountId;
    private string $token;
    private string $baseUrl;
    private string $merchantName;
    private string $merchantCity;
    private string $currency;
    private int $qrLifetime;

    public function __construct()
    {
        $this->ensureAutoloaderRegistered();

        $this->accountId = config('bakong.account_id', 'vathana_sieng@bkrt');
        $this->token = config('bakong.token', '');
        $this->baseUrl = rtrim(config('bakong.base_url', 'https://api-bakong.nbc.gov.kh'), '/');
        $this->merchantName = config('bakong.merchant_name', 'BookVerse Cambodia');
        $this->merchantCity = config('bakong.merchant_city', 'Phnom Penh');
        $this->currency = config('bakong.currency', 'USD');
        $this->qrLifetime = max(1, (int) config('bakong.qr_lifetime', 15));
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    /**
     * Rewrite the KHQR timestamp tag (99) to carry both creation (00) and
     * expiration (01) timestamps in milliseconds, then recompute the CRC.
     * The bundled library only writes the creation timestamp.
     */
    public function withExpiration(string $qrString, int $expiresAtMs): string
    {
        // Drop the trailing CRC: tag 63, length 04, 4 hex chars
        $body = substr($qrString, 0, -8);

        // Keep every top-level tag except the existing 99
        $pos = 0;
        $withoutTimestamp = '';
        while ($pos < strlen($body)) {
            $tag = substr($body, $pos, 2);
            $len = (int) substr($body, $pos + 2, 2);
            if ($tag !== '99') {
                $withoutTimestamp .= substr($body, $pos, 4 + $len);
            }
            $pos += 4 + $len;
        }

        $created = (string) (int) floor(microtime(true) * 1000);
        $expires = (string) $expiresAtMs;
        $value = '00' . sprintf('%02d', strlen($created)) . $created
            . '01' . sprintf('%02d', strlen($expires)) . $expires;

        $khqr = $withoutTimestamp . '99' . sprintf('%02d', strlen($value)) . $value . '6304';

        return $khqr . \KHQR\Helpers\Utils::crc16($khqr);
    }

    /**
     * Ensure the pure PHP Bakong KHQR classes are autoloaded.
     */
    private function ensureAutoloaderRegistered(): void
    {
        if (!class_exists(\KHQR\BakongKHQR::class)) {
            spl_autoload_register(function ($class) {
                $prefix = 'KHQR\\';
                $baseDir = base_path('vendor/khqr-gateway/bakong-khqr-php/src/');
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) {
                    return;
                }
                $relativeClass = substr($class, $len);
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                }
            });
        }
    }

    /**
     * Resolve the registered KYC account holder name from NBC Bakong.
     * Caches the verified name for 24 hours.
     */
    public function getAccountHolderName(): string
    {
        try {
            return Cache::remember("bakong_holder_{$this->accountId}", 86400, function () {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->token}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post("{$this->baseUrl}/v1/check_bakong_account", [
                        'accountId' => $this->accountId,
                    ]);

                if ($response->successful()) {
                    $fullName = $response->json('data.fullName');
                    if (!empty($fullName)) {
                        return $fullName;
                    }
                }
                return config('bakong.merchant_name', 'vathana Sieng');
            });
        } catch (\Throwable $e) {
            return config('bakong.merchant_name', 'vathana Sieng');
        }
    }

    /**
     * Generate dynamic Bakong KHQR and deep link for an Order.
     *
     * @param Order $order
     * @param string $bankProvider
     * @return array
     */
    public function generateKhqr(Order $order, string $bankProvider = 'BAKONG'): array
    {
        $amount = (float)$order->total_amount;
        $currencyCode = strtoupper($this->currency) === 'KHR'
            ? \KHQR\Helpers\KHQRData::CURRENCY_KHR
            : \KHQR\Helpers\KHQRData::CURRENCY_USD;

        // Individual Tag 29 requires the exact KYC registered name of the account holder
        $accountHolderName = $this->getAccountHolderName();

        // Build individual KHQR specification (clean EMVCo without unsupported store/terminal tags)
        $individualInfo = \KHQR\Models\IndividualInfo::withOptionalArray(
            $this->accountId,
            $accountHolderName,
            $this->merchantCity,
            [
                'currency' => $currencyCode,
                'amount' => $amount,
            ]
        );

        $khqrResponse = \KHQR\BakongKHQR::generateIndividual($individualInfo);
        $qrData = $khqrResponse->data;

        $expiresAt = now()->addMinutes($this->qrLifetime);
        $qrString = $this->withExpiration($qrData['qr'] ?? '', (int) $expiresAt->getPreciseTimestamp(3));
        $md5Hash = md5($qrString);

        // Generate Deep Link via Bakong Open API
        $deepLink = null;
        try {
            $sourceInfo = new \KHQR\Models\SourceInfo(
                config('bakong.app_icon_url', 'https://bakong.nbc.gov.kh/images/logo.svg'),
                config('bakong.app_name', 'BookVerse'),
                config('bakong.app_deeplink_callback', 'bookverse://payment/complete')
            );
            $deepLinkResponse = \KHQR\BakongKHQR::generateDeepLink($qrString, $sourceInfo, false);
            if (is_object($deepLinkResponse->data) && !empty($deepLinkResponse->data->shortLink)) {
                $deepLink = $deepLinkResponse->data->shortLink;
            } elseif (is_array($deepLinkResponse->data) && !empty($deepLinkResponse->data['shortLink'])) {
                $deepLink = $deepLinkResponse->data['shortLink'];
            }
        } catch (\Throwable $e) {
            Log::warning('Bakong deep link generation failed: ' . $e->getMessage());
        }

        return [
            'qr_string' => $qrString,
            'md5' => $md5Hash,
            'deeplink' => $deepLink,
            'amount' => $amount,
            'currency' => $this->currency,
            'bank_provider' => $bankProvider,
            'merchant_name' => $accountHolderName,
            'account_id' => $this->accountId,
            'lifetime_minutes' => $this->qrLifetime,
            'expired_at' => $expiresAt->toIso8601String(),
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ];
    }

    /**
     * Check transaction status on NBC Bakong Open API using the MD5 hash.
     *
     * @param string $md5
     * @return array
     */
    public function checkTransactionByMd5(string $md5): array
    {
        $endpoint = "{$this->baseUrl}/v1/check_transaction_by_md5";

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, [
                    'md5' => $md5,
                ]);

            if ($response->successful()) {
                $json = $response->json();
                $responseCode = $json['responseCode'] ?? null;

                // responseCode 0 = Successful transaction found and settled
                if ($responseCode === 0) {
                    return [
                        'success' => true,
                        'is_paid' => true,
                        'status' => 'paid',
                        'message' => 'Payment received and confirmed by Bakong',
                        'data' => $json['data'] ?? null,
                    ];
                }

                $errorCode = $json['errorCode'] ?? null;
                if ($errorCode === 17) {
                    return [
                        'success' => true,
                        'is_paid' => false,
                        'status' => 'rate_limited',
                        'message' => 'Bakong sandbox limit reached (100 req/day). Try manual simulation or test tomorrow.',
                        'data' => null,
                    ];
                }

                // responseCode 1 = Transaction not found / not yet paid
                return [
                    'success' => true,
                    'is_paid' => false,
                    'status' => 'pending',
                    'message' => $json['responseMessage'] ?? 'Transaction pending payment',
                    'data' => null,
                ];
            }

            Log::error('Bakong check_transaction_by_md5 failed HTTP response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'is_paid' => false,
                'status' => 'unknown',
                'message' => 'Bakong API request failed: ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('Bakong check_transaction_by_md5 exception: ' . $e->getMessage());

            return [
                'success' => false,
                'is_paid' => false,
                'status' => 'error',
                'message' => 'Failed to reach Bakong Open API: ' . $e->getMessage(),
            ];
        }
    }
}
