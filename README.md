# Laravel Monnify Package

A Laravel package for seamless integration with the Monnify payment gateway API.

## Installation

You can install the package via composer:

```bash
composer require monnify/monnify-laravel
```

## Configuration

1. Publish the configuration file:

```bash
php artisan vendor:publish --provider="Monnify\MonnifyLaravel\MonnifyServiceProvider"
```

2. Add the following variables to your `.env` file:

```env
MONNIFY_API_KEY=your_api_key
MONNIFY_SECRET_KEY=your_secret_key
MONNIFY_CONTRACT_CODE=your_contract_code
MONNIFY_WALLET_ACCOUNT_NUMBER=your_wallet_number
MONNIFY_ACCOUNT_NUMBER=your_account_number
MONNIFY_ENVIRONMENT=SANDBOX # or LIVE
```

## Usage

### Basic Usage

```php
use Monnify\MonnifyLaravel\Facades\Monnify;

// Initialize a transaction
$transactionData = [
    'amount' => 100.00,
    'customerName' => 'John3 Doe',
    'customerEmail' => 'john3@example.com',
    'paymentReference' => 'Trial transaction 3',
    'paymentDescription' => 'Payment for services',
    'currencyCode' => 'NGN',
    'contractCode' => config('monnify.contract_code'),
    'redirectUrl' => 'https://my-merchants-page.com/transaction/confirm',
    'paymentMethods' => [
        'CARD',
        'ACCOUNT_TRANSFER'
    ],
];

$response = Monnify::transactions()->initialise($transactionData);
```

## Detailed Service Documentation

### 1. Transaction Service
The Transaction Service handles all payment-related operations.

#### All Available Methods
```php
// Payment Initialization
Monnify::transactions()->initialise($data);                    // Initialize a new transaction
Monnify::transactions()->payWithBankTransfer($data);          // Initialize bank transfer payment
Monnify::transactions()->chargeCard($data);                   // Charge a card

// Card Authorization
Monnify::transactions()->authorizeOTP($data);                 // Authorize with OTP
Monnify::transactions()->authorizeThreeDSCard($data);         // Authorize 3D secure card

// Transaction Information
Monnify::transactions()->all();                   	          // Get all transactions
Monnify::transactions()->status($transactionReference);       // Get transaction status
Monnify::transactions()->statusByReference($reference, $type); // Get status by reference
```

#### Initialize Transaction
Creates a new payment transaction.
```php
Monnify::transactions()->initialise($data);
```
**Required Parameters:**
```php
$data = [
    'amount' => 1000.00,              // float: Amount to be paid
    'customerName' => 'John Doe',      // string: Customer's full name
    'customerEmail' => 'john@example.com', // string: Customer's email
    'paymentReference' => 'UNIQUE-REF-123', // string: Unique payment reference
    'paymentDescription' => 'Payment for services', // string: Description
    'currencyCode' => 'NGN',          // string: Currency code (NGN, USD, etc.)
    'contractCode' => 'CONTRACT-CODE', // string: Your Monnify contract code
    'redirectUrl' => 'https://your-website.com/callback', // string: Redirect URL
];
```
**Optional Parameters:**
```php
$data['paymentMethods'] = ['CARD', 'ACCOUNT_TRANSFER']; // array: Payment methods
$data['incomeSplitConfig'] = [        // array: Split payment configuration
    [
        'subAccountCode' => 'MFY_SUB_319452883228',
        'feePercentage' => 2.0,
        'splitPercentage' => 20.0,
        'feeBearer' => true
    ]
];
```

#### Pay with Bank Transfer
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

#### Charge Card
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
#### Authorize OTP
Authorize with OTP
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

#### Authorize 3DS Card
Authorize 3D secure card
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

#### Get Transaction Status
Check the status of a transaction.
```php
Monnify::transactions()->status($transactionReference);
```
**Parameters:**
- `$transactionReference` (string): The transaction reference to check

#### Get Status by Reference
Check transaction status using different reference types.
```php
Monnify::transactions()->statusByReference($reference, $referenceType);
```
**Parameters:**
- `$reference` (string): The reference number
- `$referenceType` (string): Either 'transaction' or 'payment' (default: 'transaction')

### 2. Customer Reserved Account Service
Manages reserved account operations.

