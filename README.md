# Laravel Monnify Package Documentation

A Laravel package to effortlessly integrate the Monnify payment gateway API into your Laravel projects.


## Installation

Install via Composer:

```bash
composer require monnify/monnify-laravel
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Monnify\MonnifyLaravel\MonnifyServiceProvider"
```

Add environment variables (`.env`):

```env
MONNIFY_API_KEY=your_api_key
MONNIFY_SECRET_KEY=your_secret_key
MONNIFY_CONTRACT_CODE=your_contract_code
MONNIFY_WALLET_ACCOUNT_NUMBER=your_wallet_number
MONNIFY_ACCOUNT_NUMBER=your_account_number
MONNIFY_ENVIRONMENT=SANDBOX # or LIVE
```

## Quick Start

Here's how to quickly initialize a payment transaction:

```php
use Monnify\MonnifyLaravel\Facades\Monnify;

$data = [
    'amount' => 100.00,
    'customerName' => 'Jane Doe',
    'customerEmail' => 'jane@example.com',
    'paymentReference' => 'UNIQUE_REF_001',
    'paymentDescription' => 'Payment for Service',
    'currencyCode' => 'NGN',
    'contractCode' => config('monnify.contract_code'),
    'redirectUrl' => 'https://your-site.com/payment-confirmation',
    'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER'],
];

try {
    $response = Monnify::transactions()->initialise($data);
    return redirect($response['checkoutUrl']);
} catch (Exception $e) {
    // Handle error
    $errorMessage = $e->getMessage();
    // $e->status  // error status
    // $e->error  // error object
}
```

> This package throws exceptions for various error cases. Hence, wrapping your API calls in try-catch blocks.

# Available Services

This package provides the following services:

1. **Transaction Service**: Manage payments, authorizations, and statuses.
2. **Customer Reserved Account Service**: Create/manage virtual accounts.
3. **Invoice Service**: Generate and manage invoices.
4. **Disbursement Service**: Manage single and bulk fund transfers.
5. **Wallet Service**: Manage wallet creation, balances, and transactions.
6. **Verification Service**: Perform account, BVN, NIN verifications.
7. **Sub Account Service**: Create/manage sub-accounts for split payments.
8. **Refund Service**: Handle payment refunds.
9. **Settlement Service**: Settlement transaction handling.
10. **Limit Profile Service**: Manage transaction limits.
11. **Pay Code Service**: Generate and manage pay codes.
12. **Direct Debit Service**: Manage direct debit mandates.
13. **Recurring Payment Service**: Automate recurring payments.
14. **Helper Service**: Utility functions (fetch banks, etc).


# Detailed Usage

## Transaction Service

The Transaction Service handles all payment-related operations.

### All Available Methods

```php
// Initialize a new transaction
Monnify::transactions()->initialise($data);  

// Initialize bank transfer payment                 
Monnify::transactions()->payWithBankTransfer($data);

// Charge a card
Monnify::transactions()->chargeCard($data);                   

/* Card Authorization */

// Authorize with OTP
Monnify::transactions()->authorizeOTP($data);   

// Authorize 3D secure card
Monnify::transactions()->authorizeThreeDSCard($data);        

/* Transaction Information */

// Get all transactions
Monnify::transactions()->all();

// Get transaction status
Monnify::transactions()->status($transactionReference);  

// Get status by reference
Monnify::transactions()->statusByReference($reference, $type); 
```

## Transaction Initialization

```php
Monnify::transactions()->initialise($data);
```
**Required fields:** `amount`, `customerName`, `customerEmail`, `paymentReference`, `currencyCode`, `contractCode`, `redirectUrl`.

**Optional fields:** `paymentMethods`, `incomeSplitConfig`.

### Pay with Bank Transfer

Initializes a bank transfer payment.

```php
Monnify::transactions()->payWithBankTransfer($data);
```
**Required Parameters:**

```php
$data = [
    'amount' => 1000.00,
    'customerName' => 'John Doe',
    'customerEmail' => 'john@example.com',
    'paymentReference' => 'UNIQUE-REF-123',
    'currencyCode' => 'NGN',
    'contractCode' => 'CONTRACT-CODE'
];
```

