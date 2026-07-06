<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Actividades</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-4xl flex-col gap-8 px-6 py-10">
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Lista diaria</p>
                    <h1 class="text-3xl font-bold tracking-tight">Actividades</h1>
                    <p class="text-slate-600">Agrega tus actividades y define cada cuánto se repiten.</p>
                </div>

                @if (session('status'))
                    <p class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('status') }}
                    </p>
                @endif

                <form method="POST" action="{{ route('tasks.store') }}" class="mt-6 grid gap-4">
                    @csrf

                    <div class="grid gap-2">
                        <label for="title" class="text-sm font-medium text-slate-700">Título</label>
                        <input id="title" name="title" value="{{ old('title') }}" class="rounded-lg border border-slate-300 px-3 py-2 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" required>
                        @error('title')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-2">
                        <label for="description" class="text-sm font-medium text-slate-700">Descripción</label>
                        <textarea id="description" name="description" rows="3" class="rounded-lg border border-slate-300 px-3 py-2 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-2">
                        <label for="frequency" class="text-sm font-medium text-slate-700">Frecuencia</label>
                        <select id="frequency" name="frequency" class="rounded-lg border border-slate-300 px-3 py-2 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200" required>
                            <option value="daily" @selected(old('frequency', 'daily') === 'daily')>Diaria</option>
                            <option value="weekly" @selected(old('frequency') === 'weekly')>Semanal</option>
                            <option value="monthly" @selected(old('frequency') === 'monthly')>Mensual</option>
                        </select>
                        @error('frequency')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex w-fit items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        Agregar actividad
                    </button>
                </form>
            </section>

            <section class="grid gap-4">
                @forelse ($tasks as $task)
                    <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="grid gap-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-xl font-semibold">{{ $task->title }}</h2>
                                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                                        {{ ['daily' => 'Diaria', 'weekly' => 'Semanal', 'monthly' => 'Mensual'][$task->frequency] ?? $task->frequency }}
                                    </span>
                                </div>

                                @if ($task->description)
                                    <p class="text-slate-600">{{ $task->description }}</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl bg-white p-6 text-center text-slate-600 shadow-sm ring-1 ring-slate-200">Todavía no hay actividades.</p>
                @endforelse
            </section>
        </main>
    </body>
</html>