#### All Available Methods
```php
// Account Creation and Management
Monnify::customerReservedAccount()->createGeneralAccount($data);    // Create general account
Monnify::customerReservedAccount()->createInvoiceAccount($data);    // Create invoice account
Monnify::customerReservedAccount()->get($accountReference);         // Get account details
Monnify::customerReservedAccount()->addLinkedAccounts($ref, $data); // Add linked accounts
Monnify::customerReservedAccount()->deallocateAccount($ref);        // Remove account

// Account Updates
Monnify::customerReservedAccount()->updateBVN($ref, $bvn);         // Update BVN
Monnify::customerReservedAccount()->updateKYCInfo($ref, $data);    // Update KYC info
Monnify::customerReservedAccount()->allowedPaymentSource($ref, $data); // Update payment sources
Monnify::customerReservedAccount()->updateSplitConfig($ref, $data); // Update split config

// Transaction Information
Monnify::customerReservedAccount()->transactions($ref, $params);    // Get transactions
```

#### Create General Account
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

#### Create Invoice Account
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

#### Get Account Details
Get the full reserved account detail.
```php
Monnify::customerReservedAccount()->get($accountReference);
```
**Parameters:**
- `$accountReference` (string): The reference of the main account

#### Add Linked Accounts
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

#### Deallocate Account
Remove account.
```php
Monnify::customerReservedAccount()->deallocateAccount($accountReference);
```
**Parameters:**
- `$accountReference` (string): The reference of the main account

#### Update BVN
Update the BVN for a reserved account.
```php
Monnify::customerReservedAccount()->updateBVN($accountReference, $bvn);
```
**Parameters:**
- `$accountReference` (string): Account reference
- `$bvn` (string): Bank Verification Number

#### Update KYC Info
Update the KYC Info for a reserved account.
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

#### Allowed Payment Source
Update payment sources for a reserved account.
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

#### Update Split Config
Update Split Config for a reserved account.
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

#### Get Account Transactions
Retrieve transactions for a reserved account.
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

### 3. Invoice Service
Manages invoice creation and operations.

#### All Available Methods
```php
// Invoice Management
Monnify::invoice()->create($data);                // Create new invoice
Monnify::invoice()->get($invoiceReference);       // Get invoice details
Monnify::invoice()->all();                        // Get all invoices
Monnify::invoice()->cancel($invoiceReference);    // Cancel invoice
Monnify::invoice()->attachReservedAccount($data); // Attach reserved account
```

#### Create Invoice
Creates a new invoice.
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

#### Get Invoice Details
Retrieve details of a specific invoice.
```php
Monnify::invoice()->get($invoiceReference);
```
**Parameters:**
- `$invoiceReference` (string): The invoice reference to retrieve

#### Get All Invoices
Retrieve all invoices.
```php
Monnify::invoice()->all();
```

#### Cancel Invoice
Cancel an existing invoice.
```php
Monnify::invoice()->cancel($invoiceReference);
```
**Parameters:**
- `$invoiceReference` (string): The invoice reference to cancel

#### Attach Reserved Account
Attach a reserved account to an existing invoice.
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


### 4. Disbursement Service
Handles money transfers and disbursements.

#### All Available Methods
```php
// Single Transfers
Monnify::transfer()->single($data, $async);           // Single transfer
Monnify::transfer()->authoriseSingle($data);          // Authorize single transfer
Monnify::transfer()->singleStatus($reference);        // Get single transfer status

// Bulk Transfers
Monnify::transfer()->bulk($data);                     // Bulk transfer
Monnify::transfer()->authoriseBulk($data);           // Authorize bulk transfer
Monnify::transfer()->bulkStatus($ref, $pageSize, $pageNumber); // Get bulk status
Monnify::transfer()->bulkTransaction($ref, $pageSize, $pageNumber); // Get transactions

// Other Operations
Monnify::transfer()->resendOTP($reference);          // Resend OTP
Monnify::transfer()->all($type, $pageSize, $pageNumber); // Get all transfers
Monnify::transfer()->search($accountNumber, $pageSize, $pageNumber); // Search
```

#### Single Transfer
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

#### Bulk Transfer
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

#### Authorize Transfer
Authorize a transfer with OTP.
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

#### Check Transfer Status
```php
Monnify::transfer()->singleStatus($reference);  // Single transfer status
Monnify::transfer()->bulkStatus($batchReference, $pageSize = 10, $pageNumber = 0);  // Bulk transfer status
```

#### Get Transaction
```php
Monnify::transfer()->bulkTransaction($batchReference, $pageSize = 10, $pageNumber = 0);  // Bulk transfer status
```

#### Other Operations

```php
Monnify::transfer()->resendOTP($reference);          // Resend OTP
Monnify::transfer()->all($type, $pageSize, $pageNumber); // Get all transfers
Monnify::transfer()->search($accountNumber, $pageSize, $pageNumber); // Search
```
**Parameters:**
- `$accountNumber` (string): Wallet account Number
- `$reference` (string): transaction reference
- `$type` (string): type of transaction (`single` or `bulk`)
- `$pageSize` (integer): Number of records per page (default: 10)
- `$pageNumber` (integer): Page number (default: 0)


