<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>To Do diario, semanal y mensual</title>
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
        <main class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
            
            
            <section class="grid gap-6 lg:grid-cols-[1fr_24rem]">
                <div class="rounded-3xl border border-white/10 bg-white/10 p-8 shadow-2xl shadow-cyan-950/30 backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-300">Organizador personal</p>
                    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">Mis actividades</h1>
                            <p class="mt-3 max-w-2xl text-base text-slate-300">Carga tareas diarias, semanales o mensuales y márcalas como realizadas cuando las termines.</p>
                        </div>
                    </div>

                    <section class="mt-10 grid gap-5 xl:grid-cols-3">
                        @foreach ($frequencies as $frequency => $label)
                            <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5 shadow-xl shadow-slate-950/20">
                                <div class="flex items-center justify-between gap-4">
                                    <h2 class="text-2xl font-bold text-white">{{ $label }}</h2>
                                    <span class="rounded-full bg-white/10 px-3 py-1 text-sm text-slate-300">{{ $tasksByFrequency->get($frequency, collect())->count() }}</span>
                                </div>
                                <p class="mt-2 text-xs font-medium uppercase tracking-wide text-cyan-200">Ordenadas de mas viejas a mas nuevas</p>
                                <div class="mt-5 flex flex-col gap-4">
                                    @forelse ($tasksByFrequency->get($frequency, collect()) as $task)
                                         @php($isOverdue = $task->isOverdue())
                                        <article class="rounded-2xl border p-4 {{ $isOverdue ? 'border-rose-400/40 bg-rose-500/10 shadow-lg shadow-rose-950/20' : 'border-white/10 bg-slate-950/70' }} {{ $task->isCompleted() ? 'opacity-60' : '' }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <h3 class="font-semibold {{ $isOverdue ? 'text-rose-100' : 'text-white' }} {{ $task->isCompleted() ? 'line-through' : '' }}">{{ $task->title }}</h3>
                                                        @if ($isOverdue)
                                                            <span class="rounded-full border border-rose-300/30 bg-rose-400/20 px-2 py-0.5 text-xs font-bold uppercase tracking-wide text-rose-100">Pendiente</span>
                                                        @endif
                                                    </div>
                                                    @if ($task->description)
                                                        <p class="mt-3 text-sm text-slate-300">{{ $task->description }}</p>
                                                    @endif    
                                                    @if ($task->due_date)
                                                        <p class="mt-3 text-xs font-medium uppercase tracking-wide {{ $isOverdue ? 'text-rose-200' : 'text-cyan-200' }}">Fecha: {{ $task->due_date->format('d/m/Y') }}</p>
                                                    @endif
                                                    @if ($task->realization_time)
                                                        <p class="mt-1 text-xs font-medium uppercase tracking-wide {{ $isOverdue ? 'text-rose-200' : 'text-cyan-200' }}">Hora: {{ \Illuminate\Support\Str::of($task->realization_time)->substr(0, 5) }}</p>
                                                    @endif
                                                </div>

                                                @if ($task->isCompleted())
                                                    <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="rounded-full border border-white/10 px-3 py-1 text-sm font-semibold text-amber-200 hover:bg-amber-300/10">
                                                            Reabrir
                                                        </button>
                                                    </form>
                                                @else
                                                    <button
                                                        type="button"
                                                        class="rounded-full border border-white/10 px-3 py-1 text-sm font-semibold text-emerald-200 hover:bg-emerald-300/10"
                                                        data-completion-choice-open="completion-choice-{{ $task->id }}"
                                                    >Hecha                                                
                                                    </button>
                                                @endif
                                            </div>

                                            @unless ($task->isCompleted())
                                                <div
                                                    id="completion-choice-{{ $task->id }}"
                                                    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 px-4 backdrop-blur-sm"
                                                    data-completion-choice
                                                >
                                                    <div class="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl shadow-slate-950/40">
                                                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-300">Actividad hecha</p>
                                                        <h4 class="mt-3 text-2xl font-bold text-white">¿Qué querés hacer con “{{ $task->title }}”?</h4>
                                                        <p class="mt-2 text-sm text-slate-300">Podés borrarla definitivamente o guardarla como completada para que luego aparezca la opción Reabrir.</p>
                                                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                                                            <button type="button" class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-white/10" data-completion-choice-close>Cancelar</button>
                                                            <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="text-sm font-semibold text-red-200 transition hover:text-red-400">Eliminar actividad</button>
                                                            </form>
                                                            <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button class="w-full rounded-2xl bg-emerald-300 px-4 py-2 text-sm font-bold text-slate-950 transition hover:bg-emerald-200 sm:w-auto">Guardar hecha</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endunless

                                            <details class="mt-4">
                                                <summary class="cursor-pointer text-sm font-medium text-slate-300">Editar</summary>
                                                <form method="POST" action="{{ route('tasks.update', $task) }}" class="mt-3 flex flex-col gap-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <input name="title" value="{{ old('title', $task->title) }}" required class="rounded-xl bg-white px-3 py-2 text-sm text-slate-950">
                                                    <textarea name="description" rows="2" class="rounded-xl bg-white px-3 py-2 text-sm text-slate-950">{{ old('description', $task->description) }}</textarea>
                                                    <select name="frequency" class="rounded-xl bg-white px-3 py-2 text-sm text-slate-950">
                                                        @foreach ($frequencies as $value => $optionLabel)
                                                            <option value="{{ $value }}" @selected(old('frequency', $task->frequency) === $value)>{{ $optionLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" class="rounded-xl bg-white px-3 py-2 text-sm text-slate-950">
                                                    <input type="time" name="realization_time" value="{{ old('realization_time', $task->realization_time ? \Illuminate\Support\Str::of($task->realization_time)->substr(0, 5) : null) }}" class="rounded-xl bg-white px-3 py-2 text-sm text-slate-950">
                                                    <button class="rounded-xl bg-cyan-100 px-3 py-2 text-sm font-bold text-slate-950 transition hover:bg-cyan-200">Guardar cambios</button>
                                                </form>
                                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="mt-3">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-sm font-semibold text-red-500 transition hover:text-red-400">Eliminar actividad</button>
                                                </form>
                                            </details>
                                        </article>
                                    @empty
                                        <p class="rounded-2xl border border-dashed border-white/10 p-4 text-sm text-slate-400">Todavía no hay actividades {{ mb_strtolower($label) }}.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </section>

                     @php
                        $calendarMarkerStyles = [
                            'daily' => ['label' => 'Diarias', 'dot' => 'bg-cyan-300', 'text' => 'text-cyan-100'],
                            'weekly' => ['label' => 'Semanales', 'dot' => 'bg-amber-300', 'text' => 'text-amber-100'],
                            'monthly' => ['label' => 'Mensuales', 'dot' => 'bg-fuchsia-300', 'text' => 'text-fuchsia-100'],
                        ];
                    @endphp

                    <section class="mt-8 rounded-3xl border border-white/10 bg-slate-950/50 p-5 shadow-xl shadow-slate-950/20">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-300">Calendario de actividades</p>
                                <h2 class="mt-2 text-2xl font-bold capitalize text-white">{{ $calendar['monthLabel'] }}</h2>
                                <p class="mt-2 text-sm text-slate-300">Las actividades diarias, semanales y mensuales se marcan con colores distintos según su repetición.</p>
                            </div>
                            <div class="flex flex-wrap gap-3 text-xs font-semibold uppercase tracking-wide">
                                @foreach ($calendarMarkerStyles as $style)
                                    <span class="inline-flex items-center gap-2 text-slate-300">
                                        <span class="size-2.5 rounded-full {{ $style['dot'] }}"></span>
                                        {{ $style['label'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-7 gap-2 text-center text-xs font-bold uppercase tracking-wide text-slate-400">
                            @foreach ($calendar['weekdays'] as $weekday)
                                <div>{{ $weekday }}</div>
                            @endforeach
                        </div>

                        <div class="mt-2 grid gap-2">
                            @foreach ($calendar['weeks'] as $week)
                                <div class="grid grid-cols-7 gap-2">
                                    @foreach ($week as $day)
                                        <div
                                            class="min-h-28 rounded-2xl border p-2 text-left {{ $day['isCurrentMonth'] ? 'border-white/10 bg-slate-900/80' : 'border-white/5 bg-slate-950/30 text-slate-600' }} {{ $day['isToday'] ? 'ring-2 ring-cyan-300/70' : '' }}"
                                            data-calendar-date="{{ $day['date']->toDateString() }}"
                                        >
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-sm font-bold {{ $day['isCurrentMonth'] ? 'text-white' : 'text-slate-600' }}">{{ $day['date']->format('j') }}</span>
                                                @if ($day['isToday'])
                                                    <span class="rounded-full bg-cyan-300 px-2 py-0.5 text-[0.65rem] font-bold uppercase text-slate-950">Hoy</span>
                                                @endif
                                            </div>

                                            @if ($day['markers'] !== [])
                                                <div class="mt-3 flex flex-col gap-1.5">
                                                    @foreach ($day['markers'] as $frequency => $titles)
                                                        @php($style = $calendarMarkerStyles[$frequency])
                                                        <div class="rounded-xl bg-white/5 px-2 py-1" data-calendar-marker="{{ $frequency }}">
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="size-2 rounded-full {{ $style['dot'] }}"></span>
                                                                <span class="text-[0.65rem] font-bold uppercase tracking-wide {{ $style['text'] }}">{{ $style['label'] }}</span>
                                                            </div>
                                                            <p class="mt-1 truncate text-xs text-slate-300" title="{{ implode(', ', $titles) }}">{{ count($titles) }} actividad{{ count($titles) === 1 ? '' : 'es' }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <form method="POST" action="{{ route('tasks.store') }}" class="rounded-3xl border border-cyan-400/20 bg-cyan-400/10 p-6 shadow-xl shadow-cyan-950/20 lg:sticky lg:top-8 lg:self-start">
                    @csrf
                    <h2 class="text-xl font-semibold text-white">Nueva actividad</h2>
                    <div class="mt-5 flex flex-col gap-4">
                        <label class="flex flex-col gap-2 text-sm font-medium text-slate-200">
                            Título
                            <input name="title" value="{{ old('title') }}" required class="rounded-2xl border border-white/10 bg-white px-4 py-3 text-slate-950 outline-none ring-cyan-300 transition focus:ring-4" placeholder="Ej. Planificar el dia, la semana o el mes">
                            @error('title')<span class="text-sm text-rose-300">{{ $message }}</span>@enderror
                        </label>
                        <label class="flex flex-col gap-2 text-sm font-medium text-slate-200">
                            Frecuencia
                            <select name="frequency" required class="rounded-2xl border border-white/10 bg-white px-4 py-3 text-slate-950 outline-none ring-cyan-300 transition focus:ring-4">
                                @foreach ($frequencies as $value => $label)
                                    <option value="{{ $value }}" @selected(old('frequency') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('frequency')<span class="text-sm text-rose-300">{{ $message }}</span>@enderror
                        </label>
                        <label class="flex flex-col gap-2 text-sm font-medium text-slate-200">
                            Fecha objetivo
                            <input type="date" name="due_date" value="{{ old('due_date') }}" class="rounded-2xl border border-white/10 bg-white px-4 py-3 text-slate-950 outline-none ring-cyan-300 transition focus:ring-4">
                            @error('due_date')<span class="text-sm text-rose-300">{{ $message }}</span>@enderror
                        </label>
                        <label class="flex flex-col gap-2 text-sm font-medium text-slate-200">
                            Hora de realización
                            <input type="time" name="realization_time" value="{{ old('realization_time') }}" class="rounded-2xl border border-white/10 bg-white px-4 py-3 text-slate-950 outline-none ring-cyan-300 transition focus:ring-4">
                            @error('realization_time')<span class="text-sm text-rose-300">{{ $message }}</span>@enderror
                        </label>
                        <label class="flex flex-col gap-2 text-sm font-medium text-slate-200">
                            Detalle
                            <textarea name="description" rows="3" class="rounded-2xl border border-white/10 bg-white px-4 py-3 text-slate-950 outline-none ring-cyan-300 transition focus:ring-4" placeholder="Notas opcionales">{{ old('description') }}</textarea>
                            @error('description')<span class="text-sm text-rose-300">{{ $message }}</span>@enderror
                        </label>
                        <button class="rounded-2xl bg-cyan-300 px-5 py-3 font-bold text-slate-950 transition hover:bg-cyan-200">Agregar actividad</button>
                    </div>
                </form>
            </section>   

            @if (session('status'))
                <div data-auto-dismiss="5000" class="rounded-2xl border border-emerald-300/30 bg-emerald-400/10 px-5 py-4 text-emerald-100 transition duration-300 ease-in-out">{{ session('status') }}</div>
            @endif   
        </main>
    </body>
</html>