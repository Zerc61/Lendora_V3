<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function edit(Request $request): View
    {
        return view('admin.settings.edit', [
            'organization' => $request->user()->organization,
            'settings' => $this->settings->all($request->user()->organization_id),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        $organization->update([
            'name' => $request->input('organization_name'),
            'address' => $request->input('organization_address'),
            'phone' => $request->input('organization_phone'),
            'email' => $request->input('organization_email'),
        ]);

        $organizationId = $organization->id;

        $this->settings->set('max_loan_days', $request->integer('max_loan_days'), $organizationId);
        $this->settings->set('operator_approval_limit', $request->integer('operator_approval_limit'), $organizationId);
        $this->settings->set('return_reminder_enabled', $request->boolean('return_reminder_enabled') ? '1' : '0', $organizationId);
        $this->settings->set('borrower_announcement', (string) $request->input('borrower_announcement', ''), $organizationId);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', __('messages.setting.updated'));
    }
}
