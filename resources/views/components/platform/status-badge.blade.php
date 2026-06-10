@props(['value', 'size' => 'default'])

@php
    $theme = match ($value) {
        'planificada', 'active' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
        'en_ejecucion' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        'paralizada', 'inactive' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
        'finalizada' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'registrado' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
        'derivado', 'en_revision' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        'en_proceso' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-300',
        'recibido' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300',
        'observado' => 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300',
        'atendido' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'archivado' => 'bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300',
        'aprobado' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'rechazado' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
        'conforme' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'vencido' => 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-950/60 dark:text-fuchsia-300',
        'borrador' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        'solicitada', 'en_cotizacion' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
        'cotizada', 'en_evaluacion' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        'adjudicada', 'orden_generada' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'generada' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
        'en_aprobacion', 'en_revision' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        'anulada' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
        'convertida_a_contrato', 'aprobada', 'firmado', 'en_ejecucion' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'finalizado' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        'pendiente' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
        'parcial' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        'pagado', 'pagada' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'pendiente_documentos' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
        'lista_para_pago' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'pago_parcial' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        'observada' => 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300',
        'cancelada' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
        'reprogramado' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300',
        'baja' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        'media' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
        'alta' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        'critica' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
        'cerrada' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    };

    $label = str($value)->replace('_', ' ')->headline();

    $sizeClass = match ($size) {
        'xs' => 'px-1.5 py-0.5 text-[10px] leading-tight',
        default => 'px-2.5 py-1 text-xs',
    };
@endphp

<span class="inline-flex rounded-full font-medium {{ $sizeClass }} {{ $theme }}">
    {{ $label }}
</span>
