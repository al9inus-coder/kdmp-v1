<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->q;
        $status = $request->status;

        $accounts = Account::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', $status);
            })
            ->orderBy('kode')
            ->paginate(10)
            ->withQueryString();

        return view('accounts.index', compact('accounts', 'search', 'status'));
    }

    public function create(): View
    {
        $account = new Account([
            'is_active' => true,
        ]);

        return view('accounts.create', compact('account'));
    }

    public function store(AccountRequest $request): RedirectResponse
    {
        Account::create($request->validated());

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Rekening Belanja berhasil ditambahkan.');
    }

    public function edit(Account $account): View
    {
        return view('accounts.edit', compact('account'));
    }

    public function update(AccountRequest $request, Account $account): RedirectResponse
    {
        $account->update($request->validated());

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Rekening Belanja berhasil diperbarui.');
    }
}
