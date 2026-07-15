<?php

namespace Monnify\MonnifyLaravel\Enums;

enum WebhookEventType: string
{
    case SuccessfulTransaction = 'SUCCESSFUL_TRANSACTION';
    case SuccessfulDisbursement = 'SUCCESSFUL_DISBURSEMENT';
    case FailedDisbursement = 'FAILED_DISBURSEMENT';
    case ReversedDisbursement = 'REVERSED_DISBURSEMENT';
    case SuccessfulRefund = 'SUCCESSFUL_REFUND';
    case FailedRefund = 'FAILED_REFUND';
    case Settlement = 'SETTLEMENT';
    case RejectedPayment = 'REJECTED_PAYMENT';
    case MandateUpdate = 'MANDATE_UPDATE';
    case AccountActivity = 'ACCOUNT_ACTIVITY';
    case LowBalanceAlert = 'LOW_BALANCE_ALERT';
    case OfflinePaymentAgent = 'OFFLINE_PAYMENT_AGENT';
}
