<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /* ==========================================================================
       User management (every query is automatically limited to the logged-in
       admin's own company by the tenant scope on the Admin model)
       ========================================================================== */

    // Fetch users (search + pagination)
    public function index(Request $request)
    {
        $query = Admin::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $perPage = min(max((int) $request->get('per_page', 10), 1), 100);
        $users   = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $users->items(),
            'meta'    => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    // Insert new user (company super admin only). tenant_id is set automatically from the current company.
    public function store(Request $request)
    {
        if ($denied = $this->denyUnlessSuperAdmin()) {
            return $denied;
        }

        // Seat limit of the plan this company bought (set from the software owner's Console).
        $tenant = Auth::guard('admin')->user()->tenant;
        if ($tenant && ! $tenant->hasFreeSeat()) {
            return response()->json([
                'success' => false,
                'message' => "Your plan allows {$tenant->seatLimit()} users and all seats are used. Upgrade your plan to add more users.",
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:admins,email',   // globally unique: Google login depends on it
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8|same:confirm_password',
            'role'     => 'required|in:admin,super_admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $admin = Admin::create([
            'name'     => $request->name,
            'email'    => strtolower(trim($request->email)),
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'status'   => 'active',
        ]);

        return response()->json(['success' => true, 'message' => 'User added successfully.', 'data' => $admin], 201);
    }

    // Single user (edit modal pre-fill)
    public function show($id)
    {
        $admin = Admin::find($id);
        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => $admin]);
    }

    // Update user (company super admin only)
    public function update(Request $request, $id)
    {
        if ($denied = $this->denyUnlessSuperAdmin()) {
            return $denied;
        }

        $admin = Admin::find($id);
        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:admins,email,' . $admin->id,
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|same:confirm_password',
            'role'     => 'required|in:admin,super_admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($this->isSelf($admin) && $request->role !== $admin->role) {
            return response()->json(['success' => false, 'message' => 'You cannot change your own role.'], 403);
        }

        $data = $request->only('name', 'phone', 'role');
        $data['email'] = strtolower(trim($request->email));

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return response()->json(['success' => true, 'message' => 'User updated successfully.', 'data' => $admin->fresh()]);
    }

    // Toggle block / unblock (company super admin only)
    public function toggleStatus($id)
    {
        if ($denied = $this->denyUnlessSuperAdmin()) {
            return $denied;
        }

        $admin = Admin::find($id);
        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($this->isSelf($admin)) {
            return response()->json(['success' => false, 'message' => 'You cannot block yourself.'], 403);
        }

        $admin->status = $admin->status === 'active' ? 'inactive' : 'active';
        $admin->save();

        return response()->json(['success' => true, 'status' => $admin->status]);
    }

    // Single delete (company super admin only)
    public function destroy($id)
    {
        if ($denied = $this->denyUnlessSuperAdmin()) {
            return $denied;
        }

        $admin = Admin::find($id);
        if (! $admin) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if ($this->isSelf($admin)) {
            return response()->json(['success' => false, 'message' => 'You cannot delete yourself.'], 403);
        }

        $admin->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }

    // Multiselect (bulk) delete (company super admin only)
    public function bulkDelete(Request $request)
    {
        if ($denied = $this->denyUnlessSuperAdmin()) {
            return $denied;
        }

        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array|min:1|max:200',
            'ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'No users selected.'], 422);
        }

        $deleted = Admin::whereIn('id', $request->ids)
            ->where('id', '!=', Auth::guard('admin')->id())   // never delete yourself
            ->delete();

        return response()->json(['success' => true, 'message' => $deleted . ' user(s) deleted.']);
    }

    /* ==========================================================================
       Password login / logout
       ========================================================================== */

    public function showLogin()
    {
        return view('pages.auth.login');
    }

    // Handle login (AJAX)
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // No tenant is known yet, so the Admin model does not filter here (email is globally unique).
        // LOWER() makes the lookup case-insensitive on every database, so "Name@Company.com" still matches.
        $email = strtolower(trim($request->email));
        $admin = Admin::whereRaw('LOWER(email) = ?', [$email])->first();

        // The reason is only written to the log. The user always sees the same generic message.
        $reason = null;
        if (! $admin) {
            $reason = 'unknown_email';
        } elseif (! $admin->hasPassword()) {
            $reason = 'no_password_set';        // Google-only account
        } elseif (! Hash::check($request->password, $admin->password)) {
            $reason = 'wrong_password';
        }

        if ($reason) {
            Log::info('Password login failed', ['reason' => $reason, 'admin_id' => $admin?->id]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        if (! $admin->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked. Please contact your company admin.',
            ], 403);
        }

        if (! $admin->tenant?->isUsable()) {
            return response()->json([
                'success' => false,
                'message' => 'Your company workspace is inactive. Please contact support.',
            ], 403);
        }

        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();   // prevents session fixation

        $admin->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return response()->json([
            'success'  => true,
            'message'  => 'Login successful.',
            'redirect' => route('auth.home.main'),
        ]);
    }

    // Logout (AJAX)
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success'  => true,
            'redirect' => route('login'),
        ]);
    }

    /* ==========================================================================
       Helpers
       ========================================================================== */

    private function denyUnlessSuperAdmin()
    {
        if (! Auth::guard('admin')->user()?->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only a company super admin can do this.'], 403);
        }

        return null;
    }

    private function isSelf(Admin $admin): bool
    {
        return Auth::guard('admin')->id() === $admin->id;
    }
}