### Charge Card

Process a card payment.

```php
Monnify::transactions()->chargeCard($data);
```

**Required Parameters:**

```php
$data = [
    'transactionReference' => 'TRANS-REF-123', // string: Transaction reference
    'card' => [
        'number' => '5399********4444',        // string: Card number
        'expiryMonth' => '07',                 // string: Card expiry month
        'expiryYear' => '25',                  // string: Card expiry year
        'cvv' => '123'                         // string: Card CVV
    ]
];
```

### Authorize with OTP

```php
Monnify::transactions()->authorizeOTP($data);
```

**Required Parameters:**

```php
$data = [
    'transactionReference' => 'TRANS-REF-123',
    'otp' => '123456'
];
```

### Authorize 3D secure card

```php
Monnify::transactions()->authorizeThreeDSCard($data);
```

**Required Parameters:**

```php
$data = [
    'transactionReference' => 'TRANS-REF-123',
    'authorizationCode' => 'AUTH-CODE-123'
];
```

### Get Transaction Status

```php
Monnify::transactions()->status($transactionReference);
```

**Parameters:**
- `$transactionReference` (string): The transaction reference to check

### Get Status by Reference

Check transaction status using different reference types.

```php
Monnify::transactions()->statusByReference($reference, $referenceType);
```

**Parameters:**
- `$reference` (string): The reference number
- `$referenceType` (string): Either 'transaction' or 'payment' (default: 'transaction')

## Customer Reserved Account Service

Manages reserved account operations.

### All Available Methods

```php
// Create general account
Monnify::customerReservedAccount()->createGeneralAccount($data);

// Create invoice account
Monnify::customerReservedAccount()->createInvoiceAccount($data);    

// Get account details
Monnify::customerReservedAccount()->get($accountReference);

// Add linked accounts
Monnify::customerReservedAccount()->addLinkedAccounts($ref, $data); 

// Remove account
Monnify::customerReservedAccount()->deallocateAccount($ref);        


/** Account Updates **/

// Update BVN
Monnify::customerReservedAccount()->updateBVN($ref, $bvn);  

// Update KYC info
Monnify::customerReservedAccount()->updateKYCInfo($ref, $data);  

// Update payment sources
Monnify::customerReservedAccount()->allowedPaymentSource($ref, $data); 

// Update split config
Monnify::customerReservedAccount()->updateSplitConfig($ref, $data); 

/** Transaction Information **/

 // Get transactions
Monnify::customerReservedAccount()->transactions($ref, $params);   
```

### Create General Account

Creates a new reserved general account.

```php
Monnify::customerReservedAccount()->createGeneralAccount($data);
```
**Required Parameters:**

```php
$data = [
    'accountReference' => 'ACC-REF-123',    // string: Unique account reference
    'accountName' => 'John Doe',            // string: Account holder name
    'currencyCode' => 'NGN',                // string: Currency code
    'contractCode' => 'CONTRACT-CODE',       // string: Your contract code
    'customerEmail' => 'john@example.com',   // string: Customer email
    'customerName' => 'John Doe',           // string: Customer name
    'getAllAvailableBanks' => true          // boolean: Get all available banks
];
```

**Optional Parameters:**

```php
$data['preferredBanks'] = ['035', '058'];  // array: Preferred bank codes
$data['restrictPaymentSource'] = true;      // boolean: Restrict payment sources
$data['allowedPaymentSource'] = [           // array: Allowed payment sources
    'bvns' => ['12345678901']
];
$data['incomeSplitConfig'] = [              // array: Split payment configuration
    [
        'subAccountCode' => 'SUB-ACC-123',
        'feePercentage' => 1.5,
        'splitPercentage' => 30.0,
        'feeBearer' => true
    ]
];
```

### Create Invoice Account

Creates a new reserved invoice account.

```php
Monnify::customerReservedAccount()->createInvoiceAccount($data);
```

**Required Parameters:**

