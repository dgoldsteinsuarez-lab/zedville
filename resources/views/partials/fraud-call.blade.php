{{-- The fraud call on screen (App\Services\FraudService, proposal «Fraudes»: "tras la pregunta del día o el medidor
     de ánimo se abre una ventana de llamada entrante"): it rings once the mood meter, the question of the day and the
     plan of the month are done (layouts/profile); answered, the caller speaks and the student chooses what to do -
     the server says what happened. Declined, it rings again on the next page. Needs $fraud, $moodShown, $quizShown,
     $planShown. --}}
@php
    $t = \App\Services\FraudService::amounts();
    $cfg = [
        'url' => route('fraud.call', $fraud),
        'csrf' => csrf_token(),
        'caller' => __('fraud.callers.' . $fraud->script),
        'number' => in_array($fraud->script, ['bank_code', 'safe_account'], true) ? \App\Services\FraudService::BANK_PHONE : '+00 611 000 000',
        'lines' => __('fraud.lines.' . $fraud->script, $t),
        'answers' => collect(\App\Services\FraudService::answers($fraud))->keys()->map(fn ($key) => ['key' => $key, 'text' => __('fraud.answers.' . $key)])->all(),
        'wait' => ['mood' => $moodShown, 'quiz' => $quizShown, 'plan' => $planShown],
    ];
@endphp
<div x-data="fraudCall(@js($cfg))" x-init="init()" x-show="open" x-cloak class="zfc-back" style="display:none" role="dialog" aria-modal="true" aria-labelledby="zfc-caller">
    <div class="zfc-phone">
        <template x-if="stage === 'ringing'">
            <div class="zfc-ring">
                <p class="zfc-incoming">📞 {{ __('fraud.call_incoming') }}</p>
                <p class="zfc-caller" id="zfc-caller" x-text="cfg.caller"></p>
                <p class="zfc-number" x-text="cfg.number"></p>
                <div class="zfc-buttons">
                    <button type="button" class="zfc-decline" @click="open = false">{{ __('fraud.call_decline') }}</button>
                    <button type="button" class="zfc-answer" @click="stage = 'talk'">{{ __('fraud.call_answer') }}</button>
                </div>
            </div>
        </template>
        <template x-if="stage !== 'ringing'">
            <div class="zfc-talk">
                <p class="zfc-caller" x-text="cfg.caller"></p>
                <template x-for="(line, i) in cfg.lines" :key="i"><p class="zfc-bubble" x-text="line"></p></template>
                <template x-if="stage === 'talk'">
                    <div>
                        <p class="zfc-choose">{{ __('fraud.call_choose') }}</p>
                        <template x-for="a in cfg.answers" :key="a.key">
                            <button type="button" class="zfc-option" @click="answer(a.key)" :disabled="busy" x-text="a.text"></button>
                        </template>
                    </div>
                </template>
                <template x-if="stage === 'done' && result">
                    <div class="zfc-result" :class="result.status === 'safe' ? 'is-safe' : 'is-fell'">
                        <p class="zfc-title" x-text="result.title"></p>
                        <p x-text="result.text"></p>
                        <p x-text="result.name"></p>
                        <p x-show="result.sign"><strong>{{ __('fraud.signs_title') }}:</strong> <span x-text="result.sign"></span></p>
                        <p><strong>{{ __('fraud.good_title') }}:</strong> <span x-text="result.good"></span></p>
                        <p class="zfc-slogan" x-text="result.slogan"></p>
                        <div class="zfc-buttons">
                            <a x-show="result.resolve" :href="result.resolve" class="zfc-answer">{{ __('fraud.go_resolve') }}</a>
                            <button type="button" class="zfc-decline" @click="open = false">{{ __('fraud.call_close') }}</button>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
<style>
    .zfc-back { position: fixed; inset: 0; z-index: 130; background: rgba(15, 23, 32, .55); display: flex; align-items: center; justify-content: center; padding: 16px; }
    .zfc-phone { width: 100%; max-width: 380px; max-height: 92vh; overflow-y: auto; background: #101826; color: #F4F7FA; border-radius: 28px; padding: 22px 18px; box-shadow: 0 20px 50px rgba(0,0,0,.35); }
    .zfc-ring { text-align: center; padding: 24px 0 8px; }
    .zfc-incoming { font-size: 15px; color: #AFC0D3; margin: 0 0 18px; }
    .zfc-caller { font: 800 22px/1.25 "Manrope", sans-serif; margin: 0 0 4px; text-align: center; }
    .zfc-number { font-size: 16px; color: #AFC0D3; margin: 0 0 30px; }
    .zfc-buttons { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; margin-top: 10px; }
    .zfc-answer, .zfc-decline { border: 0; border-radius: 999px; padding: 12px 22px; font-weight: 800; font-size: 15px; cursor: pointer; text-decoration: none; }
    .zfc-answer { background: #1FAE6B; color: #fff; }
    .zfc-decline { background: #D9443B; color: #fff; }
    .zfc-talk .zfc-caller { margin-bottom: 14px; }
    .zfc-bubble { background: #22324A; border-radius: 16px 16px 16px 4px; padding: 10px 12px; margin: 0 0 10px; font-size: 15px; line-height: 1.45; }
    .zfc-choose { font-weight: 800; margin: 16px 0 8px; }
    .zfc-option { display: block; width: 100%; text-align: left; background: #F4F7FA; color: #101826; border: 0; border-radius: 12px; padding: 10px 12px; margin-bottom: 8px; font-size: 15px; cursor: pointer; }
    .zfc-option:disabled { opacity: .6; }
    .zfc-result { background: #F4F7FA; color: #101826; border-radius: 16px; padding: 14px; margin-top: 12px; font-size: 15px; line-height: 1.45; }
    .zfc-result.is-safe { border: 3px solid #1FAE6B; }
    .zfc-result.is-fell { border: 3px solid #D9443B; }
    .zfc-result p { margin: 0 0 8px; }
    .zfc-title { font: 800 20px/1.25 "Manrope", sans-serif; }
    .zfc-slogan { font-weight: 800; }
</style>
<script>
    window.fraudCall = function (cfg) {
        return {
            cfg, open: false, stage: 'ringing', busy: false, result: null,
            init() {
                // after the mood meter, the question of the day and the plan of the month (layouts/profile)
                const ready = () => (!cfg.wait.mood || window.__moodDone === true)
                    && (!cfg.wait.quiz || window.__quizDone === true)
                    && (!cfg.wait.plan || window.__planDone === true);
                const wait = setInterval(() => {
                    if (ready()) {
                        clearInterval(wait);
                        setTimeout(() => { this.open = true; }, 1500);
                    }
                }, 400);
            },
            async answer(key) {
                if (this.busy) return;
                this.busy = true;
                try {
                    const response = await fetch(cfg.url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                        body: JSON.stringify({ answer: key }),
                    });
                    if (response.ok) {
                        this.result = await response.json();
                        this.stage = 'done';
                    }
                } finally {
                    this.busy = false;
                }
            },
        };
    };
</script>
