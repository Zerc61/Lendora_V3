<?php

namespace App\Http\Requests;

use App\Models\Asset;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Booking::class);
    }

    public function rules(): array
    {
        return [
            'assets' => ['required', 'array', 'min:1'],
            'assets.*' => ['integer', 'distinct'],
            'purpose' => ['required', 'string', 'max:255'],
            'requested_start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'requested_end_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:requested_start_date'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $organizationId = $this->user()->organization_id;
                $start = Carbon::parse($this->input('requested_start_date'))->startOfDay();
                $end = Carbon::parse($this->input('requested_end_date'))->startOfDay();

                $assets = Asset::query()
                    ->where('organization_id', $organizationId)
                    ->whereIn('id', $this->input('assets'))
                    ->get();

                if ($assets->count() !== count($this->input('assets'))) {
                    $validator->errors()->add('assets', __('messages.booking.assets_not_found'));

                    return;
                }

                $maxLoanDays = app(SettingService::class)->maxLoanDays();

                if ((int) $start->diffInDays($end) > $maxLoanDays) {
                    $validator->errors()->add(
                        'requested_end_date',
                        __('messages.booking.duration_exceeded', ['days' => $maxLoanDays]),
                    );
                }

                $conflict = app(BookingService::class)->conflictMessageFor($assets, $start, $end);

                if ($conflict !== null) {
                    $validator->errors()->add('assets', $conflict);
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'assets.required' => __('messages.booking.assets_required'),
            'assets.min' => __('messages.booking.assets_required'),
            'purpose.required' => __('messages.booking.purpose_required'),
        ];
    }
}