```php
$data = [
    'contractCode' => 'your_contract_code',
    'accountName' => 'Account Name',
    'currencyCode' => 'NGN',
    'accountReference' => 'unique_reference',
    'customerEmail' => 'customer@email.com',
    'reservedAccountType' => 'INVOICE'
];
```

**Optional Parameters:**

```php
$data['customerName'] = 'Customer Name'; 
$data['bvn'] = '12345678901';
$data['nin'] = '000000009090897878'
```

### Get Account Details

Get the full reserved account detail.

```php
Monnify::customerReservedAccount()->get($accountReference);
```

**Parameters:**

- `$accountReference` (string): The reference of the main account

### Add Linked Accounts

Add additional accounts to a reserved account.

```php
Monnify::customerReservedAccount()->addLinkedAccounts($accountReference, $data);
```

**Parameters:**

- `$accountReference` (string): The reference of the main account
- `$data` (array): Linked accounts configuration

```php
$data = [
    'accountNames' => [
        [
            'preferredName' => 'Business Account',
            'accountName' => 'John Doe Business'
        ]
    ],
    'getAllAvailableBanks' => true,
    'preferredBanks' => ['035'] // Optional
];
```

### Deallocate Account

```php
Monnify::customerReservedAccount()->deallocateAccount($accountReference);
```

**Parameters:**

- `$accountReference` (string): The reference of the main account

### Update the BVN for a reserved account.

```php
Monnify::customerReservedAccount()->updateBVN($accountReference, $bvn);
```

**Parameters:**

- `$accountReference` (string): Account reference
- `$bvn` (string): Bank Verification Number

### Update the KYC Info for a reserved account.

```php
Monnify::customerReservedAccount()->updateKYCInfo($accountReference, $data);
```

**Parameters:**

- `$accountReference` (string): Account reference
- `$data` (array): KYC info (BVN, NIN or both)

```php
$data = [
    'bvn' => '21212121212',
    'nin' => ''
];
```

### Allowed Payment Source

```php
Monnify::customerReservedAccount()->allowedPaymentSource($accountReference, $data);
```

**Parameters:**

- `$accountReference` (string): Account reference
- `$data` (array): payment source setttings

```php
$data = [
    'restrictPaymentSource' => true,
    'allowedPaymentSources' => [
    	'bvns' => [
        	'21212121212',
        	'20202020202'
        ]
    ]
];
```

### Update Split Config for a reserved account.

```php
Monnify::customerReservedAccount()->updateSplitConfig($accountReference, $data);
```

**Parameters:**

- `$accountReference` (string): Account reference
- `$data` (array): split configs

```php
$data = [
    [
    	'subAccountCode' => 'MFY_SUB_305040939040',
        'feePercentage' => 10.50
    ]
];
```

### Get Account Transactions

```php
Monnify::customerReservedAccount()->transactions($accountReference, $parameters);
```

**Parameters:**
- `$accountReference` (string): Account reference
- `$parameters` (array): Optional parameters

```php
$parameters = [
    'page' => 0,     // integer: Page number (default: 0)
    'size' => 10     // integer: Items per page (default: 10)
];
```

## Invoice Service

Manage invoice creation and operations.

### All Available Methods

```php
// Create new invoice
Monnify::invoice()->create($data);    

// Get invoice details
Monnify::invoice()->get($invoiceReference);  

// Get all invoices
Monnify::invoice()->all();   

// Cancel invoice
Monnify::invoice()->cancel($invoiceReference); 

// Attach reserved account
Monnify::invoice()->attachReservedAccount($data); 
```

### Create a new Invoice

```php
Monnify::invoice()->create($data);
```
**Required Parameters:**

```php
$data = [
    'amount' => 1000.00,                    // float: Invoice amount
    'customerName' => 'John Doe',           // string: Customer name
    'customerEmail' => 'john@example.com',  // string: Customer email
    'expiryDate' => '2024-12-31',          // string: Invoice expiry date
    'invoiceReference' => 'INV-123',        // string: Unique invoice reference
    'description' => 'Service payment',     // string: Invoice description
    'currencyCode' => 'NGN',               // string: Currency code
    'contractCode' => 'CONTRACT-CODE'       // string: Your contract code
];
```

