<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Reports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * Form Request for Vendors Report API
 */
class VendorsReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'order_status' => ['nullable', 'string', 'in:pending,confirmed,processing,shipped,delivered,completed,cancelled,returned'],
            'sort_by' => ['nullable', 'string', 'in:revenue,orders,products'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'group_by' => ['nullable', 'string', 'in:daily,weekly,monthly'],

            // For comparison endpoint
            'compare_start_date' => ['nullable', 'date'],
            'compare_end_date' => ['nullable', 'date', 'after_or_equal:compare_start_date'],
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
            'end_date.after_or_equal' => __('lang.end_date_after_start_date'),
            'vendor_id.exists' => __('lang.vendor_not_found'),
            'category_id.exists' => __('lang.category_not_found'),
            'order_status.in' => __('lang.invalid_order_status'),
            'sort_by.in' => __('lang.invalid_sort_by_vendors'),
            'sort_order.in' => __('lang.invalid_sort_order'),
            'limit.min' => __('lang.limit_must_be_positive'),
            'limit.max' => __('lang.limit_max_100'),
            'group_by.in' => __('lang.invalid_group_by_parameter'),
            'compare_end_date.after_or_equal' => __('lang.end_date_after_start_date'),
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
            'category_id' => __('lang.category'),
            'order_status' => __('lang.order_status'),
            'sort_by' => __('lang.sort_by'),
            'sort_order' => __('lang.sort_order'),
            'limit' => __('lang.limit'),
            'group_by' => __('lang.group_by'),
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
                'errors' => $validator->errors(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
