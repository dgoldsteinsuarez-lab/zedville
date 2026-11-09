@extends('layouts.profile')
@section('no_page_back', '1'){{-- has its own back link at the top --}}

@section('title', 'Wellbeing Room - Educational Finance Department')

@section('content')
@php
    $categoryName = fn ($category) => \Illuminate\Support\Facades\Lang::has('cityhall.categories.' . $category)
        ? __('cityhall.categories.' . $category) : ucfirst($category);
@endphp
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;1,400&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    :root {
      --gold: #8A6D1D; --gold-light: rgba(138,109,29,0.10); --gold-border: rgba(138,109,29,0.30);
      --teal: #016950; --teal-light: rgba(1,105,80,0.10); --teal-border: rgba(1,105,80,0.25);
      --green: #00A47D; --green-light: rgba(0,164,125,0.10); --green-border: rgba(0,164,125,0.25);
      --blue: #1D4ED8; --blue-light: rgba(29,78,216,0.10); --blue-border: rgba(29,78,216,0.25);
      --purple: #7C3AED; --purple-light: rgba(124,58,237,0.10);
      --glass: rgba(255,255,255,0.92); --shadow: 0 4px 24px rgba(0,0,0,0.09);
      --radius: 16px; --radius-sm: 10px;
    }
   

    .wb-layout {
      max-width: 1060px; margin: 0 auto;
      padding: 2rem 1.5rem 4rem;
      display: flex; flex-direction: column; gap: 1.25rem;
    }

    /* ── BREADCRUMB ── */

    /* ── HEADER ── */
    .wb-header {
      background: var(--glass); border: 0.5px solid var(--teal-border);
      border-radius: var(--radius); padding: 1.25rem 1.5rem; box-shadow: var(--shadow);
      display: flex; align-items: center; justify-content: space-between;
      animation: fadeUp 0.4s ease both;
    }
    .wb-header-left { display: flex; align-items: center; gap: 14px; }
    .wb-icon {
      width: 48px; height: 48px; border-radius: 12px;
      background: var(--teal-light); border: 0.5px solid var(--teal-border);
      display: flex; align-items: center; justify-content: center; font-size: 22px; color: var(--teal);
    }
    .wb-title { font-family: 'Lora', serif; font-size: 20px; font-weight: 600; color: #222222; }
    .wb-subtitle { font-size: 12px; color: #5C5C5C; margin-top: 2px; }

    /* ── FILTER BAR ── */
    .filter-bar {
      display: flex; gap: 8px; flex-wrap: wrap;
      animation: fadeUp 0.4s 0.07s ease both;
    }
    .filter-btn {
      padding: 7px 16px; border-radius: 20px;
      font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 500;
      border: 0.5px solid var(--teal-border); background: var(--glass);
      color: #5C5C5C; cursor: pointer; transition: all 0.15s;
    }
    .filter-btn:hover { background: var(--teal-light); color: var(--teal); }
    .filter-btn.active {
      background: var(--teal-light); color: var(--teal);
      border-color: var(--teal-border);
    }

    /* ── SECTION LABEL ── */
    .sec-label {
      font-size: 12px; font-weight: 500; text-transform: uppercase;
      letter-spacing: 0.08em; color: #999999;
    }

    /* ── FEATURED ARTICLE (large card at top) ── */
    .featured-card {
      background: var(--glass); border: 0.5px solid var(--teal-border);
      border-radius: var(--radius); padding: 1.5rem;
      box-shadow: var(--shadow); display: flex; gap: 1.5rem; align-items: flex-start;
      cursor: pointer; transition: border-color 0.2s, transform 0.18s;
      text-decoration: none;
      animation: fadeUp 0.4s 0.12s ease both;
    }
    .featured-card:hover { border-color: var(--teal); transform: translateY(-2px); }
    .featured-icon {
      width: 56px; height: 56px; border-radius: 14px; flex-shrink: 0;
      background: var(--teal-light); border: 0.5px solid var(--teal-border);
      display: flex; align-items: center; justify-content: center; font-size: 26px; color: var(--teal);
    }
    .featured-body { flex: 1; }
    .featured-tag {
      display: inline-block; font-size: 12px; font-weight: 500;
      padding: 2px 8px; border-radius: 8px; margin-bottom: 8px;
    }
    .tag-financial { background: var(--gold-light); color: var(--gold); }
    .tag-stress    { background: rgba(220,38,38,0.10); color: #DC2626; }
    .tag-lifestyle { background: var(--purple-light); color: var(--purple); }
    .tag-general   { background: var(--teal-light); color: var(--teal); }
    .tag-video     { background: var(--blue-light); color: var(--blue); }

    .new-badge {
      display: inline-block; font-size: 12px; font-weight: 500;
      padding: 2px 7px; border-radius: 8px; margin-left: 6px;
      background: rgba(1,105,80,0.12); color: var(--teal);
      vertical-align: middle;
    }
    .featured-title {
      font-family: 'Lora', serif; font-size: 17px; font-weight: 600;
      color: #222222; line-height: 1.5; margin-bottom: 6px;
    }
    .featured-desc { font-size: 13px; color: #5C5C5C; line-height: 1.6; margin-bottom: 10px; }
    .featured-meta { font-size: 12px; color: #999999; display: flex; align-items: center; gap: 12px; }
    .read-more {
      font-size: 12px; font-weight: 500; color: var(--teal);
      display: flex; align-items: center; gap: 4px; margin-top: 10px;
    }

    /* ── CONTENT GRID ── */
    .content-grid {
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;
      animation: fadeUp 0.4s 0.18s ease both;
    }

    /* ── ARTICLE CARD ── */
    .article-card {
      background: var(--glass); border: 0.5px solid var(--teal-border);
      border-radius: var(--radius); padding: 1rem 1.1rem;
      box-shadow: var(--shadow); cursor: pointer;
      transition: border-color 0.2s, transform 0.18s;
      text-decoration: none; display: flex; flex-direction: column; gap: 8px;
    }
    .article-card:hover { border-color: var(--teal); transform: translateY(-2px); }
    .article-icon {
      width: 36px; height: 36px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .ai-teal   { background: var(--teal-light); color: var(--teal); }
    .ai-green  { background: var(--green-light); color: var(--green); }
    .ai-gold   { background: var(--gold-light); color: var(--gold); }
    .ai-stress { background: rgba(220,38,38,0.10); color: #DC2626; }
    .ai-purple { background: var(--purple-light); color: var(--purple); }

    .article-title {
      font-family: 'Lora', serif; font-size: 13px; font-weight: 600;
      color: #222222; line-height: 1.5;
    }
    .article-desc { font-size: 12px; color: #5C5C5C; line-height: 1.5; }
    .article-meta { font-size: 12px; color: #999999; display: flex; align-items: center; gap: 8px; margin-top: auto; }

    /* ── VIDEO CARD ── */
    .video-card {
      background: var(--glass); border: 0.5px solid var(--blue-border);
      border-radius: var(--radius); padding: 1rem 1.1rem;
      box-shadow: var(--shadow); cursor: pointer;
      transition: border-color 0.2s, transform 0.18s;
      text-decoration: none; display: flex; flex-direction: column; gap: 8px;
    }
    .video-card:hover { border-color: var(--blue); transform: translateY(-2px); }
    .video-thumb {
      width: 100%; height: 80px; border-radius: 8px;
      background: var(--blue-light); border: 0.5px solid var(--blue-border);
      display: flex; align-items: center; justify-content: center;
      position: relative; overflow: hidden;
    }
    .video-play {
      width: 32px; height: 32px; border-radius: 50%;
      background: var(--blue); display: flex; align-items: center; justify-content: center;
      color: white; font-size: 14px;
    }
    .video-title {
      font-family: 'Lora', serif; font-size: 13px; font-weight: 600;
      color: #222222; line-height: 1.5;
    }
    .video-meta { font-size: 12px; color: #999999; display: flex; align-items: center; gap: 8px; margin-top: auto; }
    .video-duration {
      display: inline-flex; align-items: center; gap: 3px;
      font-size: 12px; font-weight: 500;
      background: var(--blue-light); color: var(--blue);
      padding: 2px 7px; border-radius: 8px;
    }

    /* ── EMPTY STATE ── */
    .empty-state {
      text-align: center; padding: 3rem 1rem;
      color: #999999; font-size: 13px; grid-column: 1 / -1;
    }
    .empty-state i { font-size: 36px; display: block; margin-bottom: 10px; opacity: 0.35; }

    /* ── READ ARTICLE MODAL ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.45); z-index: 100;
      align-items: flex-start; justify-content: center;
      padding: 2rem 1rem; overflow-y: auto;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: white; border-radius: var(--radius);
      padding: 2rem; max-width: 680px; width: 100%;
      box-shadow: 0 8px 40px rgba(0,0,0,0.18);
      position: relative; margin: auto;
      animation: fadeUp 0.3s ease both;
    }
    .modal-close {
      position: absolute; top: 1rem; right: 1rem;
      width: 32px; height: 32px; border-radius: 50%;
      background: rgba(0,0,0,0.06); border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; color: #5C5C5C; transition: background 0.15s;
    }
    .modal-close:hover { background: rgba(0,0,0,0.12); }
    .modal-tag { margin-bottom: 10px; }
    .modal-title {
      font-family: 'Lora', serif; font-size: 22px; font-weight: 600;
      color: #222222; line-height: 1.4; margin-bottom: 8px;
    }
    .modal-meta { font-size: 12px; color: #999999; margin-bottom: 1.25rem; display: flex; gap: 12px; }
    .modal-body {
      font-size: 14px; color: #5C5C5C; line-height: 1.8;
      border-top: 0.5px solid var(--teal-border); padding-top: 1.25rem;
    }
    .modal-body p { margin-bottom: 1rem; }

    @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }

    @media (max-width: 700px) {
      .content-grid { grid-template-columns: 1fr; }
      .featured-card { flex-direction: column; }
      .wb-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    }
    @media (max-width: 900px) {
      .content-grid { grid-template-columns: 1fr 1fr; }
    }
  </style>  
@endpush
{{-- Back top left, where every page has it (Daniel, 2026-10-27) --}}
<a href="{{ route('education.city-hall') }}" class="zvBackBtn wb-back inline-flex items-center gap-2 py-2 px-4 text-sm">
  <span aria-hidden="true">&larr;</span> {{ __('cityhall.back_to_city_hall') }}
</a>
<div class="wb-layout">

  <!-- HEADER -->
  <div class="wb-header">
    <div class="wb-header-left">
      <div class="wb-icon"><i class="ti ti-heart" aria-hidden="true"></i></div>
      <div>
        <div class="wb-title">{{ __('cityhall.wellbeing_room') }}</div>
        <div class="wb-subtitle">{{ __('cityhall.wellbeing_desc') }}</div>
      </div>
    </div>
  </div>

  <!-- FILTER BAR -->
  <!-- IT TEAM: filter buttons show/hide cards by category using data-category attribute -->
  <div class="filter-bar">
    <button class="filter-btn active" onclick="filterContent('all')">{{ __('cityhall.all') }}</button>
    <button class="filter-btn" onclick="filterContent('financial')">
      <i class="ti ti-coins" style="font-size:12px;vertical-align:-1px;" aria-hidden="true"></i> {{ __('cityhall.categories.financial') }}
    </button>
    <button class="filter-btn" onclick="filterContent('stress')">
      <i class="ti ti-brain" style="font-size:12px;vertical-align:-1px;" aria-hidden="true"></i> {{ __('cityhall.categories.stress') }}
    </button>
    <button class="filter-btn" onclick="filterContent('lifestyle')">
      <i class="ti ti-sun" style="font-size:12px;vertical-align:-1px;" aria-hidden="true"></i> {{ __('cityhall.categories.lifestyle') }}
    </button>
    <button class="filter-btn" onclick="filterContent('video')">
      <i class="ti ti-player-play" style="font-size:12px;vertical-align:-1px;" aria-hidden="true"></i> {{ __('cityhall.videos') }}
    </button>
  </div>

  <!-- FEATURED ARTICLE -->
  <!-- IT TEAM: show the most recent published article as featured -->
  <!-- IT TEAM: mark as seen — POST /city-hall/wellbeing/{id}/seen when student opens it -->
@if($featured)

<div id="featured-section">

    <div class="sec-label" style="margin-bottom:9px;">
        {{ __('cityhall.featured') }}
    </div>

    <div class="featured-card"
         data-category="{{ $featured->category }}"
         onclick="openArticle('{{ $featured->id }}')">

        <div class="featured-icon">
            <i class="ti ti-heart"></i>
        </div>

        <div class="featured-body">

            <span class="featured-tag tag-{{ $featured->category }}">
                {{ $categoryName($featured->category) }}
            </span>

            @if($featured->featured)
                <span class="new-badge">
                    {{ __('cityhall.featured') }}
                </span>
            @endif

            <div class="featured-title">
                {{ $featured->title }}
            </div>

            <div class="featured-desc">
                {{ $featured->short_description }}
            </div>

            <div class="featured-meta">

                <span>
                    <i class="ti ti-clock"></i>
                    {{ $featured->read_time }}
                </span>

                <span>
                    {{ $featured->published_at?->translatedFormat('d M Y') }}
                </span>

            </div>

            <div class="read-more">
                {{ __('cityhall.read_article') }}

                <i class="ti ti-arrow-right"></i>

            </div>

        </div>

    </div>

</div>

@endif

  <!-- CONTENT GRID -->
  <div class="sec-label">{{ __('cityhall.all_content') }}</div>
  <div class="content-grid" id="contentGrid">

    <!-- IT TEAM: repeat article-card block for each published article from wellbeing_posts WHERE type='article' -->

    <!-- ARTICLE 1 -->
    @foreach($articles as $article)

<div class="article-card"
     data-category="{{ $article->category }}"
     onclick="openArticle('{{ $article->id }}')">

    <div class="article-icon ai-{{ $article->category }}">
        <i class="ti ti-heart"></i>
    </div>

    <span class="featured-tag tag-{{ $article->category }}">
        {{ $categoryName($article->category) }}
    </span>

    <div class="article-title">
        {{ $article->title }}
    </div>

    <div class="article-desc">
        {{ $article->short_description }}
    </div>

    <div class="article-meta">

        <span>

            <i class="ti ti-clock"></i>

            {{ $article->read_time }}

        </span>

    </div>

</div>

@endforeach

   

    

    

    

    <!-- IT TEAM: repeat video-card block for each published video from wellbeing_posts WHERE type='video' -->

    <!-- VIDEO 1 -->
    @foreach($videos as $video)

<div class="video-card"
     data-category="video {{ $video->category }}"
     onclick="window.open('{{ $video->youtube_url }}','_blank')">

    <div class="video-thumb">

        <div class="video-play">

            <i class="ti ti-player-play"></i>

        </div>

    </div>

    <span class="featured-tag tag-video">
        {{ __('cityhall.video') }}
    </span>

    <span class="featured-tag tag-{{ $video->category }}" style="margin-left:4px;">

        {{ $categoryName($video->category) }}

    </span>

    <div class="video-title">

        {{ $video->title }}

    </div>

    <div class="video-meta">

        <span>

            <i class="ti ti-clock"></i>

            {{ $video->read_time }}

        </span>

    </div>

</div>

@endforeach

  </div><!-- /contentGrid -->

</div><!-- /wb-layout -->
@foreach($articles as $article)

<div id="article-data-{{ $article->id }}" data-category="{{ $article->category }}" style="display:none;">

    <div class="title">{{ $article->title }}</div>

    <div class="category">{{ $categoryName($article->category) }}</div>

    <div class="read">{{ $article->read_time }}</div>

    <div class="date">{{ $article->published_at?->translatedFormat('d M Y') }}</div>

    <div class="body">

        {!! nl2br(e($article->content)) !!}

    </div>

</div>

@endforeach
<!-- ── READ ARTICLE MODAL ── -->
<!-- IT TEAM: populate modal content dynamically from DB when article is clicked -->
<div class="modal-overlay" id="articleModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()" aria-label="{{ __('cityhall.close') }}">
      <i class="ti ti-x" aria-hidden="true"></i>
    </button>
    <div class="modal-tag">
      <span class="featured-tag" id="modalTag"></span>
    </div>
    <div class="modal-title" id="modalTitle"></div>
    <div class="modal-meta">
      <span id="modalReadTime"></span>
      <span id="modalDate"></span>
    </div>
    <div class="modal-body" id="modalBody">
    </div>
  </div>
</div>

<script>
  // ── FILTER ──
  function filterContent(category) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    event.currentTarget.classList.add('active');

    const featured = document.getElementById('featured-section') || { style: {} };
    const cards = document.querySelectorAll('#contentGrid [data-category]');

    if (category === 'all') {
      featured.style.display = 'block';
      cards.forEach(c => c.style.display = 'flex');
    } else if (category === 'video') {
      featured.style.display = 'none';
      cards.forEach(c => {
        c.style.display = c.dataset.category.includes('video') ? 'flex' : 'none';
      });
    } else {
      featured.style.display =
        document.querySelector('.featured-card')?.dataset.category === category ? 'block' : 'none';
      cards.forEach(c => {
        c.style.display = c.dataset.category.includes(category) ? 'flex' : 'none';
      });
    }
  }

  // ── OPEN ARTICLE MODAL ──
  // Shows the published article from its hidden #article-data-{id} block above.
  // (It used to show built-in sample articles instead of the real ones.)
  function openArticle(id) {
    const source = document.getElementById('article-data-' + id);
    if (!source) return;
    const tag = document.getElementById('modalTag');
    tag.textContent = source.querySelector('.category').textContent.trim();
    tag.className = 'featured-tag tag-' + source.dataset.category;
    document.getElementById('modalTitle').textContent = source.querySelector('.title').textContent.trim();
    const readTime = document.getElementById('modalReadTime');
    readTime.innerHTML = '<i class="ti ti-clock" style="font-size:12px;vertical-align:-1px;" aria-hidden="true"></i> ';
    readTime.append(source.querySelector('.read').textContent.trim());
    document.getElementById('modalDate').textContent = source.querySelector('.date').textContent.trim();
    document.getElementById('modalBody').innerHTML = source.querySelector('.body').innerHTML;
    document.getElementById('articleModal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    document.getElementById('articleModal').classList.remove('open');
    document.body.style.overflow = '';
  }

  // Close modal on overlay click
  document.getElementById('articleModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
  });

  // Close modal on Escape key
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

  // ── OPEN VIDEO ──
  // IT TEAM: replace URL with actual video_url from DB
  function openVideo(url) {
    if (confirm(@json(__('cityhall.external_video_confirm')))) {
      window.open(url, '_blank', 'noopener');
      // IT TEAM: also mark as seen
      // fetch('/city-hall/wellbeing/' + id + '/seen', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
    }
  }
</script>
@endsection