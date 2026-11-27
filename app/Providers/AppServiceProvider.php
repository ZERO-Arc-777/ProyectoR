<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        Paginator::useBootstrapFive();

        View::composer(['layouts.app', 'admin.dashboard'], function ($view) {
            $periodoEscolarGlobal = null;

            if (Auth::check()) {
                try {
                    if (Schema::hasTable('configuraciones')) {
                        if (Schema::hasColumn('configuraciones', 'clave') && Schema::hasColumn('configuraciones', 'valor')) {
                            $periodoEscolarGlobal = DB::table('configuraciones')
                                ->where('clave', 'periodo_actual')
                                ->value('valor');
                        } elseif (Schema::hasColumn('configuraciones', 'llave') && Schema::hasColumn('configuraciones', 'valor')) {
                            $periodoEscolarGlobal = DB::table('configuraciones')
                                ->where('llave', 'periodo_actual')
                                ->value('valor');
                        } elseif (Schema::hasColumn('configuraciones', 'nombre') && Schema::hasColumn('configuraciones', 'valor')) {
                            $periodoEscolarGlobal = DB::table('configuraciones')
                                ->where('nombre', 'periodo_actual')
                                ->value('valor');
                        } elseif (Schema::hasColumn('configuraciones', 'periodo_actual')) {
                            $periodoEscolarGlobal = DB::table('configuraciones')
                                ->value('periodo_actual');
                        }
                    }
                } catch (Throwable $e) {
                    $periodoEscolarGlobal = null;
                }
            }

            $view->with('periodoEscolarGlobal', $periodoEscolarGlobal);
        });
    }
}
