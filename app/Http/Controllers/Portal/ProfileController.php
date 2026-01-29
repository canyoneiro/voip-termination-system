<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth('customer')->user();

        return view('portal.profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth('customer')->user();

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
        ]);

        $user->update($data);

        return redirect()->route('portal.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function showChangePassword()
    {
        return view('portal.profile.change-password');
    }

    public function changePassword(Request $request)
    {
        $user = auth('customer')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('portal.profile.show')
            ->with('success', 'Password changed successfully.');
    }
}