### 5. Wallet Service
Manages wallet operations.

#### All Available Methods
```php
// Wallet Operations
Monnify::wallet()->create($data);                              // Create wallet
Monnify::wallet()->get($customerEmail, $pageSize, $pageNumber); // Get wallet details
Monnify::wallet()->balance($accountNumber);                     // Get balance
Monnify::wallet()->transactions($accountNumber, $pageSize, $pageNumber); // Get transactions
```

#### Create Wallet
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

#### Get Wallet Details
```php
Monnify::wallet()->get($customerEmail, $pageSize = 10, $pageNumber = 0);
```
**Parameters:**
- `$customerEmail` (string): Customer's email address
- `$pageSize` (integer): Number of records per page (default: 10)
- `$pageNumber` (integer): Page number (default: 0)

#### Check Wallet Balance
```php
Monnify::wallet()->balance($accountNumber);
```
**Parameters:**
- `$accountNumber` (string): Wallet account number

#### Get Wallet transactions
```php
Monnify::wallet()->transactions($accountNumber, $pageSize, $pageNumber);
```
**Parameters:**
- `$accountNumber` (string): Wallet account number
- `$pageSize` (integer): Number of records per page (default: 10)
- `$pageNumber` (integer): Page number (default: 0)

### 6. Verification Service
Handles various verification operations.

#### All Available Methods
```php
// Verification Operations
Monnify::verificationAPI()->bankAccount($accountNumber, $bankCode); // Verify account
Monnify::verificationAPI()->bvnInformation($data);                 // Verify BVN
Monnify::verificationAPI()->matchBVNAndBankAccount($bvn, $bankCode, $accountNumber); // Match BVN
Monnify::verificationAPI()->nin($nin);                            // Verify NIN
```

#### Verify Bank Account
```php
Monnify::verificationAPI()->bankAccount($accountNumber, $bankCode);
```
**Required Parameters:**
- `$accountNumber` (string): Account number to verify
- `$bankCode` (string): Bank code

#### Verify BVN Information
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

#### Match BVN with Bank Account
```php
Monnify::verificationAPI()->matchBVNAndBankAccount($bvn, $bankCode, $accountNumber);
```
**Required Parameters:**
- `$bvn` (string): BVN to match
- `$bankCode` (string): Bank code
- `$accountNumber` (string): Account number

#### Verify NIN
```php
Monnify::verificationAPI()->nin($nin);
```

### 7. Sub Account Service
Manages sub-accounts for split payments.

#### All Available Methods
```php
// Sub Account Operations
Monnify::subAccount()->create($data);           // Create sub account
Monnify::subAccount()->all();                   // Get all sub accounts
Monnify::subAccount()->update($data);           // Update sub account
Monnify::subAccount()->delete($subAccountCode); // Delete sub account
```

#### Create Sub Account
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

#### Get All Sub Accounts
Retrieves all sub-accounts associated with your contract.
```php
Monnify::subAccount()->all();
```

#### Update Sub Account
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

#### Delete Sub Account
Removes a sub-account from your contract.
```php
Monnify::subAccount()->delete($subAccountCode);
```
**Required Parameters:**
- `$subAccountCode` (string): The unique code of the sub-account to delete


### 8. Refund Service
Handles payment refunds.

#### All Available Methods
```php
// Refund Operations
Monnify::refund()->initialise($data);              // Initialize a refund
Monnify::refund()->all($pageSize, $pageNumber);    // Get all refunds
Monnify::refund()->status($refundReference);       // Check refund status
```

#### Initialize Refund
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

#### Get Refund Status
Check the status of a specific refund.
```php
Monnify::refund()->status($refundReference);
```
**Parameters:**
- `$refundReference` (string): Refund reference to check

#### Get All Refunds
Retrieves all refunds with pagination.
```php
Monnify::refund()->all($pageSize = 10, $pageNumber = 0);
```
**Optional Parameters:**
- `$pageSize` (integer): Number of records per page (default: 10)
- `$pageNumber` (integer): Page number (default: 0)


### 9. Settlement Service
Manages settlement information and transactions.

#### All Available Methods
```php
// Settlement Operations
Monnify::settlements()->transactions($settlementReference, $pageSize, $pageNumber); // Get settlement transactions
Monnify::settlements()->getByTransaction($transactionReference);                    // Get by transaction reference
```

#### Get Settlement Transactions
Retrieves transactions for a specific settlement.
```php
Monnify::settlements()->transactions($settlementReference, $pageSize = 10, $pageNumber = 0);
```
**Required Parameters:**
- `$settlementReference` (string): Settlement reference to 
query

**Optional Parameters:**
```php
$pageSize = 10;    // integer: Number of records per page
$pageNumber = 0;   // integer: Page number for pagination
```

