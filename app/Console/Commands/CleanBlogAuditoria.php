<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BlogAuditoria;
use Carbon\Carbon;

class CleanBlogAuditoria extends Command
{
    protected $signature = 'blog_auditoria:clean';
    protected $description = 'Eliminar registros de blog_auditoria con mas de 1 semana';

    public function handle()
    {
        $limite = Carbon::now()->subWeek(); // 1 semana
        // $limite = Carbon::now()->subMinutes(1); // 3 minutos

        $eliminados = BlogAuditoria::where('fecha_hora','<',$limite)->delete();

        $this->info("Registros eliminados: {$eliminados}");

        return 0;
    }
}