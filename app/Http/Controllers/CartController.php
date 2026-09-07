<?php

namespace App\Http\Controllers;

use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cart) {}

    public function index()
    {
        return view('cart.index', [
            'summary' => $this->cart->summary(),
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $this->cart->add($data['product_id'], $data['qty'] ?? 1);

        return back()->with('status', 'Added to cart.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $this->cart->updateQty($data['product_id'], $data['qty']);

        return back()->with('status', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $this->cart->remove((int) $request->input('product_id'));

        return back()->with('status', 'Item removed.');
    }

    public function clear()
    {
        $this->cart->clear();

        return back()->with('status', 'Cart cleared.');
    }
}