### Get Invoice Details

Retrieve details of a specific invoice.

```php
Monnify::invoice()->get($invoiceReference);
```

**Parameters:**

- `$invoiceReference` (string): The invoice reference to retrieve

### Retrieve all invoices.

```php
Monnify::invoice()->all();
```

### Cancel an existing invoice.

```php
Monnify::invoice()->cancel($invoiceReference);
```

**Parameters:**

- `$invoiceReference` (string): The invoice reference to cancel

### Attach a reserved account to an existing invoice.

```php
Monnify::invoice()->cancel($invoiceReference);
```

**Required Parameters:**

```php
$data = [
    'amount' => '999',
    'invoiceReference' => '18389131823445',
    'accountReference' => 'reference---1290034',
    'description' => 'test invoice',
    'currencyCode' => 'NGN',
    'contractCode' => config('monnify.contract_code'),
    'customerEmail' => 'janedoe@gmail.com',
    'customerName' => 'Jane Doe',
    'expiryDate' => '2025-04-30 12:00:00'
];
```

**Optional Parameters:**

```php
$data['incomeSplitConfig'] = [];  // array: income split config 
$data['redirectUrl'] = 'https://your-website.com';
```

## Disbursement Service

Handles money transfers and disbursements.

### All Available Methods

```php
// Single Transfers
Monnify::transfer()->single($data, $async);           

// Authorize single transfer
Monnify::transfer()->authoriseSingle($data);   

// Get single transfer status
Monnify::transfer()->singleStatus($reference);        

// Bulk Transfer
Monnify::transfer()->bulk($data);                     

// Authorize bulk transfer
Monnify::transfer()->authoriseBulk($data); 

// Get bulk status
Monnify::transfer()->bulkStatus($ref, $pageSize, $pageNumber); 

// Get transactions
Monnify::transfer()->bulkTransaction($ref, $pageSize, $pageNumber); 

/** Other Operations **/

// Resend OTP
Monnify::transfer()->resendOTP($reference);   

// Get all transfers
Monnify::transfer()->all($type, $pageSize, $pageNumber);

// Search
Monnify::transfer()->search($accountNumber, $pageSize, $pageNumber); 
```

### Single Transfer

Process a single money transfer.

```php
Monnify::transfer()->single($data, $async = false);
```

**Required Parameters:**

```php
$data = [
    'amount' => 1000.00,                    // float: Amount to transfer
    'reference' => 'TRANSFER-123',          // string: Unique transfer reference
    'narration' => 'Salary payment',        // string: Transfer description
    'destinationBankCode' => '058',         // string: Bank code
    'destinationAccountNumber' => '0123456789', // string: Account number
    'currency' => 'NGN',                    // string: Currency code
    'sourceAccountNumber' => '1234567890'   // string: Source account number
];
```

**Optional Parameters:**

- `$async` (boolean): Whether to process transfer asynchronously (default: false)

### Bulk Transfer

Process multiple transfers at once.

```php
Monnify::transfer()->bulk($data);
```

**Required Parameters:**
```php
$data = [
    'title' => 'Bulk Payments',             // string: Batch title
    'batchReference' => 'BULK-123',         // string: Unique batch reference
    'narration' => 'Monthly payments',      // string: Batch description
    'sourceAccountNumber' => '1234567890',  // string: Source account
    'onValidationFailure'  => 'CONTINUE',   // optional
    'notificationInterval' => 10,			// optional
    'transactions' => [                     // array: List of transfers
        [
            'amount' => 1000.00,
            'reference' => 'TRANSFER-1',
            'destinationBankCode' => '058',
            'destinationAccountNumber' => '0123456789',
            'narration' => 'Payment 1',
            'currency' => 'NGN'
        ],
        // More transactions...
    ]
];
```

### Authorize a transfer with OTP.

```php
Monnify::transfer()->authoriseSingle($data);  // For single transfer
Monnify::transfer()->authoriseBulk($data);    // For bulk transfer
```

**Required Parameters:**

```php
$data = [
    'reference' => 'TRANSFER-123',  		// string: Transfer reference
    'authorizationCode' => '123456'         // string: OTP received
];
```

