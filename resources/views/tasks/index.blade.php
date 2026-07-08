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
                                <p class="mt-2 text-xs font-medium uppercase tracking-wide text-cyan-200">Más próximas primero</p>
                                <div class="mt-5 flex flex-col gap-4">
                                    @forelse ($tasksByFrequency->get($frequency, collect()) as $task)
                                        <article class="rounded-2xl border border-white/10 bg-slate-950/70 p-4 {{ $task->isCompleted() ? 'opacity-60' : '' }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <h3 class="font-semibold text-white {{ $task->isCompleted() ? 'line-through' : '' }}">{{ $task->title }}</h3>
                                                    @if ($task->description)
                                                        <p class="mt-2 text-sm text-slate-300">{{ $task->description }}</p>
                                                    @endif
                                                    @if ($task->due_date)
                                                        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-cyan-200">Fecha: {{ $task->due_date->format('d/m/Y') }}</p>
                                                    @endif
                                                    @if ($task->realization_time)
                                                        <p class="mt-1 text-xs font-medium uppercase tracking-wide text-cyan-200">Hora: {{ \Illuminate\Support\Str::of($task->realization_time)->substr(0, 5) }}</p>
                                                    @endif
                                                </div>
                                                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="rounded-full border border-white/10 px-3 py-1 text-sm font-semibold {{ $task->isCompleted() ? 'text-amber-200 hover:bg-amber-300/10' : 'text-emerald-200 hover:bg-emerald-300/10' }}">
                                                        {{ $task->isCompleted() ? 'Reabrir' : 'Hecha' }}
                                                    </button>
                                                </form>
                                            </div>
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
                                                    <button class="rounded-xl bg-white px-3 py-2 text-sm font-bold text-slate-950">Guardar cambios</button>
                                                </form>
                                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="mt-3">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-sm font-semibold text-rose-300 hover:text-rose-200">Eliminar actividad</button>
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
                </div>

                <form method="POST" action="{{ route('tasks.store') }}" class="rounded-3xl border border-cyan-400/20 bg-cyan-400/10 p-6 shadow-xl shadow-cyan-950/20 lg:sticky lg:top-8 lg:self-start">
                    @csrf
                    <h2 class="text-xl font-semibold text-white">Nueva actividad</h2>
                    <div class="mt-5 flex flex-col gap-4">
                        <label class="flex flex-col gap-2 text-sm font-medium text-slate-200">
                            Título
                            <input name="title" value="{{ old('title') }}" required class="rounded-2xl border border-white/10 bg-white px-4 py-3 text-slate-950 outline-none ring-cyan-300 transition focus:ring-4" placeholder="Ej. Planificar la semana">
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