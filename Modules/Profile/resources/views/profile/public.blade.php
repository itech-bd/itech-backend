@extends('layouts.public-profile')

@section('title', $user->name.' - '.config('app.name', 'Laravel'))

@section('content')
    @php
        $profile = $user->profile;
        $address = $user->address;

        $genderLabel = match ($profile?->gender) {
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            default => null,
        };

        $proficiencyLabel = [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'expert' => 'Expert',
        ];

        $proficiencyWidth = [
            'beginner' => 35,
            'intermediate' => 70,
            'expert' => 100,
        ];

        $locationLine = $address
            ? trim(implode(', ', array_filter([$address->city, $address->country])))
            : null;
    @endphp

    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="lg:col-span-12">
                <div class="relative overflow-hidden rounded-3xl bg-white/5 ring-1 ring-white/10 shadow-2xl shadow-indigo-500/10">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 via-fuchsia-500/10 to-cyan-500/20"></div>
                    <div class="relative px-6 py-8 sm:px-10">
                        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-5">
                                <div class="h-24 w-24 shrink-0 rounded-3xl bg-white/10 overflow-hidden ring-1 ring-white/10">
                                    @if ($user->profile_image)
                                        <img
                                            src="{{ asset('storage/'.$user->profile_image) }}"
                                            alt="{{ $user->name }}"
                                            class="h-full w-full object-cover"
                                        />
                                    @else
                                        <div class="h-full w-full grid place-items-center text-white/60 text-xl font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <h1 class="text-3xl sm:text-4xl font-semibold text-white truncate">{{ $user->name }}</h1>
                                    <p class="mt-1 text-sm text-white/70">{{ __('Online CV / Portfolio') }}</p>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @if ($genderLabel)
                                            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm text-white/80 ring-1 ring-white/10">
                                                {{ $genderLabel }}
                                            </span>
                                        @endif
                                        @if ($profile?->date_of_birth)
                                            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm text-white/80 ring-1 ring-white/10">
                                                {{ $profile->date_of_birth->format('d M Y') }}
                                            </span>
                                        @endif
                                        @if ($locationLine)
                                            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm text-white/80 ring-1 ring-white/10">
                                                {{ $locationLine }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm text-white/80 ring-1 ring-white/10">
                                            {{ __('Member since') }} {{ $user->created_at?->format('M Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:items-end">
                                <div class="flex flex-wrap gap-2">
                                    <a
                                        href="mailto:{{ $user->email }}"
                                        class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100"
                                    >
                                        {{ __('Email') }}
                                    </a>

                                    @if ($profile?->mobile_number)
                                        <a
                                            href="tel:{{ $profile->mobile_number }}"
                                            class="inline-flex items-center justify-center rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/15 ring-1 ring-white/10"
                                        >
                                            {{ __('Call') }}
                                        </a>
                                    @endif

                                    @if ($profile?->public_url)
                                        <a
                                            href="/p/{{ $profile->public_url }}"
                                            class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-indigo-500 to-fuchsia-500 px-4 py-2 text-sm font-semibold text-white hover:from-indigo-600 hover:to-fuchsia-600"
                                            target="_blank"
                                            rel="noreferrer"
                                        >
                                            {{ __('Share link') }}
                                        </a>
                                    @endif
                                </div>

                                @if ($profile?->bio)
                                    <p class="max-w-xl text-sm text-white/80 leading-relaxed">
                                        {{ $profile->bio }}
                                    </p>
                                @else
                                    <p class="text-sm text-white/60">{{ __('No bio provided yet.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="lg:col-span-4 space-y-6">
                <div class="rounded-3xl bg-white/5 ring-1 ring-white/10">
                    <div class="px-6 py-6">
                        <h2 class="text-sm font-semibold tracking-wide text-white/80">{{ __('Contact') }}</h2>
                        <dl class="mt-4 space-y-4 text-sm">
                            <div>
                                <dt class="text-white/50">{{ __('Email') }}</dt>
                                <dd class="mt-1 text-white break-all">{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-white/50">{{ __('Mobile') }}</dt>
                                <dd class="mt-1 text-white break-all">{{ $profile?->mobile_number ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="rounded-3xl bg-white/5 ring-1 ring-white/10">
                    <div class="px-6 py-6">
                        <h2 class="text-sm font-semibold tracking-wide text-white/80">{{ __('Family') }}</h2>
                        <dl class="mt-4 space-y-4 text-sm">
                            <div>
                                <dt class="text-white/50">{{ __('Father') }}</dt>
                                <dd class="mt-1 text-white break-all">{{ $profile?->father_name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-white/50">{{ __('Mother') }}</dt>
                                <dd class="mt-1 text-white break-all">{{ $profile?->mother_name ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="rounded-3xl bg-white/5 ring-1 ring-white/10">
                    <div class="px-6 py-6">
                        <h2 class="text-sm font-semibold tracking-wide text-white/80">{{ __('Address') }}</h2>
                        @if ($address)
                            <div class="mt-4 space-y-2 text-sm text-white/85">
                                <div class="font-semibold text-white">{{ $address->city }}, {{ $address->country }}</div>
                                <div class="grid gap-1 text-white/75">
                                    @if ($address->house_number)
                                        <div>{{ __('House') }}: {{ $address->house_number }}</div>
                                    @endif
                                    @if ($address->street)
                                        <div>{{ __('Street') }}: {{ $address->street }}</div>
                                    @endif
                                    @if ($address->post_office)
                                        <div>{{ __('Post Office') }}: {{ $address->post_office }}</div>
                                    @endif
                                    @if ($address->zip_code)
                                        <div>{{ __('ZIP') }}: {{ $address->zip_code }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <p class="mt-4 text-sm text-white/60">{{ __('No address provided yet.') }}</p>
                        @endif
                    </div>
                </div>
            </aside>

            <section class="lg:col-span-8 space-y-6">
                <div class="rounded-3xl bg-white/5 ring-1 ring-white/10">
                    <div class="px-6 py-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold tracking-wide text-white/80">{{ __('Skills') }}</h2>
                            <div class="text-xs text-white/50">{{ __('Level') }}</div>
                        </div>

                        @if ($user->skills && $user->skills->count())
                            <div class="mt-5 space-y-4">
                                @foreach ($user->skills as $skill)
                                    @php
                                        $level = $skill->pivot?->proficiency_level;
                                        $pct = $level ? ($proficiencyWidth[$level] ?? 0) : 0;
                                    @endphp
                                    <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="font-semibold text-white">{{ $skill->name }}</div>
                                            <div class="text-xs font-medium text-white/70">
                                                {{ $level ? ($proficiencyLabel[$level] ?? $level) : '—' }}
                                            </div>
                                        </div>
                                        <div class="mt-3 h-2 rounded-full bg-white/10 overflow-hidden">
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-cyan-400 via-indigo-400 to-fuchsia-400"
                                                style="width: {{ $pct }}%"
                                            ></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-4 text-sm text-white/60">{{ __('No skills added yet.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl bg-white/5 ring-1 ring-white/10">
                    <div class="px-6 py-6">
                        <h2 class="text-sm font-semibold tracking-wide text-white/80">{{ __('Education') }}</h2>

                        @if ($user->educations && $user->educations->count())
                            <div class="mt-5">
                                <ol class="relative border-s border-white/10">
                                    @foreach ($user->educations as $edu)
                                        <li class="ms-6 pb-6">
                                            <span class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full bg-gradient-to-r from-indigo-400 to-fuchsia-400"></span>
                                            <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
                                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                                    <div>
                                                        <div class="text-white font-semibold">{{ $edu->degree_name }}</div>
                                                        <div class="text-sm text-white/80">{{ $edu->institute_name }}</div>
                                                        @if ($edu->board_or_university)
                                                            <div class="text-sm text-white/60">{{ __('Board/University') }}: {{ $edu->board_or_university }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-white/60 sm:text-right">
                                                        @if ($edu->start_year || $edu->end_year)
                                                            <div>
                                                                @if ($edu->start_year)
                                                                    {{ $edu->start_year }}
                                                                @endif
                                                                @if ($edu->start_year && $edu->end_year)
                                                                    -
                                                                @endif
                                                                @if ($edu->end_year)
                                                                    {{ $edu->end_year }}
                                                                @endif
                                                            </div>
                                                        @endif
                                                        @if ($edu->result_or_grade)
                                                            <div>{{ __('Result') }}: {{ $edu->result_or_grade }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        @else
                            <p class="mt-4 text-sm text-white/60">{{ __('No education information provided yet.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl bg-white/5 ring-1 ring-white/10">
                    <div class="px-6 py-6">
                        <h2 class="text-sm font-semibold tracking-wide text-white/80">{{ __('Experience') }}</h2>

                        @if ($user->experiences && $user->experiences->count())
                            <div class="mt-5">
                                <ol class="relative border-s border-white/10">
                                    @foreach ($user->experiences as $exp)
                                        <li class="ms-6 pb-6">
                                            <span class="absolute -start-1.5 mt-1.5 h-3 w-3 rounded-full bg-gradient-to-r from-cyan-400 to-indigo-400"></span>
                                            <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
                                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                                    <div>
                                                        <div class="text-white font-semibold">{{ $exp->job_title }}</div>
                                                        <div class="text-sm text-white/80">{{ $exp->company_name }}</div>
                                                        @if ($exp->description)
                                                            <p class="mt-2 text-sm text-white/65 whitespace-pre-line">{{ $exp->description }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-white/60 sm:text-right">
                                                        <div>
                                                            {{ $exp->start_date?->format('M Y') }}
                                                            @if ($exp->end_date)
                                                                - {{ $exp->end_date->format('M Y') }}
                                                            @else
                                                                - {{ __('Present') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        @else
                            <p class="mt-4 text-sm text-white/60">{{ __('No experience information provided yet.') }}</p>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