### Check Transfer Status

```php
// Single transfer status
Monnify::transfer()->singleStatus($reference);  
// Bulk transfer status
Monnify::transfer()->bulkStatus($batchReference, $pageSize = 10, $pageNumber = 0);  
```

### Get Transaction

```php
// Bulk transfer status
Monnify::transfer()->bulkTransaction($batchReference, $pageSize = 10, $pageNumber = 0);  
```

### Other Operations

```php
// Resend OTP
Monnify::transfer()->resendOTP($reference); 
// Get all transfers       
Monnify::transfer()->all($type, $pageSize, $pageNumber); 
// Search
Monnify::transfer()->search($accountNumber, $pageSize, $pageNumber); 
```

**Parameters:**

- `$accountNumber` (string): Wallet account Number
- `$reference` (string): transaction reference
- `$type` (string): type of transaction (`single` or `bulk`)
- `$pageSize` (integer): Number of records per page (default: 10)
- `$pageNumber` (integer): Page number (default: 0)


## Wallet Service

Manages wallet operations.

### All Available Methods

```php
// Create wallet
Monnify::wallet()->create($data);     

// Get wallet details
Monnify::wallet()->get($customerEmail, $pageSize, $pageNumber); 

// Get balance
Monnify::wallet()->balance($accountNumber);   

// Get transactions
Monnify::wallet()->transactions($accountNumber, $pageSize, $pageNumber); 
```

### Create Wallet

```php
Monnify::wallet()->create($data);
```

**Required Parameters:**

```php
$data = [
    'customerEmail' => 'john@example.com',  // string: Customer email
    'customerName' => 'John Doe',           // string: Customer name
    'accountNumber' => '0123456789',        // string: Account number
    'accountName' => 'John Doe',            // string: Account name
    'bvnDetails' =>  [
    	'bvn' =>  '22222222226',			// string: BVN
        'bvnDateOfBirth' =>  '1994-09-07'	// string: Date of Birth
    ],
];
```

### Get Wallet Details

```php
Monnify::wallet()->get($customerEmail, $pageSize = 10, $pageNumber = 0);
```

**Parameters:**

- `$customerEmail` (string): Customer's email address
- `$pageSize` (integer): Number of records per page (default: 10)
- `$pageNumber` (integer): Page number (default: 0)


### Check Wallet Balance

```php
Monnify::wallet()->balance($accountNumber);
```

**Parameters:**
- `$accountNumber` (string): Wallet account number

### Get Wallet transactions

```php
Monnify::wallet()->transactions($accountNumber, $pageSize, $pageNumber);
```

**Parameters:**

- `$accountNumber` (string): Wallet account number
- `$pageSize` (integer): Number of records per page (default: 10)
- `$pageNumber` (integer): Page number (default: 0)

## Verification Service

### All Available Methods

```php
// Verify account
Monnify::verificationAPI()->bankAccount($accountNumber, $bankCode); 
// Verify BVN
Monnify::verificationAPI()->bvnInformation($data);        
// Match BVN         
Monnify::verificationAPI()->matchBVNAndBankAccount($bvn, $bankCode, $accountNumber); 
// Verify NIN
Monnify::verificationAPI()->nin($nin);                            
```

### Verify Bank Account

```php
Monnify::verificationAPI()->bankAccount($accountNumber, $bankCode);
```

**Required Parameters:**

- `$accountNumber` (string): Account number to verify
- `$bankCode` (string): Bank code

### Verify BVN Information

```php
Monnify::verificationAPI()->bvnInformation($data);
```

**Required Parameters:**

```php
$data = [
    'bvn' => '12345678901',           // string: BVN to verify
    'name' => 'John Doe',             // string: Name to match
    'dateOfBirth' => '1990-01-01'     // string: Date of birth (YYYY-MM-DD)
    'mobileNo' => '08142223149'		  // string: mobile number
];
```

### Match BVN with Bank Account

```php
Monnify::verificationAPI()->matchBVNAndBankAccount($bvn, $bankCode, $accountNumber);
```

**Required Parameters:**

