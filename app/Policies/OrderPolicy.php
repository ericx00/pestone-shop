<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderPolicy
{
    public function __construct(protected Request $request) {}

    /**
     * Anyone who: owns the order, holds its guest tracking token (query
     * string ?ot=, e.g. from the confirmation email or a payment redirect),
     * or placed it earlier in this same browser session.
     */
    public function view(?User $user, Order $order): bool
    {
        if ($user && $order->user_id === $user->id) {
            return true;
        }

        $token = (string) $this->request->query('ot', '');
        if ($token !== '' && $order->guest_token && hash_equals($order->guest_token, $token)) {
            return true;
        }

        return (bool) $this->request->session()->get('order_access.'.$order->id, false);
    }

    /** Same rule for taking a payment action against the order. */
    public function pay(?User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }
}
