<?php

namespace Botble\Payment\Enums;

use Botble\Base\Supports\Enum;

/**
 * method static PaymentMethodEnum COD()
 * method static PaymentMethodEnum BANK_TRANSFER()
 */
/**
 * @method static PaymentMethodEnum LOAN()
 * @method static PaymentMethodEnum SAVINGS()
 * @method static PaymentMethodEnum PAYSTACK()
 * @method static PaymentMethodEnum COD()
 * @method static PaymentMethodEnum BANK_TRANSFER()
 */
class PaymentMethodEnum extends Enum
{
    #public const COD = 'cod';
    #public const BANK_TRANSFER = 'bank_transfer';

    public const LOAN = 'loan';
    public const COD = 'cod';
    public const SAVINGS = 'savings';
    public const PAYSTACK = 'paystack';
    public const BANK_TRANSFER = 'bank_transfer';

    public static $langPath = 'plugins/payment::payment.methods';

    public function getServiceClass(): ?string
    {
        return apply_filters(PAYMENT_FILTER_GET_SERVICE_CLASS, null, (string)$this->value);
    }
}