- `$bvn` (string): BVN to match
- `$bankCode` (string): Bank code
- `$accountNumber` (string): Account number

### Verify NIN

```php
Monnify::verificationAPI()->nin($nin);
```

## Sub Account Service
Manages sub-accounts for split payments.

### All Available Methods

```php
// Create sub account
Monnify::subAccount()->create($data);   
// Get all sub accounts        
Monnify::subAccount()->all();   
// Update sub account                
Monnify::subAccount()->update($data);    
// Delete sub account       
Monnify::subAccount()->delete($subAccountCode); 
```

### Create Sub Account

Creates a new sub-account for split payments.

```php
Monnify::subAccount()->create($data);
```

**Required Parameters:**

```php
$data = [
    'bankCode' => '058',              // string: Bank code
    'accountNumber' => '0123456789',  // string: Account number
    'email' => 'sub@example.com',     // string: Sub-account email
    'currencyCode' => 'NGN'           // string: Currency code (NGN, USD, etc.)
    'defaultSplitPercentage' => 20.87 // integer: split percentage
];
```

### Get All Sub Accounts

Retrieves all sub-accounts associated with your contract.

```php
Monnify::subAccount()->all();
```

### Update Sub Account

Updates an existing sub-account's details.

```php
Monnify::subAccount()->update($data);
```

**Required Parameters:**

```php
$data = [
    'subAccountCode' => 'SUB-ACC-123',  // string: Sub account code
    'bankCode' => '058',                // string: New bank code
    'accountNumber' => '0123456789',    // string: New account number
    'email' => 'sub@example.com',       // string: New email
    'currencyCode' => 'NGN'             // string: Currency code
    'defaultSplitPercentage' => 20.87   // integer: split percentage
];
```

### Delete Sub Account

Removes a sub-account from your contract.

```php
Monnify::subAccount()->delete($subAccountCode);
```

**Required Parameters:**

- `$subAccountCode` (string): The unique code of the sub-account to delete

## Refund Service

### All Available Methods

```php
// Initialize a refund
Monnify::refund()->initialise($data);  
// Get all refunds            
Monnify::refund()->all($pageSize, $pageNumber);
// Check refund status    
Monnify::refund()->status($refundReference);       
```

### Initialize Refund

Creates a new refund request.

```php
Monnify::refund()->initialise($data);
```

**Required Parameters:**

```php
$data = [
    'transactionReference' => 'TRANS-123',  // string: Original transaction reference
    'refundReference' => 'REFUND-123',      // string: Unique refund reference
    'refundReason' => 'Customer request',   // string: Refund reason
    'refundAmount' => 1000.00,              // float: Amount to refund
    'customerNote' => 'An optional note',   // string: customer side note
];
```

### Get Refund Status

Check the status of a specific refund.

```php
Monnify::refund()->status($refundReference);
```

**Parameters:**

- `$refundReference` (string): Refund reference to check

### Get All Refunds

Retrieves all refunds with pagination.

```php
Monnify::refund()->all($pageSize = 10, $pageNumber = 0);
```

**Optional Parameters:**

- `$pageSize` (integer): Number of records per page (default: 10)
- `$pageNumber` (integer): Page number (default: 0)

## Settlement Service

Manages settlement information and transactions.

### All Available Methods

```php
// Get settlement transactions
Monnify::settlements()->transactions($settlementReference, $pageSize, $pageNumber); 
 // Get by transaction reference
Monnify::settlements()->getByTransaction($transactionReference);                   
```

### Get Settlement Transactions

Retrieves transactions for a specific settlement.

```php
Monnify::settlements()->transactions($settlementReference, $pageSize = 10, $pageNumber = 0);
```
**Required Parameters:**

- `$settlementReference` (string): Settlement reference to query

**Optional Parameters:**

```php
$pageSize = 10;    // integer: Number of records per page
$pageNumber = 0;   // integer: Page number for pagination
```

### Get Settlement by Transaction

Retrieves settlement details for a specific transaction.

```php
Monnify::settlements()->getByTransaction($transactionReference);
```

**Required Parameters:**

- `$transactionReference` (string): Transaction reference to query

