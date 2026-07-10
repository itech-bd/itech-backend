@extends('layouts.site')

@section('title', __('frontend.checkout') . ' • ' . $course->title)

@section('content')
@php
    $onlinePrice = $course->online_discount_price ?: $course->online_old_price;
    $offlinePrice = $course->offline_discount_price ?: $course->offline_old_price;
@endphp

<main>
    <x-site.page-hero title="Confirm Enrollment" :subtitle="'Review course, class type, batch schedule, and submit your enrollment request.'" badge="Checkout" />

    <section class="py-12">
        <div class="brand-container grid gap-8 lg:grid-cols-[1fr_420px]">
            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
                <h1 class="text-2xl font-black text-slate-950">{{ $course->title }}</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('frontend.checkout_note') }}</p>

                <form method="POST" action="{{ route('checkout.store', $course) }}" class="mt-8 space-y-6" x-data="{ batchType: '{{ old('batch_type', 'online') }}' }">
                    @csrf

                    @if($hasOnlineOfflinePricing)
                        <div>
                            <label class="block text-sm font-black text-slate-900">{{ __('frontend.select_batch_type') }}</label>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <label class="cursor-pointer rounded-[1.25rem] border border-slate-200 p-4 transition has-[:checked]:border-[#292b86] has-[:checked]:bg-[#292b86]/5">
                                    <input type="radio" name="batch_type" value="online" x-model="batchType" class="text-[#292b86] focus:ring-[#292b86]">
                                    <span class="ml-2 font-black text-slate-950">{{ __('frontend.online') }}</span>
                                    <span class="mt-2 block text-sm text-slate-600">{{ __('frontend.online_class_desc') }}</span>
                                    <span class="mt-3 block text-lg font-black text-[#292b86]">{{ $onlinePrice ? '৳'.number_format((float) $onlinePrice, 0) : 'Contact' }}</span>
                                </label>
                                <label class="cursor-pointer rounded-[1.25rem] border border-slate-200 p-4 transition has-[:checked]:border-[#f15a24] has-[:checked]:bg-[#f15a24]/5">
                                    <input type="radio" name="batch_type" value="offline" x-model="batchType" class="text-[#f15a24] focus:ring-[#f15a24]">
                                    <span class="ml-2 font-black text-slate-950">{{ __('frontend.offline') }}</span>
                                    <span class="mt-2 block text-sm text-slate-600">{{ __('frontend.offline_class_desc') }}</span>
                                    <span class="mt-3 block text-lg font-black text-[#f15a24]">{{ $offlinePrice ? '৳'.number_format((float) $offlinePrice, 0) : 'Contact' }}</span>
                                </label>
                            </div>
                            @error('batch_type') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($course->relationLoaded('batches') && $course->batches->count())
                        <div>
                            <label for="batch_id" class="block text-sm font-black text-slate-900">Select batch</label>
                            <select id="batch_id" name="batch_id" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#292b86] focus:ring-[#292b86]">
                                <option value="">-- Select a batch --</option>
                                @foreach($course->batches as $batch)
                                    @php($isJoined = isset($joinedBatchIds) && in_array((int) $batch->id, (array) $joinedBatchIds, true))
                                    <option value="{{ $batch->id }}" @selected((string) old('batch_id') === (string) $batch->id) @disabled($isJoined)>
                                        {{ $batch->name }} — {{ optional($batch->start_date)->format('d M Y') }} — {{ $batch->class_time }}@if($isJoined) — Already joined @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('batch_id') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div class="rounded-[1.25rem] border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            {{ __('frontend.no_batch_available_body') }}
                        </div>
                    @endif

                    <button type="submit" class="w-full rounded-full bg-[#f15a24] px-6 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-[#f15a24]/20 transition hover:bg-[#ed1c24]">
                        {{ __('frontend.confirm_order') }}
                    </button>
                </form>
            </div>

            <aside class="space-y-6">
                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="text-sm font-bold uppercase tracking-[0.16em] text-[#f15a24]">Order summary</div>
                    <div class="mt-5 space-y-4 text-sm">
                        <div class="flex justify-between gap-4"><span class="text-slate-600">Course</span><strong class="text-right text-slate-950">{{ $course->title }}</strong></div>
                        <div class="flex justify-between gap-4"><span class="text-slate-600">Default amount</span><strong class="text-slate-950">৳{{ number_format((float) $amount, 0) }}</strong></div>
                        <div class="flex justify-between gap-4"><span class="text-slate-600">Status</span><strong class="text-[#292b86]">{{ __('frontend.checkout_pending') }}</strong></div>
                    </div>
                </div>

                <div class="rounded-[1.75rem] bg-gradient-to-br from-[#292b86] to-[#f15a24] p-6 text-white shadow-xl shadow-[#292b86]/15">
                    <h2 class="text-xl font-black">{{ __('frontend.secure_checkout') }}</h2>
                    <ul class="mt-4 space-y-3 text-sm text-white/85">
                        <li class="flex gap-2"><i class="fa-solid fa-check mt-1"></i>{{ __('frontend.secure_checkout_item_1') }}</li>
                        <li class="flex gap-2"><i class="fa-solid fa-check mt-1"></i>{{ __('frontend.secure_checkout_item_2') }}</li>
                        <li class="flex gap-2"><i class="fa-solid fa-check mt-1"></i>{{ __('frontend.secure_checkout_item_3') }}</li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>
</main>
@endsection
