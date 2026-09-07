<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuoteController extends Controller
{
    public function create(Request $request)
    {
        $product = $request->filled('product')
            ? Product::where('slug', $request->input('product'))->first()
            : null;

        return view('quote.create', compact('product'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:160'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:3000'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $items = null;
        if (! empty($data['product_id'])) {
            $p = Product::find($data['product_id']);
            $items = [[
                'product_id' => $p->id,
                'name' => $p->name,
                'qty' => $data['qty'] ?? 1,
            ]];
        }

        QuoteRequest::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'company' => $data['company'] ?? null,
            'subject' => $data['subject'] ?? 'Quote request',
            'message' => $data['message'],
            'items' => $items,
            'status' => 'new',
        ]);

        return back()->with('status', 'Thank you — our team will get back to you with a quote shortly.');
    }
}
