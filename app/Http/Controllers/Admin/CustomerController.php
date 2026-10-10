<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $customers = Customer::query()
            ->with('visitor')
            ->when($search !== '', function ($query) use ($search) {
                $digits = preg_replace('/\D+/', '', $search) ?? '';
                $query->where(function ($query) use ($search, $digits) {
                    $query->where('name', 'like', '%'.$search.'%');
                    if ($digits !== '') {
                        $query->orWhere('mobile', 'like', '%'.$digits.'%');
                    }
                });
            })
            ->orderByDesc('last_login_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'search' => $search,
        ]);
    }
}
