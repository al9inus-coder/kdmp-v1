<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        // Aplikasi ini memakai Tailwind, bukan Bootstrap. Panggilan
        // useBootstrapFive() sebelumnya membuat pagination dirender dengan
        // kelas .pagination/.page-link yang tidak punya CSS sama sekali,
        // sehingga tampil sebagai deretan tautan telanjang di keenam halaman
        // yang berhalaman. View Tailwind milik aplikasi ada di
        // resources/views/vendor/pagination/tailwind.blade.php.
        Paginator::useTailwind();

        // TLS berhenti di proxy depan (Cloudflare), nginx menerima HTTP polos —
        // paksa semua URL yang di-generate memakai https agar tidak kena blokir
        // mixed content di browser (mis. fetch() Isi Otomatis dari Katalog).
        if (str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Implicitly grant "Admin" role all permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Admin') ? true : null;
        });
    }
}
