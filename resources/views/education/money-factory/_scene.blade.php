{{-- The Money Factory as a picture to explore (Daniel, 2026-11-02: "me gustaría hacerlo más visual
     ... con más imágenes, y si el cursor pasa por un sitio darle la posibilidad de abrirlo"): each
     place glows and says its name when the cursor is on it, and opens what it is about - a window
     (what money is, why Zedland prints it, how a banknote is made), the exchange or the tracker on
     this page, or a page apart. The same places are listed under the picture, for phones. A hybrid
     class gets the windows' short texts ("en híbrido, haz todo más corto y sencillo"). --}}
<section class="zm-card zm-scene-box" aria-labelledby="zm-scene-title" x-data="zmScene()" @keydown.escape.window="close()">
    <h2 id="zm-scene-title"><span class="zm-ico" aria-hidden="true">🗺️</span>{{ __('money.scene_title') }}</h2>
    <p class="zm-small" style="margin:-4px 0 10px">{{ __('money.scene_hint') }}</p>

    <div class="zm-scene">
        @if ($scene['image'])
            <img class="zm-scene-art" src="{{ $scene['image'] }}" alt="{{ __('money.scene_title') }}">
        @else
            @include('education.money-factory._scene-art')
        @endif
        @foreach ($scene['spots'] as $key => $spot)
            {{-- near the right edge the name opens to the left, near the bottom upwards --}}
            <button type="button" class="zm-spot zm-spot--{{ $key }}{{ $spot['x'] > 75 ? ' is-right' : '' }}{{ $spot['y'] > 70 ? ' is-up' : '' }}" style="left:{{ $spot['x'] }}%;top:{{ $spot['y'] }}%"
                    @click="go(@js($spot['open']), $el)">
                <span class="zm-spot-dot" aria-hidden="true">{{ $spot['icon'] }}</span>
                <span class="zm-spot-label">{{ $spot['label'] }}</span>
            </button>
        @endforeach
    </div>

    <ul class="zm-spot-list">
        @foreach ($scene['spots'] as $spot)
            <li><button type="button" @click="go(@js($spot['open']), $el)"><span aria-hidden="true">{{ $spot['icon'] }}</span> {{ $spot['label'] }}</button></li>
        @endforeach
    </ul>

    {{-- the windows --}}
    <div class="zm-pop-back" x-show="open" x-cloak x-transition.opacity @click.self="close()">
        <div class="zm-pop" role="dialog" aria-modal="true" :aria-labelledby="'zm-pop-' + open">
            <button type="button" class="zm-pop-close" x-ref="close" @click="close()" aria-label="{{ __('money.close') }}">✕</button>

            {{-- the coin: what money is, and its three functions (ECB: medium of exchange, unit of account, store of value) --}}
            <div x-show="open === 'what'">
                <h2 id="zm-pop-what"><span class="zm-ico" aria-hidden="true">💰</span>{{ __('money.what_title') }}</h2>
                <p>{{ __('money.functions_intro') }}</p>
                <ol class="zm-functions">
                    @foreach (['exchange' => '🛒', 'account' => '🏷️', 'store' => '🐷'] as $function => $icon)
                        <li><span class="zm-functions-ico" aria-hidden="true">{{ $icon }}</span>
                            <span><strong>{{ __('money.function_' . $function) }}</strong>{{ __('money.function_' . $function . '_text') }}</span></li>
                    @endforeach
                </ol>
            </div>

            {{-- the president of the Central Bank: why Zedland prints money --}}
            <div x-show="open === 'why'">
                <h2 id="zm-pop-why"><span class="zm-ico" aria-hidden="true">🏛️</span>{{ __('money.why_title') }}</h2>
                <div class="zm-says">
                    <span class="zm-says-who" aria-hidden="true">👩‍💼</span>
                    <p class="zm-says-text">{{ __('money.president_hello') }} {{ __('money.why_text') }}</p>
                </div>
                <ul class="zm-points">
                    <li><span aria-hidden="true">🔄</span>{{ __('money.why_replace') }}</li>
                    <li><span aria-hidden="true">📈</span>{{ __('money.why_grow') }}</li>
                    <li><span aria-hidden="true">⚖️</span>{{ __('money.why_careful') }}</li>
                </ul>
            </div>

            {{-- the factory: how a banknote is made --}}
            <div x-show="open === 'how'">
                <h2 id="zm-pop-how"><span class="zm-ico" aria-hidden="true">🏭</span>{{ __('money.how_title') }}</h2>
                <ol class="zm-steps">
                    @foreach (['design', 'paper', 'print', 'security', 'check'] as $step)
                        <li><strong>{{ __('money.step_' . $step) }}</strong><span>{{ __('money.step_' . $step . '_text') }}</span></li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</section>

<script>
    // The picture's places: a window opens over the page; a "#part" scrolls to it; a page opens.
    window.zmScene = function () {
        return {
            open: null,
            from: null,
            go(target, el) {
                if (target.startsWith('#')) {
                    document.querySelector(target)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else if (target.includes('/')) {
                    window.location.href = target;
                } else {
                    this.from = el;
                    this.open = target;
                    document.documentElement.style.overflow = 'hidden';
                    this.$nextTick(() => this.$refs.close.focus());
                }
            },
            close() {
                if (!this.open) return;
                this.open = null;
                document.documentElement.style.overflow = '';
                this.from && this.from.focus();
            },
        };
    };
</script>
