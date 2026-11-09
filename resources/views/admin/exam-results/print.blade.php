{{-- A real exam on paper (Daniel, 2026-10-31: "the tutor should be able to
     print the exam in case they don't want to do it online"), in the
     language picked. With $key: the same pages with the right answers marked
     and a short list of them at the end, for whoever corrects it. --}}
@php $letters = range('A', 'Z'); @endphp
<!doctype html>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ct('curriculum', $item->title) }}{{ $key ? ' - ' . __('exams.print_answer_key') : '' }}</title>
    <style>
        @page { size: A4; margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #EEF2F1; color: #111; font: 14px/1.45 "Open Sans", Arial, sans-serif; }
        .sheet { max-width: 210mm; margin: 24px auto; padding: 18mm 16mm; background: #fff; box-shadow: 0 2px 12px rgba(0, 0, 0, .12); }
        .bar { max-width: 210mm; margin: 16px auto 0; display: flex; gap: 8px; justify-content: flex-end; align-items: center; font-size: 13px; }
        .bar a, .bar button { padding: 7px 14px; border-radius: 6px; border: 1px solid #00A47D; background: #fff; color: #016950; text-decoration: none; cursor: pointer; font: inherit; }
        .bar button { background: #00A47D; color: #fff; }
        .bar .on { background: #E7F6F1; font-weight: 700; }
        h1 { margin: 0 0 4px; font-size: 22px; }
        .key-tag { display: inline-block; margin-left: 8px; padding: 2px 8px; border: 1.5px solid #B42318; color: #B42318; border-radius: 4px; font-size: 13px; vertical-align: middle; }
        .fields { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 14px; margin: 16px 0 10px; }
        .fields div { border-bottom: 1px solid #111; padding-bottom: 2px; font-size: 12px; color: #444; }
        .note { margin: 0 0 14px; font-style: italic; color: #444; }
        .stimulus { margin: 0 0 16px; padding: 12px 14px; border: 1px solid #BBB; border-radius: 6px; }
        .stimulus img, .q img { display: block; max-width: 100%; max-height: 80mm; margin: 6px auto; }
        .q { break-inside: avoid; page-break-inside: avoid; padding: 10px 0 12px; border-top: 1px solid #DDD; }
        .q h2 { margin: 0 0 6px; font-size: 15px; }
        .q h2 span { color: #666; font-weight: 600; margin-right: 6px; }
        .opt { display: flex; gap: 10px; align-items: flex-start; margin: 5px 0 0 4px; }
        .box { flex: none; width: 16px; height: 16px; margin-top: 2px; border: 1.5px solid #111; border-radius: 3px; text-align: center; line-height: 13px; font-weight: 700; }
        .opt.right { font-weight: 700; }
        .opt.right .box { background: #111; color: #fff; }
        .answers { break-inside: avoid; margin-top: 18px; padding-top: 10px; border-top: 2px solid #111; }
        .answers ol { columns: 4; margin: 6px 0 0; padding-left: 22px; }
        @media print {
            body { background: #fff; }
            .bar { display: none; }
            .sheet { margin: 0; padding: 0; box-shadow: none; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="bar">
        @foreach (['en' => 'EN', 'es' => 'ES', 'fr' => 'FR', 'de' => 'DE'] as $code => $label)
            <a href="{{ route('admin.exam-results.print', ['item' => $item, 'lang' => $code, 'key' => $key ? 1 : null]) }}" @class(['on' => $lang === $code])>{{ $label }}</a>
        @endforeach
        <a href="{{ route('admin.exam-results.print', ['item' => $item, 'lang' => $lang, 'key' => $key ? null : 1]) }}">{{ $key ? 'Exam for students' : 'Answer key' }}</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <div class="sheet">
        <h1>{{ ct('curriculum', $item->title) }}@if ($key)<span class="key-tag">{{ __('exams.print_answer_key') }}</span>@endif</h1>

        @unless ($key)
            <div class="fields">
                <div>{{ __('exams.print_name') }}</div>
                <div>{{ __('exams.print_class') }}</div>
                <div>{{ __('exams.print_date') }}</div>
                <div>{{ __('exams.print_points') }}: &nbsp; / {{ $item->questions->count() }}</div>
            </div>
            <p class="note">{{ __('exams.print_instructions') }}</p>
        @endunless

        @if ($item->image || $item->description)
            <div class="stimulus">
                @if ($item->image)<img src="{{ ct_image($item->image) }}" alt="">@endif
                @if ($item->description)<div>{{ ct('curriculum', $item->description) }}</div>@endif
            </div>
        @endif

        @foreach ($item->questions as $i => $question)
            <div class="q">
                <h2><span>{{ __('exams.print_question') }} {{ $i + 1 }}</span>{{ ct('curriculum', $question->question_text) }}</h2>
                @foreach (array_filter([$question->image, $question->image_2]) as $picture)
                    <img src="{{ ct_image($picture) }}" alt="">
                @endforeach
                @foreach ($question->options as $j => $option)
                    <div @class(['opt', 'right' => $key && $option->is_correct])>
                        <span class="box">{{ $key && $option->is_correct ? '✓' : '' }}</span>
                        <span><strong>{{ $letters[$j] }}</strong> &nbsp;{{ ct('curriculum', $option->option_text) }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach

        @if ($key)
            <div class="answers">
                <strong>{{ __('exams.print_answers') }}</strong>
                <ol>
                    @foreach ($item->questions as $question)
                        <li>{{ $question->options->values()->search(fn ($o) => $o->is_correct) !== false ? $letters[$question->options->values()->search(fn ($o) => $o->is_correct)] : '—' }}</li>
                    @endforeach
                </ol>
            </div>
        @endif
    </div>
</body>
</html>
