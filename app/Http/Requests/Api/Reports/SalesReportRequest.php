<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Reports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Sales Report Request Validation
 * 
 * Validates all input parameters for sales report endpoints.
 */
class SalesReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'status' => ['nullable', 'string', 'in:pending,confirmed,processing,shipped,delivered,completed,cancelled,returned'],
            'payment_status' => ['nullable', 'string', 'in:pending,partial,paid,refunded'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'group_by' => ['nullable', 'string', 'in:daily,weekly,monthly'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],

            // For period comparison
            'compare_start_date' => ['nullable', 'date', 'required_with:compare_end_date'],
            'compare_end_date' => ['nullable', 'date', 'required_with:compare_start_date', 'after_or_equal:compare_start_date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.before_or_equal' => __('lang.start_date_before_end_date'),
            'end_date.after_or_equal' => __('lang.end_date_after_start_date'),
            'vendor_id.exists' => __('lang.vendor_not_found'),
            'customer_id.exists' => __('lang.customer_not_found'),
            'status.in' => __('lang.invalid_order_status'),
            'payment_status.in' => __('lang.invalid_payment_status'),
            'category_id.exists' => __('lang.category_not_found'),
            'group_by.in' => __('lang.invalid_group_by_parameter'),
            'limit.min' => __('lang.limit_must_be_positive'),
            'limit.max' => __('lang.limit_max_100'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'start_date' => __('lang.start_date'),
            'end_date' => __('lang.end_date'),
            'vendor_id' => __('lang.vendor'),
            'customer_id' => __('lang.customer'),
            'status' => __('lang.order_status'),
            'payment_status' => __('lang.payment_status'),
            'category_id' => __('lang.category'),
            'group_by' => __('lang.group_by'),
            'limit' => __('lang.limit'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'message' => __('lang.validation_error'),
                'data' => $validator->errors(),
            ], 422)
        );
    }
}