## Limit Profile Service

Manages transaction limits.

### All Available Methods

```php
// Get all profiles
Monnify::limitProfile()->all(); 
// Create profile
Monnify::limitProfile()->create($data);  
// Update profile
Monnify::limitProfile()->update($limitProfileCode, $data);   
// Reserve account
Monnify::limitProfile()->reserveAccount($data);      
// Update account reserved account with Limit profile
Monnify::limitProfile()->updateReserveAccount($accountRef, $limitProfileCode); 
```

### Get All Limit Profiles

```php
Monnify::limitProfile()->all();
```

## Create Limit Profile
```php
Monnify::limitProfile()->create($data);
```

**Required Parameters:**

```php
$data = [
    'limitProfileName' => 'Basic Tier',     // string: Profile name
    'dailyTransactionLimit' => 1000000,     // float: Daily limit
    'dailyTransactionVolume' => 100,        // integer: Daily transaction count
    'singleTransactionLimit' => 100000      // float: Single transaction limit
];
```

### Update Limit Profile

```php
Monnify::limitProfile()->update($limitProfileCode, $data);
```

**Parameters:**

- `$limitProfileCode` (string): Profile code to update
- `$data` (array): New limit settings (same structure as create)

### Reserve Account with Limit

Creates a reserved account with specific limits.

```php
Monnify::limitProfile()->reserveAccount($data);
```

**Required Parameters:**

```php
$data = [
    'accountReference' => 'ACC-REF-123',    // string: Account reference
    'limitProfileCode' => 'LIMIT-123',      // string: Limit profile code
    'contractCode' => config('monnify.contract_code'),	// string: Contract code
    'accountName' => "Kan Yo' Reserved with Limit",		// string: Account Name
];
```

### Update Reserved Account with Limit Profile

```php
Monnify::limitProfile()-updateReserveAccount($accountReference, $limitProfileCode);
```

## Pay Code Service
Manages payment codes.

### All Available Methods

```php
// Create pay code
Monnify::payCodeAPI()->create($data); 
// Get pay code details
Monnify::payCodeAPI()->get($payCodeReference);
// Get unmasked pay code
Monnify::payCodeAPI()->getUnMasked($payCodeReference);
// Get pay code history
Monnify::payCodeAPI()->history($parameters);  
// Delete pay code
Monnify::payCodeAPI()->delete($payCodeReference);      
```

### Create Pay Code

```php
Monnify::payCodeAPI()->create($data);
```

**Required Parameters:**

```php
$data = [
    'amount' => 1000.00,                    // float: Amount
    'paycodeReference' => 'PAYCODE-123',    // string: Unique reference
    'beneficiaryName' => 'John Doe',        // string: Beneficiary name
    'clientId' => 'sEYUG-123',  			// string: Client Id
    'expiryDate' => '2024-12-31'            // string: Expiry date
];
```

### Get Pay Code Details

Retrieve the full detail of a payment code

```php
Monnify::payCodeAPI()->get($payCodeReference);
```

### Get Pay Code Details with Pay Code Unmasked

Retrieve the full detail of a payment code Unmasked

```php
Monnify::payCodeAPI()->getUnMasked($payCodeReference);
```

### Get Pay Code History

Retrieves history of payment codes.

```php
Monnify::payCodeAPI()->history($parameters);
```

**Parameters:**

```php
$parameters = [
	'transactionReference' => '', // string: Transaction Reference
    'beneficiaryName' => '',	 // string: Beneficiary Name
    'transactionStatus' => '',	 // string: Transaction status
    'from' => '',				 // string: Start date (YYYY-MM-DD)
    'to' => ''					 // string: End date (YYYY-MM-DD)
];
```

### Delete a payment code

```php
Monnify::payCodeAPI()->delete($payCodeReference);
```

## Direct Debit Service

Manages direct debit mandates.

### All Available Methods

```php
// Create mandate
Monnify::directDebitMandate()->create($data);    
// Get mandate details
Monnify::directDebitMandate()->get($mandateReference);     
// Debit mandate
Monnify::directDebitMandate()->debit($data);       
// Get mandate status
Monnify::directDebitMandate()->status($paymentReference);  
// Cancel mandate
Monnify::directDebitMandate()->cancel($mandateCode);       
```

