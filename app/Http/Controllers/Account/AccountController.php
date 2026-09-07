<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('account.index', [
            'user' => $user,
            'orders' => $user->orders()->withCount('items')->take(10)->get(),
        ]);
    }

    public function order(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        return view('account.order', ['order' => $order->load('items', 'payments')]);
    }

    public function business()
    {
        return view('account.business', ['user' => Auth::user()]);
    }

    public function applyBusiness(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:160'],
            'kra_pin' => ['required', 'string', 'max:20'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        $user->update([
            'type' => 'b2b',
            'company_name' => $data['company_name'],
            'kra_pin' => $data['kra_pin'],
            'phone' => $data['phone'],
            'b2b_status' => 'pending',
        ]);

        return back()->with('status', 'Business account application submitted. We\'ll review and email you within one business day.');
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'contact_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'line1' => ['required', 'string', 'max:160'],
            'town' => ['required', 'string', 'max:80'],
            'county' => ['nullable', 'string', 'max:80'],
        ]);

        Auth::user()->addresses()->create($data + ['is_default' => true]);

        return back()->with('status', 'Address saved.');
    }
}
