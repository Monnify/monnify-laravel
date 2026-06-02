# Monnify Laravel

[![Tests](https://github.com/Monnify/monnify-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/Monnify/monnify-laravel/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/Monnify/monnify-laravel/branch/main/graph/badge.svg)](https://codecov.io/gh/Monnify/monnify-laravel)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/monnify/monnify-laravel.svg)](https://packagist.org/packages/monnify/monnify-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/monnify/monnify-laravel.svg)](https://packagist.org/packages/monnify/monnify-laravel)

A Laravel package for integrating the [Monnify](https://monnify.com) payment gateway into your Laravel application. It covers collections, disbursements, virtual accounts, bills payment, verification, and more — all through a clean, consistent API.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [How Responses Work](#how-responses-work)
- [Error Handling](#error-handling)
- [Webhooks](#webhooks)
- [Services](#services)
  - [Transactions](#transactions)
  - [Customer Reserved Accounts](#customer-reserved-accounts)
  - [Invoices](#invoices)
  - [Disbursements (Transfers)](#disbursements-transfers)
  - [Wallets](#wallets)
  - [Verification](#verification)
  - [Sub Accounts](#sub-accounts)
  - [Refunds](#refunds)
  - [Settlements](#settlements)
  - [Limit Profiles](#limit-profiles)
  - [Pay Codes](#pay-codes)
  - [Direct Debit](#direct-debit)
  - [Recurring Payments](#recurring-payments)
  - [Bills Payment](#bills-payment)
  - [Helper / Utilities](#helper--utilities)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

---

## Requirements

- PHP **8.1** or higher
- Laravel **8.x – 12.x**
- A Monnify merchant account ([sign up here](https://app.monnify.com))

---

## Installation

Install the package via Composer:

```bash
composer require monnify/monnify-laravel
```

Publish the config file:

```bash
php artisan vendor:publish --provider="Monnify\MonnifyLaravel\MonnifyServiceProvider"
```

This creates a `config/monnify.php` file in your application.

---

## Configuration

Add the following to your `.env` file:

```env
MONNIFY_API_KEY=your_api_key
MONNIFY_SECRET_KEY=your_secret_key
MONNIFY_CONTRACT_CODE=your_contract_code
MONNIFY_WALLET_ACCOUNT_NUMBER=your_wallet_account_number
MONNIFY_ACCOUNT_NUMBER=your_account_number
MONNIFY_ENVIRONMENT=SANDBOX   # Use LIVE when going to production
```

> **Where do I find these?** Log in to your [Monnify dashboard](https://app.monnify.com), go to **Developers → API Keys & Contract** to get your API key, secret, and contract code.

> **Tip:** Always start with `MONNIFY_ENVIRONMENT=SANDBOX` while building and testing. Switch to `LIVE` only when you are ready for production.

---

## Quick Start

The most common flow: collect a payment and verify it when the customer returns.

**Step 1 — Initialize the payment and redirect the customer:**

```php
use Monnify\MonnifyLaravel\Facades\Monnify;

// In your checkout controller
$response = Monnify::transactions()->initialise([
    'amount'           => 5000.00,
    'customerEmail'    => 'jane@example.com',
    'paymentReference' => 'ORDER-' . uniqid(),   // must be unique per transaction
    'currencyCode'     => 'NGN',
    'contractCode'     => config('monnify.contract_code'),
    'redirectUrl'      => route('payment.callback'),
]);

return redirect($response['body']['responseBody']['checkoutUrl']);
```

**Step 2 — Verify the payment in your callback handler:**

```php
// In your callback controller
public function callback(Request $request)
{
    $reference = $request->query('paymentReference');

    $result = Monnify::transactions()->statusByReference($reference, 'payment');
    $status = $result['body']['responseBody']['paymentStatus'] ?? null;

    if ($status === 'PAID') {
        // Payment confirmed server-side — safe to fulfil the order
    }
}
```

> **Important:** Never rely solely on the redirect URL parameters to confirm a payment. Always call `statusByReference()` (or `status()`) from your callback handler to verify the payment directly with Monnify before fulfilling orders. For an additional layer of reliability — especially for payments that complete after a timeout or network drop — subscribe to [Monnify webhook notifications](#webhooks) and treat them as a second source of truth.

---

## How Responses Work

Every method in this package returns an array with two keys:

```php
[
    'status' => 200,       // HTTP status code from Monnify
    'body'   => [ ... ],   // The parsed response data from Monnify
]
```

> **Why `$response['body']['responseBody']`?** Monnify's API always wraps the actual data inside a `responseBody` key within `body`. This is a Monnify API convention — the package returns it as-is so nothing is hidden from you. When you see `$response['body']['responseBody']['checkoutUrl']`, the two levels are: `body` (this package's wrapper) → `responseBody` (Monnify's wrapper) → the actual data.

On a failed request (e.g. a network error or a 4xx/5xx response), the array will look like:

```php
[
    'status' => 400,
    'error'  => [ ... ],   // Error details returned by Monnify
]
```

**Practical example — reading a response:**

```php
$response = Monnify::transactions()->initialise($data);

if ($response['status'] === 200) {
    $checkoutUrl = $response['body']['responseBody']['checkoutUrl'];
    return redirect($checkoutUrl);
}

// Something went wrong
logger()->error('Monnify error', $response['error'] ?? []);
```

---

## Error Handling

Wrap your calls in a `try/catch` block to handle validation errors (thrown before the request is made) and unexpected network failures:

```php
use Exception;
use Monnify\MonnifyLaravel\Facades\Monnify;

try {
    $response = Monnify::transactions()->initialise($data);
} catch (InvalidArgumentException $e) {
    // A required field was missing or invalid — the request was never sent
    return response()->json(['message' => $e->getMessage()], 422);
} catch (Exception $e) {
    // Something unexpected happened (network issue, etc.)
    return response()->json(['message' => 'Payment service unavailable'], 503);
}
```

---

## Webhooks

Monnify sends an HTTP `POST` to your server whenever a significant event occurs (payment received, transfer completed, refund processed, etc.). Webhooks are the most reliable way to keep your system in sync — they fire even if your customer closes the browser mid-flow or a network timeout prevents the redirect from completing.

Full details: [Webhook Overview](https://developers.monnify.com/docs/webhooks) · [Event Types](https://developers.monnify.com/docs/webhooks/event-types)

---

### Setting Up

1. Log in to your [Monnify dashboard](https://app.monnify.com)
2. Go to **Developers → Webhook URLs**
3. Enter your endpoint URL for each notification type (Transaction Completion, Disbursement, Refund, Settlement)

---

### Event Types

| Event                     | When it fires                                        | Related service                 |
| ------------------------- | ---------------------------------------------------- | ------------------------------- |
| `SUCCESSFUL_TRANSACTION`  | Payment confirmed on a reserved account or offline   | Transactions, Reserved Accounts |
| `SUCCESSFUL_DISBURSEMENT` | A transfer completes successfully                    | Disbursements                   |
| `FAILED_DISBURSEMENT`     | A transfer fails                                     | Disbursements                   |
| `REVERSED_DISBURSEMENT`   | A transfer is reversed                               | Disbursements                   |
| `SUCCESSFUL_REFUND`       | A refund is processed                                | Refunds                         |
| `FAILED_REFUND`           | A refund attempt fails                               | Refunds                         |
| `SETTLEMENT`              | Funds settled to your bank account or wallet         | Settlements                     |
| `ACCOUNT_ACTIVITY`        | Credit or debit on a wallet                          | Wallets                         |
| `REJECTED_PAYMENT`        | Payment rejected (e.g. under-payment)                | Transactions                    |
| `MANDATE_UPDATE`          | Direct debit mandate status changes                  | Direct Debit                    |
| `LOW_BALANCE_ALERT`       | Wallet balance drops below your configured threshold | Wallets                         |
| `OFFLINE_PAYMENT_AGENT`   | An offline agent payment completes                   | Transactions                    |

Every webhook payload follows this structure:

```json
{
  "eventType": "SUCCESSFUL_TRANSACTION",
  "eventData": {
    "transactionReference": "MNFY|...",
    "paymentStatus": "PAID",
    ...
  }
}
```

---

### Verifying the Signature

Monnify signs every webhook request with a `monnify-signature` header — an HMAC-SHA512 hash of the raw request body, keyed with your **secret key**.

**Always verify this before processing any webhook.** Skipping this check means anyone who knows your endpoint URL could send fake events.

```php
$signature = $request->header('monnify-signature');
$expected  = hash_hmac('sha512', $request->getContent(), config('monnify.secret_key'));

if (! hash_equals($expected, $signature)) {
    return response()->json(['message' => 'Invalid signature'], 401);
}
```

> **Note:** Use `hash_equals()` instead of `===` to prevent timing attacks.

---

### Handling Webhooks in Laravel

**1. Create the controller:**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MonnifyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify the signature
        $signature = $request->header('monnify-signature');
        $expected  = hash_hmac('sha512', $request->getContent(), config('monnify.secret_key'));

        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $eventType = $request->input('eventType');
        $eventData = $request->input('eventData');

        // Return 200 immediately, then process — prevents Monnify from resending
        // due to a timeout caused by slow downstream logic.
        match ($eventType) {
            'SUCCESSFUL_TRANSACTION'  => ProcessPayment::dispatch($eventData),
            'SUCCESSFUL_DISBURSEMENT' => ProcessDisbursement::dispatch($eventData),
            'FAILED_DISBURSEMENT'     => HandleFailedDisbursement::dispatch($eventData),
            'SUCCESSFUL_REFUND'       => ProcessRefund::dispatch($eventData),
            'SETTLEMENT'              => ProcessSettlement::dispatch($eventData),
            default                   => null,
        };

        return response()->json(['message' => 'Webhook received'], 200);
    }
}
```

**2. Register the route** (webhooks must be excluded from CSRF verification):

```php
// routes/web.php
Route::post('/webhooks/monnify', [MonnifyWebhookController::class, 'handle'])
    ->name('monnify.webhook');
```

```php
// Laravel 11+ — bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'webhooks/monnify',
    ]);
})

// Laravel 10 and below — app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'webhooks/monnify',
];
```

---

### Best Practices

- **Verify the signature** on every request before touching `eventData`
- **Return HTTP 200 immediately** — do heavy processing in a queued job. Monnify will retry delivery if it does not receive a 200 within a reasonable timeout
- **Deduplicate events** — store processed event references (e.g. `transactionReference`) and skip duplicates; Monnify may send the same event more than once
- **Whitelist Monnify's IP** (`35.242.133.146`) at your firewall or in middleware as an extra layer of protection

---

## Services

All services are accessed through the `Monnify` facade:

```php
use Monnify\MonnifyLaravel\Facades\Monnify;
```

---

### Transactions

Handles payment initialisation, card charges, and status checks.

```php
Monnify::transactions()->initialise($data);
Monnify::transactions()->payWithBankTransfer($data);
Monnify::transactions()->chargeCard($data);
Monnify::transactions()->authorizeOTP($data);
Monnify::transactions()->authorizeThreeDSCard($data);
Monnify::transactions()->all($parameters);
Monnify::transactions()->status($transactionReference);
Monnify::transactions()->statusByReference($reference, $referenceType);
```

---

#### Initialize a Transaction

The starting point for collecting a payment. Returns a checkout URL you redirect your customer to.

```php
$response = Monnify::transactions()->initialise([
    'amount'             => 5000.00,
    'customerName'       => 'Jane Doe',
    'customerEmail'      => 'jane@example.com',
    'paymentReference'   => 'ORDER-' . uniqid(),   // must be unique per transaction, you can also change the prefix from ORDER- to anything else.
    'paymentDescription' => 'Payment for Order #1042',
    'currencyCode'       => 'NGN',
    'contractCode'       => config('monnify.contract_code'),
    'redirectUrl'        => 'https://yoursite.com/payment/callback', //this tells the package where to redirect on successful completion
    'paymentMethods'     => ['CARD', 'ACCOUNT_TRANSFER'],  // optional
]);

// Redirect the customer to Monnify's checkout page
return redirect($response['body']['responseBody']['checkoutUrl']);
```

**Required fields:** `amount`, `customerEmail`, `paymentReference`, `currencyCode`, `contractCode`, `redirectUrl`
**Optional fields:** `customerName`, `paymentDescription`, `paymentMethods`, `incomeSplitConfig`

> **Production checklist:** When a customer completes payment and is redirected to your `redirectUrl`, always call `statusByReference()` to verify the payment server-side before fulfilling the order. Additionally, subscribe to the `SUCCESSFUL_TRANSACTION` webhook event on your [Monnify dashboard](https://app.monnify.com) so your system is notified even if the customer closes the browser before the redirect completes. See the [Webhooks](#webhooks) section for setup details.

---

#### Pay with Bank Transfer

Use this when you want to generate a bank transfer payment directly inside your app (without redirecting to checkout).

```php
$response = Monnify::transactions()->payWithBankTransfer([
    'transactionReference' => 'MONNIFY_TXN_REF',  // from initialise() response
    'bankCode'             => '058',               // optional — specific bank
]);
```

---

#### Charge a Card

```php
$response = Monnify::transactions()->chargeCard([
    'transactionReference' => 'MONNIFY_TXN_REF',
    'collectionChannel'    => 'API_NOTIFICATION',
    'card' => [
        'number'      => '4111111111111111',
        'pin'         => '1234',
        'expiryMonth' => '10',
        'expiryYear'  => '2029',
        'cvv'         => '123',
    ],
]);
```

> **Sandbox test cards** — use these card numbers in your `SANDBOX` environment. All share PIN `1234` and CVV `123`.

| Scenario                 | Card Number        | Expiry  |
| ------------------------ | ------------------ | ------- |
| No OTP (direct approval) | `4111111111111111` | 10/2029 |
| Requires OTP             | `5060995994247093` | 12/2029 |
| Requires 3DS             | `4000000000000002` | 12/2029 |
| Declined / Failed        | `4111111111111110` | 10/2029 |

---

#### Authorize with OTP

Called after `chargeCard()` when the bank requires OTP confirmation.

```php
$response = Monnify::transactions()->authorizeOTP([
    'transactionReference' => 'MONNIFY_TXN_REF',
    'collectionChannel'    => 'API_NOTIFICATION',
    'tokenId'              => 'TOKEN_ID_FROM_CHARGE_RESPONSE',
    'token'                => '123456',
]);
```

---

#### Authorize 3D Secure Card

3DS requires browser/device metadata collected from your customer's browser via JavaScript. Gather these values on your frontend and pass them along with the card details.

```php
$response = Monnify::transactions()->authorizeThreeDSCard([
    'transactionReference' => 'MONNIFY_TXN_REF',
    'collectionChannel'    => 'API_NOTIFICATION',
    'card' => [
        'number'      => '4111111111111111',
        'pin'         => '1234',
        'expiryMonth' => '10',
        'expiryYear'  => '31',
        'cvv'         => '100',
    ],
    'apiKey'            => config('monnify.api_key'),
    'deviceInformation' => [
        // Collect these from the customer's browser using JavaScript
        'httpBrowserLanguage'          => 'en-US',       // navigator.language
        'httpBrowserJavaEnabled'       => false,          // navigator.javaEnabled()
        'httpBrowserJavaScriptEnabled' => true,           // always true if JS is running
        'httpBrowserColorDepth'        => '24',           // screen.colorDepth
        'httpBrowserScreenHeight'      => '900',          // screen.height
        'httpBrowserScreenWidth'       => '1440',         // screen.width
        'httpBrowserTimeDifference'    => '-60',          // new Date().getTimezoneOffset()
        'userAgentBrowserValue'        => 'Mozilla/5.0', // navigator.userAgent
    ],
]);
```

> **How to collect device information on the frontend:** Add a small JavaScript snippet that reads these values from the browser's `navigator` and `screen` objects, then POST them to your backend before calling this endpoint. This data is required by card networks for 3DS fraud assessment.

---

#### Get All Transactions

```php
$response = Monnify::transactions()->all([
    'page'             => 0,
    'size'             => 20,
    'paymentStatus'    => 'PAID',       // optional filter
    'customerEmail'    => 'jane@example.com', // optional filter
]);
```

---

#### Get Transaction Status

```php
// By Monnify transaction reference
$response = Monnify::transactions()->status('MONNIFY_TXN_REF');

// By your own payment or transaction reference
$response = Monnify::transactions()->statusByReference('ORDER-123', 'payment');
// $referenceType is either 'transaction' (default) or 'payment'
```

---

### Customer Reserved Accounts

Reserved accounts are dedicated virtual bank accounts assigned to a customer. Any payment made to that account is automatically recognised and processed.

```php
Monnify::customerReservedAccount()->createGeneralAccount($data);
Monnify::customerReservedAccount()->createInvoiceAccount($data);
Monnify::customerReservedAccount()->get($accountReference);
Monnify::customerReservedAccount()->addLinkedAccounts($accountReference, $data);
Monnify::customerReservedAccount()->updateBVN($accountReference, $bvn);
Monnify::customerReservedAccount()->updateKYCInfo($accountReference, $data);
Monnify::customerReservedAccount()->allowedPaymentSource($accountReference, $data);
Monnify::customerReservedAccount()->updateSplitConfig($accountReference, $data);
Monnify::customerReservedAccount()->deallocateAccount($accountReference);
Monnify::customerReservedAccount()->transactions($accountReference, $parameters);
```

---

#### Create a General Reserved Account

```php
$response = Monnify::customerReservedAccount()->createGeneralAccount([
    'accountReference'    => 'CUSTOMER-001',        // your unique reference for this account
    'accountName'         => 'Jane Doe',
    'currencyCode'        => 'NGN',
    'contractCode'        => config('monnify.contract_code'),
    'customerEmail'       => 'jane@example.com',
    'customerName'        => 'Jane Doe',
    'getAllAvailableBanks' => true,
    // 'preferredBanks'   => ['035', '058'],         // optional — pick specific banks
    // 'bvn'              => '12345678901',           // optional
]);
```

---

#### Create an Invoice Reserved Account

Similar to a general account but tied to a specific invoice amount.

```php
$response = Monnify::customerReservedAccount()->createInvoiceAccount([
    'contractCode'        => config('monnify.contract_code'),
    'accountName'         => 'Jane Doe',
    'currencyCode'        => 'NGN',
    'accountReference'    => 'INVOICE-ACCT-001',
    'customerEmail'       => 'jane@example.com',
    'reservedAccountType' => 'INVOICE',
    // 'customerName'     => 'Jane Doe',             // optional
    // 'bvn'              => '12345678901',           // optional
]);
```

---

#### Get Account Details

```php
$response = Monnify::customerReservedAccount()->get('CUSTOMER-001');
```

---

#### Add Linked Accounts

Link additional bank accounts to an existing reserved account.

```php
$response = Monnify::customerReservedAccount()->addLinkedAccounts('CUSTOMER-001', [
    'getAllAvailableBanks' => true,
    // 'preferredBanks'   => ['044'],   // optional
]);
```

---

#### Update BVN

```php
$response = Monnify::customerReservedAccount()->updateBVN('CUSTOMER-001', '12345678901');
```

---

#### Update KYC Info

```php
$response = Monnify::customerReservedAccount()->updateKYCInfo('CUSTOMER-001', [
    'bvn' => '12345678901',
    'nin' => '00000000000',   // optional if bvn is provided
]);
```

---

#### Restrict Payment Sources

Limit which BVNs or account numbers can fund a reserved account.

```php
$response = Monnify::customerReservedAccount()->allowedPaymentSource('CUSTOMER-001', [
    'restrictPaymentSource' => true,
    'allowedPaymentSources' => [
        'bvns' => ['12345678901', '09876543210'],
    ],
]);
```

---

#### Update Split Configuration

```php
$response = Monnify::customerReservedAccount()->updateSplitConfig('CUSTOMER-001', [
    [
        'subAccountCode'        => 'MFY_SUB_305040939040',
        'feePercentage'         => 10.50,
        'splitPercentage'       => 30.00,
        'feeBearer'             => true,
    ],
]);
```

---

#### Get Account Transactions

```php
$response = Monnify::customerReservedAccount()->transactions('CUSTOMER-001', [
    'page' => 0,
    'size' => 10,
]);
```

---

#### Deallocate (Delete) an Account

```php
$response = Monnify::customerReservedAccount()->deallocateAccount('CUSTOMER-001');
```

---

### Invoices

Create time-limited payment requests you can send to customers.

```php
Monnify::invoice()->create($data);
Monnify::invoice()->get($invoiceReference);
Monnify::invoice()->all();
Monnify::invoice()->cancel($invoiceReference);
Monnify::invoice()->attachReservedAccount($data);
```

---

#### Create an Invoice

```php
$response = Monnify::invoice()->create([
    'amount'           => 15000.00,
    'customerName'     => 'Jane Doe',
    'customerEmail'    => 'jane@example.com',
    'expiryDate'       => '2025-12-31 23:59:59',
    'invoiceReference' => 'INV-' . uniqid(),
    'description'      => 'Subscription - Pro Plan',
    'currencyCode'     => 'NGN',
    'contractCode'     => config('monnify.contract_code'),
    // 'redirectUrl'   => 'https://yoursite.com/invoice/callback',  // optional
    // 'incomeSplitConfig' => [],                                    // optional
]);
```

---

#### Get Invoice Details

```php
$response = Monnify::invoice()->get('INV-6630abc');
```

---

#### Get All Invoices

```php
$response = Monnify::invoice()->all();
```

---

#### Cancel an Invoice

```php
$response = Monnify::invoice()->cancel('INV-6630abc');
```

---

#### Attach a Reserved Account to an Invoice

```php
$response = Monnify::invoice()->attachReservedAccount([
    'amount'           => 15000.00,
    'invoiceReference' => 'INV-6630abc',
    'accountReference' => 'CUSTOMER-001',
    'description'      => 'Subscription - Pro Plan',
    'currencyCode'     => 'NGN',
    'contractCode'     => config('monnify.contract_code'),
    'customerEmail'    => 'jane@example.com',
    'customerName'     => 'Jane Doe',
    'expiryDate'       => '2025-12-31 23:59:59',
]);
```

---

### Disbursements (Transfers)

Send money from your Monnify wallet to bank accounts — one at a time or in bulk.

```php
Monnify::transfer()->single($data, $asynchronous);
Monnify::transfer()->authoriseSingle($data);
Monnify::transfer()->resendOTP($reference);
Monnify::transfer()->singleStatus($reference);
Monnify::transfer()->bulk($data);
Monnify::transfer()->authoriseBulk($data);
Monnify::transfer()->bulkResendOTP($reference);
Monnify::transfer()->bulkStatus($batchReference, $pageSize, $pageNumber);
Monnify::transfer()->bulkBatchSummary($batchReference);
Monnify::transfer()->bulkTransaction($batchReference, $pageSize, $pageNumber);
Monnify::transfer()->all($type, $pageSize, $pageNumber);
Monnify::transfer()->search($sourceAccountNumber, $pageSize, $pageNumber);
Monnify::transfer()->walletBalance($accountNumber);
```

---

#### Single Transfer

```php
$response = Monnify::transfer()->single([
    'amount'                  => 5000.00,
    'reference'               => 'TXN-' . uniqid(),      // your unique reference
    'narration'               => 'Salary - May 2025',
    'destinationBankCode'     => '058',
    'destinationAccountNumber'=> '0123456789',
    'destinationAccountName'  => 'John Doe',
    'currency'                => 'NGN',
    'sourceAccountNumber'     => config('monnify.account_number'),
], $asynchronous = false);
```

> **Two-factor note:** By default, transfers require OTP authorisation. If you receive a `PENDING_AUTHORIZATION` status, call `authoriseSingle()` with the OTP.

---

#### Authorise a Single Transfer (OTP)

```php
$response = Monnify::transfer()->authoriseSingle([
    'reference'         => 'TXN-abc123',
    'authorizationCode' => '123456',     // OTP sent to your registered email
]);
```

---

#### Resend OTP for a Single Transfer

```php
$response = Monnify::transfer()->resendOTP('TXN-abc123');
```

---

#### Single Transfer Status

```php
$response = Monnify::transfer()->singleStatus('TXN-abc123');
```

---

#### Bulk Transfer

Send payments to multiple recipients in one request.

```php
$response = Monnify::transfer()->bulk([
    'title'               => 'May 2025 Salaries',
    'batchReference'      => 'BATCH-' . uniqid(),
    'narration'           => 'Monthly salary payment',
    'sourceAccountNumber' => config('monnify.account_number'),
    'onValidationFailure' => 'CONTINUE',  // CONTINUE or BREAK
    'notificationInterval'=> 25,          // notify after every 25% of transactions
    'transactionList'     => [
        [
            'amount'                   => 50000.00,
            'reference'                => 'SAL-EMP001',
            'narration'                => 'Salary - John',
            'destinationBankCode'      => '058',
            'destinationAccountNumber' => '0123456789',
            'currency'                 => 'NGN',
        ],
        [
            'amount'                   => 75000.00,
            'reference'                => 'SAL-EMP002',
            'narration'                => 'Salary - Mary',
            'destinationBankCode'      => '033',
            'destinationAccountNumber' => '9876543210',
            'currency'                 => 'NGN',
        ],
    ],
]);
```

---

#### Authorise a Bulk Transfer (OTP)

```php
$response = Monnify::transfer()->authoriseBulk([
    'reference'         => 'BATCH-abc123',
    'authorizationCode' => '123456',
]);
```

---

#### Resend OTP for a Bulk Transfer

```php
$response = Monnify::transfer()->bulkResendOTP('BATCH-abc123');
```

---

#### Bulk Transfer Status (Per Transaction)

Lists individual transactions within a batch.

```php
$response = Monnify::transfer()->bulkStatus('BATCH-abc123', $pageSize = 10, $pageNumber = 0);
```

---

#### Bulk Batch Summary

Gets the overall summary of a batch (total sent, total failed, etc.).

```php
$response = Monnify::transfer()->bulkBatchSummary('BATCH-abc123');
```

---

#### List All Transfers

```php
// List single transfers
$response = Monnify::transfer()->all('single', $pageSize = 10, $pageNumber = 0);

// List bulk transfers
$response = Monnify::transfer()->all('bulk', $pageSize = 10, $pageNumber = 0);
```

---

#### Search Transfers

```php
$response = Monnify::transfer()->search(
    config('monnify.account_number'),
    $pageSize = 10,
    $pageNumber = 0
);
```

---

#### Get Disbursement Wallet Balance

Returns the available balance on your Monnify disbursement wallet account.

```php
$response = Monnify::transfer()->walletBalance(config('monnify.account_number'));
```

> **Note:** This returns the balance for your Monnify **disbursement** (merchant) wallet — identified by `accountNumber`. For the balance of a customer sub-wallet, use `Monnify::wallet()->balance($accountNumber)` instead.

---

### Wallets

> **Activation required:** The Wallet service is not enabled by default. Contact [sales@monnify.com](mailto:sales@monnify.com) to have it activated on your account.

Manage Monnify sub-wallets for your customers.

```php
Monnify::wallet()->create($data);
Monnify::wallet()->get($customerEmail, $pageSize, $pageNumber);
Monnify::wallet()->balance($accountNumber);
Monnify::wallet()->transactions($accountNumber, $pageSize, $pageNumber);
```

---

#### Create a Wallet

```php
$response = Monnify::wallet()->create([
    'customerEmail' => 'jane@example.com',
    'customerName'  => 'Jane Doe',
    'bvnDetails'    => [
        'bvn'            => '22222222226',
        'bvnDateOfBirth' => '1994-09-07',
    ],
]);
```

---

#### Get Wallet Details

```php
$response = Monnify::wallet()->get('jane@example.com', $pageSize = 10, $pageNumber = 0);
```

---

#### Check Wallet Balance

Returns the balance for a specific customer sub-wallet (Wallet Service endpoint).

```php
$response = Monnify::wallet()->balance('0123456789');
```

> **Note:** This uses the Wallet Service endpoint (`/api/v1/disbursements/wallet/balance`) and requires the customer's sub-wallet account number. To check your Monnify disbursement wallet balance instead, use `Monnify::transfer()->walletBalance($accountNumber)`.

---

#### Get Wallet Transactions

```php
$response = Monnify::wallet()->transactions('0123456789', $pageSize = 10, $pageNumber = 0);
```

---

### Verification

Verify bank accounts, BVN, and NIN before processing payments or disbursements.

```php
Monnify::verificationAPI()->bankAccount($accountNumber, $bankCode);
Monnify::verificationAPI()->bvnInformation($data);
Monnify::verificationAPI()->matchBVNAndBankAccount($bvn, $bankCode, $accountNumber);
Monnify::verificationAPI()->nin($nin);
```

---

#### Verify Bank Account

```php
$response = Monnify::verificationAPI()->bankAccount('0123456789', '058');

$accountName = $response['body']['responseBody']['accountName'];
```

---

#### Verify BVN Information

```php
$response = Monnify::verificationAPI()->bvnInformation([
    'bvn'         => '12345678901',
    'name'        => 'Jane Doe',
    'dateOfBirth' => '1990-01-15',    // YYYY-MM-DD
    'mobileNo'    => '08012345678',
]);
```

---

#### Match BVN with Bank Account

```php
$response = Monnify::verificationAPI()->matchBVNAndBankAccount(
    '12345678901',   // BVN
    '058',           // bank code
    '0123456789'     // account number
);
```

---

#### Verify NIN

```php
$response = Monnify::verificationAPI()->nin('00000000000');
```

---

### Sub Accounts

> **Activation required:** The Sub Account service is not enabled by default. Contact [integration-support@monnify.com](mailto:integration-support@monnify.com) to have it activated on your account.

Sub accounts let you split incoming payments between multiple bank accounts automatically.

```php
Monnify::subAccount()->create($data);
Monnify::subAccount()->all();
Monnify::subAccount()->update($data);
Monnify::subAccount()->delete($subAccountCode);
```

---

#### Create a Sub Account

```php
$response = Monnify::subAccount()->create([
    [
        'bankCode'               => '058',
        'accountNumber'          => '0123456789',
        'email'                  => 'vendor@example.com',
        'currencyCode'           => 'NGN',
        'defaultSplitPercentage' => 20.00,
    ],
]);
```

> **Note:** The API accepts an **array of sub accounts**, so always wrap a single sub account in an outer array as shown above.

---

#### Get All Sub Accounts

```php
$response = Monnify::subAccount()->all();
```

---

#### Update a Sub Account

```php
$response = Monnify::subAccount()->update([
    'subAccountCode'         => 'MFY_SUB_305040939040',
    'bankCode'               => '033',
    'accountNumber'          => '9876543210',
    'email'                  => 'vendor-new@example.com',
    'currencyCode'           => 'NGN',
    'defaultSplitPercentage' => 25.00,
]);
```

---

#### Delete a Sub Account

```php
$response = Monnify::subAccount()->delete('MFY_SUB_305040939040');
```

---

### Refunds

> **Activation required:** The Refund service is not enabled by default. Contact [integration-support@monnify.com](mailto:integration-support@monnify.com) to have it activated on your account.

Reverse a completed transaction and return funds to the customer.

```php
Monnify::refund()->initialise($data);
Monnify::refund()->status($refundReference);
Monnify::refund()->all($pageSize, $pageNumber);
```

---

#### Initiate a Refund

```php
$response = Monnify::refund()->initialise([
    'transactionReference' => 'MONNIFY_TXN_REF',       // original transaction
    'refundReference'      => 'REFUND-' . uniqid(),     // your unique refund reference
    'refundReason'         => 'Customer request',
    'refundAmount'         => 5000.00,
    'customerNote'         => 'Refund processed within 3 business days',
    // 'destinationAccountNumber' => '0123456789',       // optional — defaults to original payer
    // 'destinationAccountBankCode' => '058',            // optional
]);
```

---

#### Check Refund Status

```php
$response = Monnify::refund()->status('REFUND-abc123');
```

---

#### Get All Refunds

```php
$response = Monnify::refund()->all($pageSize = 10, $pageNumber = 0);
```

---

### Settlements

Query how funds were settled into your bank account.

```php
Monnify::settlements()->transactions($settlementReference, $pageSize, $pageNumber);
Monnify::settlements()->getByTransaction($transactionReference);
```

---

#### Get Transactions by Settlement Reference

```php
$response = Monnify::settlements()->transactions('SETTLEMENT-REF', $pageSize = 10, $pageNumber = 0);
```

---

#### Get Settlement Info for a Transaction

```php
$response = Monnify::settlements()->getByTransaction('MONNIFY_TXN_REF');
```

---

### Limit Profiles

Set transaction limits on reserved accounts — useful for KYC tier management.

```php
Monnify::limitProfile()->all();
Monnify::limitProfile()->create($data);
Monnify::limitProfile()->update($limitProfileCode, $data);
Monnify::limitProfile()->reserveAccount($data);
Monnify::limitProfile()->updateReserveAccount($accountReference, $limitProfileCode);
```

---

#### Get All Limit Profiles

```php
$response = Monnify::limitProfile()->all();
```

---

#### Create a Limit Profile

```php
$response = Monnify::limitProfile()->create([
    'limitProfileName'      => 'Tier 1 - Unverified',
    'singleTransactionLimit'=> 50000.00,
    'dailyTransactionLimit' => 200000.00,
    'dailyTransactionVolume'=> 5,
]);
```

---

#### Update a Limit Profile

```php
$response = Monnify::limitProfile()->update('LIMIT-PROFILE-CODE', [
    'limitProfileName'      => 'Tier 1 - Upgraded',
    'singleTransactionLimit'=> 100000.00,
    'dailyTransactionLimit' => 500000.00,
    'dailyTransactionVolume'=> 10,
]);
```

---

#### Create a Reserved Account with a Limit Profile

```php
$response = Monnify::limitProfile()->reserveAccount([
    'accountReference' => 'CUSTOMER-001',
    'limitProfileCode' => 'LIMIT-PROFILE-CODE',
    'contractCode'     => config('monnify.contract_code'),
    'accountName'      => 'Jane Doe',
]);
```

---

#### Update a Reserved Account's Limit Profile

```php
$response = Monnify::limitProfile()->updateReserveAccount('CUSTOMER-001', 'LIMIT-PROFILE-CODE');
```

---

### Pay Codes

> **Activation required:** The Pay Code service is not enabled by default. Contact [integration-support@monnify.com](mailto:integration-support@monnify.com) to have it activated on your account.

Generate single-use payment codes that can be redeemed at ATMs or agent locations.

```php
Monnify::payCodeAPI()->create($data);
Monnify::payCodeAPI()->get($payCodeReference);
Monnify::payCodeAPI()->getUnMasked($payCodeReference);
Monnify::payCodeAPI()->history($parameters);
Monnify::payCodeAPI()->delete($payCodeReference);
```

---

#### Create a Pay Code

```php
$response = Monnify::payCodeAPI()->create([
    'amount'           => 10000.00,
    'paycodeReference' => 'PAYCODE-' . uniqid(),
    'beneficiaryName'  => 'John Doe',
    'clientId'         => 'YOUR_CLIENT_ID',
    'expiryDate'       => '2025-12-31',
]);
```

---

#### Get Pay Code Details

```php
// Masked (safe to display)
$response = Monnify::payCodeAPI()->get('PAYCODE-abc123');

// Unmasked (full code — handle with care)
$response = Monnify::payCodeAPI()->getUnMasked('PAYCODE-abc123');
```

---

#### Pay Code History

```php
$response = Monnify::payCodeAPI()->history([
    'transactionReference' => '',       // optional
    'beneficiaryName'      => '',       // optional
    'transactionStatus'    => 'PAID',   // optional
    'from'                 => '2025-01-01',
    'to'                   => '2025-05-31',
]);
```

---

#### Delete a Pay Code

```php
$response = Monnify::payCodeAPI()->delete('PAYCODE-abc123');
```

---

### Direct Debit

Set up mandates to debit a customer's account automatically on a schedule.

```php
Monnify::directDebitMandate()->create($data);
Monnify::directDebitMandate()->get($mandateReference);
Monnify::directDebitMandate()->debit($data);
Monnify::directDebitMandate()->status($paymentReference);
Monnify::directDebitMandate()->cancel($mandateCode);
```

---

#### Create a Mandate

```php
$response = Monnify::directDebitMandate()->create([
    'contractCode'           => config('monnify.contract_code'),
    'mandateReference'       => 'MANDATE-' . uniqid(),
    'customerName'           => 'Jane Doe',
    'customerPhoneNumber'    => '08012345678',
    'customerEmailAddress'   => 'jane@example.com',
    'customerAddress'        => '12 Example Street, Lagos',
    'customerAccountNumber'  => '0123456789',
    'customerAccountBankCode'=> '058',
    'mandateDescription'     => 'Monthly subscription fee',
    'mandateStartDate'       => '2025-06-01T00:00:00',
    'mandateEndDate'         => '2026-06-01T00:00:00',
    // 'autoRenew'           => false,  // optional
    // 'customerCancellation'=> true,   // optional — allow customer to cancel
    // 'customerAccountName' => 'Jane Doe',  // optional
]);
```

---

#### Get Mandate Details

```php
$response = Monnify::directDebitMandate()->get('MANDATE-abc123');
```

---

#### Debit a Mandate

```php
$response = Monnify::directDebitMandate()->debit([
    'mandateCode'      => 'MONNIFY_MANDATE_CODE',  // from create response
    'amount'           => 5000.00,
    'paymentReference' => 'PAY-' . uniqid(),
    'narration'        => 'June 2025 subscription',
    'customerEmail'    => 'jane@example.com',
]);
```

---

#### Get Debit Status

```php
$response = Monnify::directDebitMandate()->status('PAY-abc123');
```

---

#### Cancel a Mandate

```php
$response = Monnify::directDebitMandate()->cancel('MONNIFY_MANDATE_CODE');
```

---

### Recurring Payments

> **Activation required:** Card tokenisation is not enabled by default. You must request it from Monnify before card tokens will be returned. Contact [integration-support@monnify.com](mailto:integration-support@monnify.com) to have it enabled on your account.

Once enabled, you can charge a customer's saved card token for future payments without asking them to re-enter their card details.

```php
Monnify::recurringPayment()->chargeCardToken($data);
```

---

#### How Card Tokens Work

1. A customer completes a **first-time card payment** through your integration
2. After the payment succeeds, you call the **transaction status requery** endpoint
3. If tokenisation is enabled on your account, the requery response will include a `cardToken`
4. You store this token securely in your database against the customer's record
5. For all future charges, you pass the stored token to `chargeCardToken()` — no card details needed

---

#### Charge a Card Token

```php
$response = Monnify::recurringPayment()->chargeCardToken([
    'cardToken'          => 'MNFY_TOKEN_FROM_REQUERY',   // stored from requery after first charge
    'amount'             => 5000.00,
    'customerEmail'      => 'jane@example.com',
    'paymentReference'   => 'RECUR-' . uniqid(),
    'contractCode'       => config('monnify.contract_code'),
    'apiKey'             => config('monnify.api_key'),
    // 'customerName'       => 'Jane Doe',        // optional
    // 'paymentDescription' => 'Monthly plan',    // optional
    // 'currencyCode'       => 'NGN',             // optional
]);
```

---

### Bills Payment

Pay utility bills, buy airtime, subscribe to cable TV, and more.

> **Note:** Bills Payment is not enabled by default. To activate it, email [integration-support@monnify.com](mailto:integration-support@monnify.com).

```php
Monnify::billsPayment()->categories($pageSize, $pageNumber);
Monnify::billsPayment()->billers($categoryCode, $pageSize, $pageNumber);
Monnify::billsPayment()->products($billerCode, $pageSize, $pageNumber);
Monnify::billsPayment()->validateCustomer($data);
Monnify::billsPayment()->vend($data);
Monnify::billsPayment()->requery($vendReference);
```

---

#### Typical Bills Payment Flow

```
1. categories()       — find the right category (e.g. ELECTRICITY)
2. billers()          — find the right biller within that category (e.g. IKEDC)
3. products()         — find the right product/package for that biller
4. validateCustomer() — confirm the customer's meter number / decoder ID is valid
5. vend()             — process the actual payment
6. requery()          — check status if the response was IN_PROGRESS
```

---

#### Get Biller Categories

```php
$response = Monnify::billsPayment()->categories();

// Example categories: ELECTRICITY, CABLE_TV, AIRTIME, DATA, BETTING, EDUCATION
```

---

#### List Billers

```php
// All billers
$response = Monnify::billsPayment()->billers();

// Billers in a specific category
$response = Monnify::billsPayment()->billers('ELECTRICITY');
```

---

#### Get Biller Products

```php
$response = Monnify::billsPayment()->products('IKEDC');
// Returns the available payment packages/plans for that biller
```

---

#### Validate Customer

Always call this before `vend()` to confirm the customer ID (meter number, decoder ID, etc.) is valid. Some products also require you to pass back the `validationReference` in the vend request.

```php
$validation = Monnify::billsPayment()->validateCustomer([
    'productCode' => 'IKEDC_PREPAID',     // from products() response
    'customerId'  => '1234567890',         // meter number / decoder ID / betting ID
]);

$customerName        = $validation['body']['responseBody']['customerName'];
$requiresValidation  = $validation['body']['responseBody']['requireValidationRef'] ?? false;
$validationReference = $validation['body']['responseBody']['validationReference'] ?? null;
```

---

#### Process a Bill (Vend)

```php
$response = Monnify::billsPayment()->vend([
    'productCode'   => 'IKEDC_PREPAID',
    'customerId'    => '1234567890',
    'vendAmount'    => 5000.00,
    'vendReference' => 'BILL-' . uniqid(),      // your unique reference for this transaction

    // Include this only if validateCustomer() returned requireValidationRef = true
    'validationReference' => $validationReference,
]);

$vendStatus = $response['body']['responseBody']['vendStatus'];
// SUCCESSFUL, FAILED, or IN_PROGRESS
```

---

#### Check Bill Payment Status

Call this when `vend()` returns `IN_PROGRESS` to get the final result.

```php
$response = Monnify::billsPayment()->requery('BILL-abc123');
```

---

#### Complete Example — Pay an Electricity Bill

```php
use Monnify\MonnifyLaravel\Facades\Monnify;

// 1. Validate the customer's meter number
$validation = Monnify::billsPayment()->validateCustomer([
    'productCode' => 'IKEDC_PREPAID',
    'customerId'  => '45210012345',
]);

if ($validation['status'] !== 200) {
    return response()->json(['message' => 'Invalid meter number'], 422);
}

$validationBody      = $validation['body']['responseBody'];
$validationReference = $validationBody['validationReference'] ?? null;

// 2. Process the payment
$vend = Monnify::billsPayment()->vend([
    'productCode'         => 'IKEDC_PREPAID',
    'customerId'          => '45210012345',
    'vendAmount'          => 5000.00,
    'vendReference'       => 'BILL-' . uniqid(),
    'validationReference' => $validationReference,
]);

$status = $vend['body']['responseBody']['vendStatus'];

// 3. Handle IN_PROGRESS status
if ($status === 'IN_PROGRESS') {
    // The vend is still processing. In production, dispatch a queued job
    // that calls requery() after a short delay instead of blocking here.
    $vend   = Monnify::billsPayment()->requery($vend['body']['responseBody']['vendReference']);
    $status = $vend['body']['responseBody']['vendStatus'];
}

if ($status === 'SUCCESSFUL') {
    $token = $vend['body']['responseBody']['token'];   // electricity token to give the customer
    return response()->json(['token' => $token]);
}

return response()->json(['message' => 'Bill payment failed'], 400);
```

---

### Helper / Utilities

Fetch general information like a list of banks.

```php
Monnify::helper()->banks();          // All banks supported by Monnify
Monnify::helper()->banksWithUSSD();  // Banks that support USSD payment collection
```

---

## Contributing

Contributions are welcome! To contribute:

1. Fork the repository
2. Create a feature or bug-fix branch (`git checkout -b feature/my-feature`)
3. Make your changes and add tests where applicable
4. Submit a Pull Request with a clear description of what you changed and why

Please ensure your code follows the existing style and that all tests pass before submitting.

---

## Credits

- [Babatunde Adelabu](https://github.com/fredneutron)
- [Aransiola Ayodele](https://github.com/CodeLeom)
- [Auwal MS](https://github.com/auwalms)
- [Moses Adewale](https://github.com/mosesadwale)

---

## License

This package is open-sourced under the [MIT License](LICENSE.md).

---

## Support

For integration questions or to activate additional features (like Bills Payment), contact the Monnify team:

- **Email:** [integration-support@monnify.com](mailto:integration-support@monnify.com)
- **Developer Docs:** [developers.monnify.com](https://developers.monnify.com)
