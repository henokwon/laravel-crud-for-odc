<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function change_password(Request $request, User $user)
    {
        abort_unless($request->user()->is($user) || $request->user()->can('manage-records'), 403);
        $request->validate([
          'current_password' => ['bail', 'required', 'string', function ($attribute, $value, $fail) use ($request) {
              if (!Hash::check($value, $request->user()->password)) {
                  $fail('Your current password is incorrect.');
              }
          }],
          'password' => ['required', 'string', 'min:8', 'confirmed'],
      ]);

        $user->update([
          'password' => Hash::make($request->password)
      ]);

        return back()->with('message', 'User password updated successfully.');
    }
}
