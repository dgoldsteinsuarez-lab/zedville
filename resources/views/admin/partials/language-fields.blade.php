{{-- The language a Grade or Class uses: default language, whether students
     may switch, and standard/beginner wording. Expects $settings
     (language_mode, default_language, language_level) and a unique $idPrefix.
     Hybrid: for students who speak little of the language - see App\Support\Hybrid. --}}
<div class="mb-3">
    <label class="form-label" for="{{ $idPrefix }}Language">Language</label>
    <select name="default_language" id="{{ $idPrefix }}Language" class="form-control">
        @foreach (config('zedville.languages') as $code => $languageName)
            <option value="{{ $code }}" @selected(($settings['default_language'] ?? 'en') === $code)>{{ $languageName }}</option>
        @endforeach
    </select>
</div>
<div class="form-check">
    <input type="checkbox" class="form-check-input" style="appearance: auto; -webkit-appearance: checkbox" name="language_mode" value="locked" id="{{ $idPrefix }}Locked"
           @checked(($settings['language_mode'] ?? 'free') === 'locked')>
    <label class="form-check-label" for="{{ $idPrefix }}Locked">Lock to this language (students can't switch)</label>
</div>
<div class="mb-3 mt-3">
    <label class="form-label" for="{{ $idPrefix }}Level">Language level</label>
    <select name="language_level" id="{{ $idPrefix }}Level" class="form-control">
        <option value="standard" @selected(($settings['language_level'] ?? 'standard') === 'standard')>Standard</option>
        <option value="beginner" @selected(($settings['language_level'] ?? 'standard') === 'beginner')>Beginner (A2/B1 - simpler wording)</option>
        <option value="hybrid" @selected(($settings['language_level'] ?? 'standard') === 'hybrid')>Hybrid (English + this language: words in the language, short texts in both, explanations in English)</option>
    </select>
</div>