#### Get Settlement by Transaction
Retrieves settlement details for a specific transaction.
```php
Monnify::settlements()->getByTransaction($transactionReference);
```
**Required Parameters:**
- `$transactionReference` (string): Transaction reference to query

### 10. Limit Profile Service
Manages transaction limits.

#### All Available Methods
```php
// Limit Profile Operations
Monnify::limitProfile()->all();                                   // Get all profiles
Monnify::limitProfile()->create($data);                           // Create profile
Monnify::limitProfile()->update($limitProfileCode, $data);        // Update profile
Monnify::limitProfile()->reserveAccount($data);                   // Reserve account
Monnify::limitProfile()->updateReserveAccount($accountRef, $limitProfileCode); // Update account reserved account with Limit profile
```

#### Get All Limit Profiles
```php
Monnify::limitProfile()->all();
```

#### Create Limit Profile
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

#### Update Limit Profile
```php
Monnify::limitProfile()->update($limitProfileCode, $data);
```
**Parameters:**
- `$limitProfileCode` (string): Profile code to update
- `$data` (array): New limit settings (same structure as create)

#### Reserve Account with Limit
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

#### Update Reserved Account with Limit Profile
```php
Monnify::limitProfile()-updateReserveAccount($accountReference, $limitProfileCode);
```

### 11. Pay Code Service
Manages payment codes.

#### All Available Methods
```php
// Pay Code Operations
Monnify::payCodeAPI()->create($data);                  // Create pay code
Monnify::payCodeAPI()->get($payCodeReference);         // Get pay code details
Monnify::payCodeAPI()->getUnMasked($payCodeReference); // Get unmasked pay code
Monnify::payCodeAPI()->history($parameters);           // Get pay code history
Monnify::payCodeAPI()->delete($payCodeReference);      // Delete pay code
```

#### Create Pay Code
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

#### Get Pay Code Details
Retrieve the full detail of a payment code
```php
Monnify::payCodeAPI()->get($payCodeReference);
```

#### Get Pay Code Details with Pay Code Unmasked
Retrieve the full detail of a payment code Unmasked
```php
Monnify::payCodeAPI()->getUnMasked($payCodeReference);
```

#### Get Pay Code History
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

#### Delete Pay Code
Delete a payment code
```php
Monnify::payCodeAPI()->delete($payCodeReference);
```

### 12. Direct Debit Service
Manages direct debit mandates.

#### All Available Methods
```php
// Direct Debit Operations
Monnify::directDebitMandate()->create($data);              // Create mandate
Monnify::directDebitMandate()->get($mandateReference);     // Get mandate details
Monnify::directDebitMandate()->debit($data);               // Debit mandate
Monnify::directDebitMandate()->status($paymentReference);  // Get mandate status
Monnify::directDebitMandate()->cancel($mandateCode);       // Cancel mandate
```

#### Create Mandate
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

#### Get Mandate Details
Get full detail of a mandate payment.
```php
Monnify::directDebitMandate()->get($mandateReference);
```
**Required Parameters:**
- `$mandateReference` (string): Mandate reference


#### Debit Mandate
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

#### Get Mandate Status
Checks the status of a mandate payment.
```php
Monnify::directDebitMandate()->status($paymentReference);
```
**Required Parameters:**
- `$paymentReference` (string): Payment reference to check

#### Cancel Mandate Payment
Cancel mandate payment.
```php
Monnify::directDebitMandate()->cancel($mandateCode);
```
**Required Parameters:**
- `$mandateCode` (string): Mandate code


### 13. Recurring Payment Service
Handles recurring payments.

#### All Available Methods
```php
// Recurring Payment Operations
Monnify::recurringPayment()->chargeCardToken($data); // Charge card using token
```

#### Charge Card Token
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


### 14. Other / Helper Service
Provides utility functions.

#### All Available Methods
```php
// Helper Operations
Monnify::helper()->banks();         // Get all banks
Monnify::helper()->banksWithUSSD(); // Get banks with USSD
```

Each service method returns an array containing the API response. Always wrap your API calls in try-catch blocks to handle potential errors:

## Error Handling

The package throws exceptions for various error cases. It's recommended to wrap your API calls in try-catch blocks:

```php
try {
    $response = Monnify::transactions()->initialise($data);
} catch (Exception $e) {
    // Handle the error
    $errorMessage = $e->getMessage();
}
```

## Testing

```bash
composer test
```

## Security

If you discover any security-related issues, please email Adelabu4fred@gmail.com instead of using the issue tracker.

## Credits

- [Babatunde Adelabu](https://github.com/fredneutron)
<!-- - [All Contributors](../../contributors) -->

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please contact [Babatunde Adelabu](https://github.com/fredneutron).