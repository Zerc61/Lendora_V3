<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $organizations = \App\Models\Organization::orderBy('name')->get();

        return view('auth.register', compact('organizations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'study_program' => ['required', 'string', 'max:100'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'whatsapp_opt_in' => ['sometimes', 'boolean'],
            'terms' => ['required', 'accepted'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'nim' => $request->nim,
            'email' => $request->email,
            'phone' => $request->phone,
            'study_program' => $request->study_program,
            'organization_id' => $request->organization_id,
            'whatsapp_opt_in' => $request->boolean('whatsapp_opt_in'),
            'password' => Hash::make($request->password),
            'role_id' => Role::where('slug', 'borrower')->firstOrFail()->id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}