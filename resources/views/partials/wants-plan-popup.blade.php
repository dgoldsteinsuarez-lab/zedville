{{-- Spending 4, the plan of the month (Daniel, 2026-10-31: "Saldría después
     de la pregunta del día y solo una vez al mes, en un recuadro grande, con
     la explicación y una imagen"; "podemos darle la opción de ahorrar el
     dinero o hacer tres actividades de bajo presupuesto", then "solo eligen
     una actividad de Spending 4"). Four plans of the class's Spending 4 -
     Low or High (App\Services\WantsPlanService) - and one is chosen. Opens
     once the mood and the question of the day are done; it can't be closed
     without a choice.
     Expects $wantsPlanBox, $hasMoodToday, $hasQuizToday. --}}
<div x-data="wantsPlanPopup()" x-init="init()">
    <div x-show="open" x-transition.opacity class="fixed w-full h-full z-100 overflow-y-auto top-0 left-0 overflow-x-hidden themeModal" style="display:none;">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="modalDilog zv-plan-dialog">
            <div class="modalContent bg-white rounded-xl z-100 border border-[#D2DDDB] zv-plan">
                <div x-show="!result">
                    <div class="zv-plan-head">
                        <span class="zv-plan-icon" aria-hidden="true">🎟️</span>
                        <div>
                            <span class="zv-plan-label">{{ match ($wantsPlanBox['kind']) { 'high' => 'High Spending 4', 'mid' => 'Spending 4', default => 'Low Spending 4' } }}</span>
                            <h4>{{ __('plans.title', ['month' => now()->locale(app()->getLocale())->isoFormat('MMMM')]) }}</h4>
                            <p>{{ __('plans.subtitle') }}</p>
                        </div>
                    </div>
                    <div class="zv-plan-grid">
                        @foreach ($wantsPlanBox['offers'] as $offer)
                            <div class="zv-plan-card">
                                <img src="{{ $offer['image'] }}" alt="" class="zv-plan-img">
                                <div class="zv-plan-body">
                                    <h5>{{ $offer['name'] }}</h5>
                                    <p class="zv-plan-desc">{{ $offer['description'] }}</p>
                                    <p class="zv-plan-cost">{{ $offer['cost_line'] }}</p>
                                    <p class="zv-plan-total">{{ $offer['total_line'] ?? '' }}</p>
                                    <button type="button" class="zv-plan-choose" :disabled="saving" @click="choose({{ $offer['id'] }})">
                                        <span x-show="saving !== {{ $offer['id'] }}">{{ __('plans.choose') }}</span>
                                        <span x-show="saving === {{ $offer['id'] }}">{{ __('plans.choosing') }}</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="zv-plan-error" x-show="error" x-text="error"></p>
                </div>

                <div x-show="result" class="zv-plan-done">
                    <template x-if="result">
                        <div>
                            <div class="zv-plan-done-pics"><img :src="result.image" alt=""></div>
                            <h4>{{ __('plans.chosen_title') }}</h4>
                            <p><b x-text="result.name"></b> · <span x-text="result.total_line || result.cost_line"></span></p>
                            <p x-show="result.paid_on" x-text="result.paid_on"></p>
                            <p class="zv-plan-paid" x-show="result.paid_today" x-text="result.paid_today"></p>
                            {{-- not enough money: when it will be paid (Daniel, 2026-11-02: "se descuenta
                                 justo cuando llegue el salario, y se debe notificar en este mensaje") --}}
                            <p class="zv-plan-wait" x-show="result.waiting"><span aria-hidden="true">⏳</span><span x-text="result.waiting"></span></p>
                        </div>
                    </template>
                    {{-- __planDone: what comes after it waits for it (the fraud call, partials/fraud-call) --}}
                    <button type="button" class="zv-plan-choose" @click="open = false; window.__planDone = true">{{ __('plans.ok') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .zv-plan-dialog { max-width: 1120px; width: calc(100% - 32px); }
    .zv-plan { padding: 20px 24px; max-height: calc(100vh - 32px); overflow-y: auto; }
    .zv-plan-head { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
    .zv-plan-icon { display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%;
        background: #EEF9F5; font-size: 22px; flex: none; }
    .zv-plan-label { display: inline-block; padding: 2px 10px; border-radius: 999px; background: #FFF7ED; color: #B45309; font-size: 12px;
        font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .zv-plan-head h4 { margin: 2px 0 0; font-size: 22px; font-weight: 800; color: #111; text-transform: none; }
    .zv-plan-head p { margin: 2px 0 0; font-size: 14px; color: #4B5B55; }
    .zv-plan-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
    .zv-plan-card { display: flex; flex-direction: column; border: 1px solid #D2DDDB; border-radius: 14px; overflow: hidden; background: #fff;
        transition: box-shadow .2s, border-color .2s; }
    .zv-plan-card:hover { border-color: #00A47D; box-shadow: 0 10px 26px rgba(15, 42, 34, .12); }
    .zv-plan-img { display: block; width: 100%; aspect-ratio: 2 / 1; object-fit: cover; background: #EEF9F5; }
    .zv-plan-body { display: flex; flex-direction: column; flex: 1; padding: 10px 14px 12px; }
    .zv-plan-body h5 { margin: 0 0 4px; font-size: 16px; font-weight: 800; color: #014A38; line-height: 1.25; }
    .zv-plan-desc { margin: 0 0 8px; font-size: 13px; line-height: 1.45; color: #333; flex: 1; }
    .zv-plan-cost { margin: 0; font-size: 14px; font-weight: 700; color: #B45309; }
    .zv-plan-total { margin: 2px 0 10px; font-size: 13px; color: #4B5B55; }
    .zv-plan-choose { width: 100%; padding: 9px 14px; border: 0; border-radius: 999px; background: #00A47D; color: #fff; font-weight: 700;
        font-size: 14px; cursor: pointer; }
    .zv-plan-choose:hover { background: #016950; }
    .zv-plan-choose:disabled { opacity: .6; cursor: wait; }
    .zv-plan-error { margin: 10px 0 0; color: #B42318; font-size: 13px; }
    .zv-plan-done { max-width: 560px; margin: 0 auto; text-align: center; }
    .zv-plan-done-pics { display: flex; gap: 8px; justify-content: center; margin-bottom: 12px; }
    .zv-plan-done-pics img { width: 100%; max-width: 240px; aspect-ratio: 2 / 1; object-fit: cover; border-radius: 12px; }
    .zv-plan-done-icon { font-size: 48px; margin-bottom: 6px; }
    .zv-plan-done h4 { margin: 0 0 8px; font-size: 22px; font-weight: 800; color: #016950; text-transform: none; }
    .zv-plan-done p { margin: 0 0 6px; font-size: 14px; color: #333; }
    .zv-plan-paid { color: #016950 !important; font-weight: 700; }
    .zv-plan-done .zv-plan-wait { display: flex; gap: 10px; align-items: flex-start; max-width: 470px; margin: 12px auto 0; padding: 10px 14px;
        border: 1px solid #FED7AA; border-radius: 12px; background: #FFF7ED; color: #7C2D12; line-height: 1.45; text-align: left; }
    .zv-plan-done .zv-plan-choose { margin-top: 10px; max-width: 260px; }
    @media (max-width: 1100px) { .zv-plan-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 600px) { .zv-plan-grid { grid-template-columns: 1fr; } }
</style>
<script>
    window.wantsPlanPopup = function () {
        return {
            open: false,
            saving: false,
            result: null,
            error: '',
            T: @json(['error' => __('plans.error')]),
            init() {
                // after the mood and the question of the day (layouts/profile)
                const quizShown = @json(!$hasQuizToday);
                const moodShown = @json(!$hasMoodToday);
                const ready = () => quizShown ? window.__quizDone === true : (!moodShown || window.__moodDone === true);
                const wait = setInterval(() => {
                    if (ready()) {
                        clearInterval(wait);
                        setTimeout(() => { this.open = true; }, 600);
                    }
                }, 300);
            },
            choose(id) {
                if (this.saving) return;
                this.saving = id;
                this.error = '';
                fetch(@json(route('wants-plan.choose')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ plan_id: id }),
                })
                    .then((r) => r.ok ? r.json() : Promise.reject(r))
                    .then((data) => { this.result = data; })
                    .catch(() => { this.error = this.T.error; })
                    .finally(() => { this.saving = false; });
            },
        };
    };
</script>
