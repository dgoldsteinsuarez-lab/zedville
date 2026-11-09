@extends('layouts.profile')

@section('title', $activity->studentTitle())

{{-- "Match the words" (Hybrid Classes > Language Activities, 2026-10-29): the
     words in the language the class learns, big; their English meanings
     smaller. Tap a word, then its meaning. All matched = done. --}}
@section('content')
@php
    $lang = $activity->language ?: app()->getLocale();
@endphp
<style>
    .zw { max-width: 920px; }
    .zw-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .zw-col { display: flex; flex-direction: column; gap: 10px; }
    .zw-btn { width: 100%; text-align: left; padding: 12px 16px; border-radius: 12px; border: 2px solid #D2DDDB; background: #fff; color: #0F2A22; transition: border-color .15s, background .15s; }
    .zw-btn:hover:not(:disabled) { border-color: #00A47D; }
    .zw-word { font: 800 20px/1.3 "Manrope", sans-serif; }
    .zw-meaning { font-size: 15px; font-style: italic; color: #44564F; }
    .zw-btn.is-on { border-color: #00A47D; background: #F0FBF7; box-shadow: 0 0 0 3px rgba(0, 164, 125, .15); }
    .zw-btn.is-matched { border-color: #B7E3D6; background: #E2F6EF; color: #016950; cursor: default; }
    .zw-btn.is-wrong { border-color: #E5484D; background: #FDECEC; }
    .zw-tag { margin: 0 0 4px; font-size: 13px; font-weight: 700; color: #9D174D; }
    .zw-note { margin: 12px 0 0; padding: 10px 14px; border-radius: 10px; font-size: 15px; }
    .zw-note.is-ok { background: #E2F6EF; color: #016950; font-weight: 700; }
    .zw-note.is-wrong { background: #FFF4DE; color: #7A4F00; }
    @media (max-width: 560px) { .zw-word { font-size: 17px; } .zw-btn { padding: 10px 12px; } }
</style>

<div class="zw" x-data="wordMatch()">
    <div class="tailCard w-full rounded-lg p-6 mb-5">
        <p class="zw-tag">
            {{ __('engagement.easy_tag') }}
            @if ($running)
                · {{ __('engagement.easy_' . $activity->position) }} · {{ __('engagement.easy_badge', ['badge' => __('badges.engagement')]) }}
            @endif
        </p>
        <h1 class="text-xl font-bold mb-2">{{ $activity->studentTitle() }}</h1>
        @foreach ($activity->steps() as $step)
            <p class="text-gray-700 mb-1">{{ $step }}</p>
        @endforeach
        <p class="text-gray-500 text-sm mb-0">{{ __('engagement.words_how') }}</p>
        @if (!$running)
            <p class="zw-note is-wrong">{{ __('engagement.words_practice') }}</p>
        @elseif ($done)
            <p class="zw-note is-ok">✓ {{ __('engagement.words_already') }}</p>
        @endif
    </div>

    <div class="tailCard w-full rounded-lg p-6">
        <div class="zw-grid">
            <div class="zw-col" role="group" aria-label="{{ __('engagement.words_word') }}">
                <template x-for="(pair, i) in pairs" :key="'w' + i">
                    <button type="button" class="zw-btn zw-word" lang="{{ $lang }}"
                            :class="{ 'is-on': picked === i, 'is-matched': matched[i] !== undefined }"
                            :disabled="matched[i] !== undefined" @click="picked = i; message = ''"
                            x-text="pair[0]"></button>
                </template>
            </div>
            <div class="zw-col" role="group" aria-label="{{ __('engagement.words_meaning') }}">
                <template x-for="(meaning, j) in meanings" :key="'m' + j">
                    <button type="button" class="zw-btn zw-meaning" lang="en"
                            :class="{ 'is-matched': used(meaning), 'is-wrong': wrong === j }"
                            :disabled="used(meaning)" @click="choose(meaning, j)"
                            x-text="meaning"></button>
                </template>
            </div>
        </div>
        <p class="zw-note is-wrong" x-show="message === 'wrong'" x-cloak>{{ __('engagement.words_try_again') }}</p>
        <p class="zw-note is-wrong" x-show="message === 'pick'" x-cloak>{{ __('engagement.words_pick_first') }}</p>
        <p class="zw-note is-ok" x-show="finished" x-cloak>
            ✓ {{ __('engagement.words_done') }}
            <span x-show="counted">{{ __('engagement.words_counted') }}</span>
        </p>
    </div>
</div>

<script>
    function wordMatch() {
        return {
            pairs: @js($pairs),
            meanings: @js($meanings),
            matched: {},
            picked: null,
            wrong: null,
            message: '',
            finished: false,
            counted: false,
            used(meaning) { return Object.values(this.matched).includes(meaning); },
            choose(meaning, j) {
                if (this.picked === null) { this.message = 'pick'; return; }
                if (this.pairs[this.picked][1] === meaning) {
                    this.matched[this.picked] = meaning;
                    this.picked = null;
                    this.message = '';
                    if (Object.keys(this.matched).length === this.pairs.length) this.finish();
                } else {
                    this.wrong = j;
                    this.message = 'wrong';
                    setTimeout(() => { this.wrong = null; }, 700);
                }
            },
            async finish() {
                this.finished = true;
                const answers = this.pairs.map((pair, i) => this.matched[i]);
                try {
                    const response = await fetch(@js(route('easy.words.done', $activity)), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ answers }),
                    });
                    const result = await response.json();
                    this.counted = !!(result.ok && result.counted);
                } catch (e) { /* the game is done; the badge is counted next time */ }
            },
        };
    }
</script>
@endsection
