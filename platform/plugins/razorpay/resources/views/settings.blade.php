@php $razorpayStatus = get_payment_setting('status', RAZORPAY_PAYMENT_METHOD_NAME); @endphp
<table class="table payment-method-item">
    <tbody>
    <tr class="border-pay-row">
        <td class="border-pay-col"><i class="fa fa-theme-payments"></i></td>
        <td style="width: 20%;">
            <img class="filter-black" src="{{ url('vendor/core/plugins/razorpay/images/razorpay.svg') }}"
                 alt="Razorpay">
        </td>
        <td class="border-right">
            <ul>
                <li>
                    <a href="https://razorpay.com" target="_blank">Razorpay</a>
                    <p>{{ __('Customer can buy product and pay directly using Visa, Credit card via :name', ['name' => 'Razorpay']) }}</p>
                </li>
            </ul>
        </td>
    </tr>
    <tr class="bg-white">
        <td colspan="3">
            <div class="float-start" style="margin-top: 5px;">
                <div
                    class="payment-name-label-group @if (get_payment_setting('status', RAZORPAY_PAYMENT_METHOD_NAME) == 0) hidden @endif">
                    <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span> <label
                        class="ws-nm inline-display method-name-label">{{ get_payment_setting('name', RAZORPAY_PAYMENT_METHOD_NAME) }}</label>
                </div>
            </div>
            <div class="float-end">
                <a class="btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger @if ($razorpayStatus == 0) hidden @endif">{{ trans('plugins/payment::payment.edit') }}</a>
                <a class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger @if ($razorpayStatus == 1) hidden @endif">{{ trans('plugins/payment::payment.settings') }}</a>
            </div>
        </td>
    </tr>
    <tr class="payment-content-item hidden">
        <td class="border-left" colspan="3">
            {!! Form::open() !!}
            {!! Form::hidden('type', RAZORPAY_PAYMENT_METHOD_NAME, ['class' => 'payment_type']) !!}
            <div class="row">
                <div class="col-sm-6">
                    <ul>
                        <li>
                            <label>{{ trans('plugins/payment::payment.configuration_instruction', ['name' => 'Razorpay']) }}</label>
                        </li>
                        <li class="payment-note">
                            <p>{{ trans('plugins/payment::payment.configuration_requirement', ['name' => 'Razorpay']) }}
                                :</p>
                            <ul class="m-md-l" style="list-style-type:decimal">
                                <li style="list-style-type:decimal">
                                    <a href="https://razorpay.com" target="_blank">
                                        {{ __('Register an account on :name', ['name' => 'Razorpay']) }}
                                    </a>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ __('After registration at :name, you will have Client ID, Client Secret', ['name' => 'Razorpay']) }}</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ __('Enter Client ID, Secret into the box in right hand') }}</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{!! BaseHelper::clean("Then you need to create a new webhook. To create a webhook, go to <strong>Account Settings</strong> -> <strong>API keys</strong> -> <strong>Webhooks</strong> and paste the below url to <strong>Webhook URL</strong> field. At <strong>Active Events</strong> field, check to <strong>Payment Events</strong> and <strong>Order Events</strong> checkbox.") !!}</p>
                                    <p>
                                        <code>{{ route('payments.razorpay.webhook') }}</code>
                                    </p>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-sm-6">
                    <div class="well bg-white">
                        <x-core-setting::text-input
                            :name="'payment_' . RAZORPAY_PAYMENT_METHOD_NAME . '_name'"
                            :label="trans('plugins/payment::payment.method_name')"
                            :value="get_payment_setting('name', RAZORPAY_PAYMENT_METHOD_NAME, __('Online payment via :name', ['name' => 'Razorpay']))"
                            data-counter="400"
                        />

                        <div class="form-group mb-3">
                            <label class="text-title-field" for="payment_{{ RAZORPAY_PAYMENT_METHOD_NAME }}_description">{{ trans('core/base::forms.description') }}</label>
                            <textarea class="next-input" name="payment_{{ RAZORPAY_PAYMENT_METHOD_NAME }}_description" id="payment_{{ RAZORPAY_PAYMENT_METHOD_NAME }}_description">{{ get_payment_setting('description', RAZORPAY_PAYMENT_METHOD_NAME, __('Payment with :paymentType', ['paymentType' => 'Razorpay'])) }}</textarea>
                        </div>

                        <p class="payment-note">
                            {{ trans('plugins/payment::payment.please_provide_information') }} <a target="_blank" href="https://razorpay.com">Razorpay</a>:
                        </p>

                        <x-core-setting::text-input
                            :name="'payment_' . RAZORPAY_PAYMENT_METHOD_NAME . '_key'"
                            :label="__('Key')"
                            :value="get_payment_setting('key', RAZORPAY_PAYMENT_METHOD_NAME)"
                            placeholder="rzp_***"
                        />

                        <x-core-setting::text-input
                            :name="'payment_' . RAZORPAY_PAYMENT_METHOD_NAME . '_secret'"
                            type="password"
                            :label="__('Secret')"
                            :value="get_payment_setting('secret', RAZORPAY_PAYMENT_METHOD_NAME)"
                            placeholder="••••••••"
                        />

                        {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, RAZORPAY_PAYMENT_METHOD_NAME) !!}
                    </div>
                </div>
            </div>
            <div class="col-12 bg-white text-end">
                <button class="btn btn-warning disable-payment-item @if ($razorpayStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.deactivate') }}</button>
                <button class="btn btn-info save-payment-item btn-text-trigger-save @if ($razorpayStatus == 1) hidden @endif" type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                <button class="btn btn-info save-payment-item btn-text-trigger-update @if ($razorpayStatus == 0) hidden @endif" type="button">{{ trans('plugins/payment::payment.update') }}</button>
            </div>
            {!! Form::close() !!}
        </td>
    </tr>
    </tbody>
</table>
