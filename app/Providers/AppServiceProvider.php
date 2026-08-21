<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Cliente;
use App\Models\CondIva;
use App\Models\DocRequerida;
use App\Models\DocumentoCliente;
use App\Models\EstadoAnses;
use App\Models\EstadoExpediente;
use App\Models\EstadoRequerimiento;
use App\Models\EstadoTramite;
use App\Models\Juzgado;
use App\Models\Pais;
use App\Models\ReparticionOrigen;
use App\Models\TimeGestion;
use App\Models\TipoAporte;
use App\Models\TipoDocumento;
use App\Models\TipoSociedad;
use App\Models\TipoTramite;
use App\Observers\AreaObserver;
use App\Observers\ClienteObserver;
use App\Observers\CondIvaObserver;
use App\Observers\DocRequeridaObserver;
use App\Observers\DocumentoClienteObserver;
use App\Observers\EstadoAnsesObserver;
use App\Observers\EstadoExpedienteObserver;
use App\Observers\EstadoRequerimientoObserver;
use App\Observers\EstadoTramiteObserver;
use App\Observers\JuzgadoObserver;
use App\Observers\PaisObserver;
use App\Observers\ReparticionOrigenObserver;
use App\Observers\TimeGestionObserver;
use App\Observers\TipoAporteObserver;
use App\Observers\TipoDocumentoObserver;
use App\Observers\TipoSociedadObserver;
use App\Observers\TipoTramiteObserver;
use App\Observers\UserObserver;
use App\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        TipoDocumento::observe(TipoDocumentoObserver::class);
        Pais::observe(PaisObserver::class);
        Area::observe(AreaObserver::class);
        TipoAporte::observe(TipoAporteObserver::class);
        TipoTramite::observe(TipoTramiteObserver::class);
        EstadoRequerimiento::observe(EstadoRequerimientoObserver::class);
        EstadoTramite::observe(EstadoTramiteObserver::class);
        TipoSociedad::observe(TipoSociedadObserver::class);
        CondIva::observe(CondIvaObserver::class);
        ReparticionOrigen::observe(ReparticionOrigenObserver::class);
        Juzgado::observe(JuzgadoObserver::class);
        EstadoExpediente::observe(EstadoExpedienteObserver::class);
        DocRequerida::observe(DocRequeridaObserver::class);
        DocumentoCliente::observe(DocumentoClienteObserver::class);
        Cliente::observe(ClienteObserver::class);
        EstadoAnses::observe(EstadoAnsesObserver::class);
        TimeGestion::observe(TimeGestionObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