### Create Mandate

```php
Monnify::directDebitMandate()->create($data);
```

**Required Parameters:**

```php
$data = [
    'contractCode' => config('monnify.contract_code'),
    'mandateReference' => 'unique_ref3_02s600972',
    'customerName' => 'Ankit Kushwaha',
    'customerPhoneNumber' => '1234567890',
    'customerEmailAddress' => 'ankit.kushwaha@gmail.com',
    'customerAddress' => '123 Example Street, City, Country',
    'customerAccountNumber' => '0051762787',
    'customerAccountBankCode' => '058',
    'mandateDescription' => 'Subscription Fee',
    'mandateStartDate' => '2024-05-22T10:15:30',
    'mandateEndDate' => '2025-05-22T10:15:30'
];
```

**Optional Parameters:**

```php
	'autoRenew' => false,
    'customerCancellation' => true,
    'customerAccountName' => 'Ankit Kushwaha',
```

### Get Mandate Details

Get full detail of a mandate payment.

```php
Monnify::directDebitMandate()->get($mandateReference);
```

**Required Parameters:**

- `$mandateReference` (string): Mandate reference

### Debit Mandate

Executes a debit on an existing mandate.

```php
Monnify::directDebitMandate()->debit($data);
```

**Required Parameters:**

```php
$data = [
    'mandateCode' => 'MANDATE-123',    // string: Mandate code
    'amount' => 1000.00,                    // float: Amount to debit
    'paymentReference' => 'PAYMENT-123',    // string: Unique payment reference
    'narration' => 'Monthly subscription'   // string: Transaction description
    'customerEmail' =>'ahsan.saleem@gmail.com' // string: Cunstomer Email
];
```

### Get Mandate Status

Checks the status of a mandate payment.

```php
Monnify::directDebitMandate()->status($paymentReference);
```

**Required Parameters:**

- `$paymentReference` (string): Payment reference to check

### Cancel Mandate Payment

```php
Monnify::directDebitMandate()->cancel($mandateCode);
```

**Required Parameters:**

- `$mandateCode` (string): Mandate code


## Recurring Payment Service

### All Available Methods

```php
// Charge card using token
Monnify::recurringPayment()->chargeCardToken($data); 
```

### Charge Card Token

```php
Monnify::recurringPayment()->chargeCardToken($data);
```

**Required Parameters:**

```php
$data = [
    'cardToken' => 'MNFY_0CD0138B45F7C3EC6D3698969',
    'amount' => 20,
    'customerEmail' => 'benjikali29@gmail.com',
    'paymentReference' => '1642776mml0068n2937',
    'contractCode' => config('monnify.contract_code'),
    'apiKey' => config('monnify.api_key'),
];
```

**Optional Parameters:**

```php
	'customerName' => 'Marvelous Benji',
    'paymentDescription' => 'Paying for Product A',
    'currencyCode' => 'NGN',
    'incomeSplitConfig' => [],
    'metaData' => [
    	'ipAddress' => '127.0.0.1',
        'deviceType' => 'mobile'
    ]
```

## Other / Helper Service

Provides utility functions.

### All Available Methods

```php
// Get all banks
Monnify::helper()->banks(); 
      
// Get banks with USSD  
Monnify::helper()->banksWithUSSD(); 
```

## Error Handling

Wrap API calls in try-catch blocks to handle exceptions efficiently:

```php
try {
    $response = Monnify::transactions()->initialise($data);
} catch (Exception $e) {
    $error = $e->getMessage();
}
```

## Testing

Run package tests with:

```bash
composer test
```

## Contributing

- Fork repository
- Create feature/fix branch
- Submit Pull Request

## Credits

- [Babatunde Adelabu](https://github.com/fredneutron)
- [Aransiola Ayodele](https://github.com/CodeLeom)


## License

This package is licensed under the [MIT License](LICENSE.md).


## Support

For any support or security issues, please contact [integration-support@monnify.com](mailto:integration-support@monnify.com).
