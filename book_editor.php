<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — book_editor.php
 * WYSIWYG "in-context" Book Spotlight editor — visual replica of book.php.
 * Auth: session + remember-me (same contract as strategy.php).
 * Submits to api/book_editor.php via AJAX FormData (unchanged endpoint/field names).
 */

require __DIR__ . '/api/conexion.php';
require __DIR__ . '/core/auth_check.php';
require __DIR__ . '/core/dev_bypass.php';

if (!lly_is_authenticated()) {
    header('Location: index.php');
    exit;
}

/* ── CSRF ──────────────────────────────────────────────────────────── */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');

/* ── Official fallback copy (Mandamiento: never render empty layouts) ─
   Mirrors the defaults hardcoded in book.php so the editor always shows
   real content, even on empty/partial DB rows. ── */
$heroTitleDefault = [
    'en' => 'The true story of a man who should have died nine times.',
    'es' => 'La historia real de un hombre que debería haber muerto nueve veces.',
];
$heroSubDefault = [
    'en' => 'I Died Nine Times. Love Brought Me Back.',
    'es' => 'Morí Nueve Veces. El Amor Me Trajo de Vuelta.',
];
$synopsisDefault = [
    'en' => 'My father survived a Japanese prison camp. I survived the jungles of New Guinea and Borneo, open-heart surgery, liver cancer, a liver transplant, depression, and more close calls than I can count. I buried dreams, damaged relationships, and often got in my own way. This is the story of a jungle boy who should have died many times — but didn\'t. More importantly, it is the story of how faith, forgiveness, and one remarkable woman taught me that surviving is not the same as living — and that one true love can arrive even after a lifetime of wrong turns.',
    'es' => 'Mi padre sobrevivió a un campo de prisioneros japonés. Yo sobreviví a las selvas de Nueva Guinea y Borneo, una cirugía de corazón abierto, cáncer de hígado, un trasplante de hígado, depresión, y más momentos límite de los que puedo contar. Enterré sueños, dañé relaciones, y muchas veces fui mi propio obstáculo. Esta es la historia de un niño de la selva que debió haber muerto muchas veces — pero no lo hizo. Más importante aún, es la historia de cómo la fe, el perdón y una mujer extraordinaria me enseñaron que sobrevivir no es lo mismo que vivir — y que el amor verdadero puede llegar incluso después de toda una vida de caminos equivocados.',
];
$amazonDefault = 'https://www.amazon.com/dp/ASIN_PLACEHOLDER';

$cardFallback = [
    1 => ['icon' => '🌴', 'en' => 'A childhood survival in the jungles of Borneo',       'es' => 'Supervivencia infantil en las selvas de Borneo'],
    2 => ['icon' => '🏹', 'en' => 'Poisoned arrows and tribal warfare',                   'es' => 'Flechas envenenadas y guerra tribal'],
    3 => ['icon' => '🎸', 'en' => 'An unexpected friendship with Eric Clapton',            'es' => 'Una amistad inesperada con Eric Clapton'],
    4 => ['icon' => '🏛️', 'en' => 'Boardrooms connected to Donald Trump',                'es' => 'Salas de juntas conectadas con Donald Trump'],
    5 => ['icon' => '🫀', 'en' => 'Open-heart surgery that should have ended it all',      'es' => 'Cirugía a corazón abierto que debió terminar con todo'],
    6 => ['icon' => '🔬', 'en' => 'Liver cancer and a life-saving transplant',             'es' => 'Cáncer de hígado y un trasplante que salvó su vida'],
    7 => ['icon' => '🌑', 'en' => 'A private battle with depression that nearly won',      'es' => 'Una batalla privada contra la depresión que casi gana'],
];
$testimonialFallback = [
    'quote_en'  => 'What a gift you’ve given me — and I mean that in every sense of the word. I’ve been reading your memoir with the kind of attention I rarely give anything anymore. You haven’t just told your story; you’ve drawn the reader into it. The writing is masterful — unhurried where it needs to breathe, gripping where the stakes are highest. That’s not an easy balance, but you found it. What moved me most was the full honesty of it — the mountaintop moments and the deep valley seasons both. So many memoirs flatten a life into a highlight reel. Yours doesn’t flinch. And that’s precisely what makes it inspiring rather than merely impressive. Journeying alongside you through these pages reminded me of what a life of genuine faith and courage actually looks like from the inside. I came away both humbled and encouraged.',
    'quote_es'  => 'Qué regalo me has dado — y lo digo en todo el sentido de la palabra. He estado leyendo tus memorias con el tipo de atención que rara vez le dedico a algo en estos días. No solo contaste tu historia; lograste que el lector se sumergiera en ella. La escritura es magistral — pausada donde necesita respirar, intensa donde la tensión es más alta. Ese no es un equilibrio fácil de lograr, pero tú lo encontraste. Lo que más me conmovió fue su honestidad absoluta — tanto los momentos en la cima como las temporadas en el valle más profundo. Tantas memorias reducen una vida a un resumen de mejores momentos. La tuya no se aparta de nada. Y es precisamente eso lo que la hace inspiradora y no solo impresionante. Acompañarte a través de estas páginas me recordó cómo se ve, desde adentro, una vida de fe y valentía genuinas. Terminé sintiéndome a la vez humilde y motivado.',
    'author_en' => 'Duane Hallock, Chief Communications and Marketing Officer, Red Cross',
    'author_es' => 'Duane Hallock, Director de Comunicación y Marketing, Cruz Roja',
];

/* ── Load ALL rows into a flat associative array ──────────────────── */
$c = [];      // $c['meta_key'] = ['en' => '...', 'es' => '...']
$cards = [];  // $cards[1..7]  = ['icon' => '', 'en' => '', 'es' => '']
for ($i = 1; $i <= 7; $i++) {
    $cards[$i] = [
        'icon' => htmlspecialchars($cardFallback[$i]['icon'], ENT_QUOTES, 'UTF-8'),
        'en'   => htmlspecialchars($cardFallback[$i]['en'],   ENT_QUOTES, 'UTF-8'),
        'es'   => htmlspecialchars($cardFallback[$i]['es'],   ENT_QUOTES, 'UTF-8'),
    ];
}

try {
    $pdo  = Conexion::getConnection();
    $rows = $pdo->query('SELECT meta_key, content_en, content_es FROM lly_book_content')->fetchAll();

    foreach ($rows as $row) {
        $k  = $row['meta_key'];
        $en = htmlspecialchars((string) ($row['content_en'] ?? ''), ENT_QUOTES, 'UTF-8');
        $es = htmlspecialchars((string) ($row['content_es'] ?? ''), ENT_QUOTES, 'UTF-8');

        if (preg_match('/^card_(\d+)$/', $k, $m)) {
            $idx = (int) $m[1];
            if ($idx >= 1 && $idx <= 7) {
                if ($en !== '') { $cards[$idx]['en'] = $en; }
                if ($es !== '') { $cards[$idx]['es'] = $es; }
            }
        } elseif (preg_match('/^card_(\d+)_icon$/', $k, $m)) {
            $idx = (int) $m[1];
            if ($idx >= 1 && $idx <= 7 && $en !== '') { $cards[$idx]['icon'] = $en; }
        } elseif (preg_match('/^card_(\d+)_img$/', $k, $m)) {
            // Card images are not rendered on the public page — ignored here.
        } else {
            $c[$k] = ['en' => $en, 'es' => $es];
        }
    }
} catch (Throwable $e) {
    error_log('[LLY book_editor.php] preload failed: ' . $e->getMessage());
}

/* Helper: return stored value or empty string */
function ed(string $key, string $lang, array $c): string
{
    return $c[$key][$lang] ?? '';
}

/* Helper: return stored value, falling back to official copy when the DB
   row is missing or blank for that language (already-escaped strings). */
function edFallback(string $key, string $lang, array $c, string $fallback): string
{
    $v = $c[$key][$lang] ?? '';
    return $v !== '' ? $v : htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
}

/* Determine current cover src for preview */
$currentCover = $c['book_cover_path']['en'] ?? 'assets/img/nine_live.png';
if ($currentCover === '') { $currentCover = 'assets/img/nine_live.png'; }
$currentCover = htmlspecialchars($currentCover, ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Book Editor Studio" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Book Editor Studio</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" />
  <link rel="icon" type="image/png" href="assets/img/logo.png" />
  <script src="assets/js/theme-init.js"></script>
</head>

<body data-active-lang="en" class="lly-editor-page">

  <!-- ══ TOPBAR ═══════════════════════════════════════════════════════ -->
  <header class="topbar" role="banner">
    <div class="container">
      <div class="topbar-inner">

        <a href="index.php" class="topbar-logo" aria-label="Back to Owner Dashboard">
          <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>Book Editor Studio</span>
          </div>
        </a>

        <div class="topbar-actions">

          <a href="index.php" class="topbar-back-btn">
            <span data-lang="en">⬅️ Back to Main Dashboard</span>
            <span data-lang="es">⬅️ Regresar al Dashboard Principal</span>
          </a>

          <nav class="topbar-nav" role="navigation" aria-label="Dashboard Views">
            <a class="topbar-nav-link active-nav" href="book_editor.php" aria-current="page">
              <span data-lang="en">✏️ Book Editor</span>
              <span data-lang="es">✏️ Editor del Libro</span>
            </a>
          </nav>

          <button class="theme-toggle" id="theme-toggle" aria-label="Switch to Night Mode" aria-pressed="false">
            <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            <svg class="icon-sun"  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m15.07-6.07l-.71.71M6.34 17.66l-.71.71m12.73 0l-.71-.71M6.34 6.34l-.71-.71M12 5a7 7 0 100 14A7 7 0 0012 5z"/></svg>
          </button>

          <div class="lang-toggle" role="group" aria-label="Language / Idioma">
            <button class="lang-btn active" id="btn-en" aria-pressed="true">EN</button>
            <button class="lang-btn"        id="btn-es" aria-pressed="false">ES</button>
          </div>

        </div>
      </div>
    </div>
  </header>

  <!-- ══ EDIT MODE BANNER — TOP ═══════════════════════════════════════ -->
  <div class="lly-edit-banner lly-edit-banner--top">
    <span data-lang="en">✏️ YOU'RE IN "MY BOOK" EDIT MODE — click any text or image below to change it.</span>
    <span data-lang="es">✏️ ESTÁS EN LA ZONA DE EDICIÓN DE LA PÁGINA "MY BOOK" — Haz clic en cualquier texto o imagen para modificarlo.</span>
  </div>

  <!-- ══ EDIT TOOLBAR ═════════════════════════════════════════════════ -->
  <div class="lly-edit-toolbar">
    <div class="lly-edit-toolbar-copy">
      <span data-lang="en">Live preview: <a href="book.php" target="_blank" rel="noopener">book.php</a></span>
      <span data-lang="es">Vista en vivo: <a href="book.php" target="_blank" rel="noopener">book.php</a></span>
    </div>
    <button type="button" class="editor-translate-btn" id="editor-translate-btn">
      <span class="editor-translate-btn-idle">
        <span data-lang="en">🌐 Translate Missing Fields</span>
        <span data-lang="es">🌐 Traducir Campos Faltantes</span>
      </span>
      <span class="editor-translate-btn-loading" aria-hidden="true">
        <span data-lang="en">Translating…</span>
        <span data-lang="es">Traduciendo…</span>
      </span>
    </button>
  </div>
  <div class="editor-alert editor-alert--hidden lly-container-alert" id="translate-alert" role="status" aria-live="polite"></div>

  <!-- ══ HIDDEN DATA FORM (never visible — WYSIWYG writes into it) ═══ -->
  <form id="book-editor-form" hidden>
    <input type="hidden" name="csrf_token" id="csrf_token_field" value="<?= $csrf ?>">

    <input type="hidden" name="hero_title_en" id="hero_title_en_input" value="<?= edFallback('hero_title', 'en', $c, $heroTitleDefault['en']) ?>">
    <input type="hidden" name="hero_title_es" id="hero_title_es_input" value="<?= edFallback('hero_title', 'es', $c, $heroTitleDefault['es']) ?>">
    <input type="hidden" name="hero_sub_en" id="hero_sub_en_input" value="<?= edFallback('hero_subtitle', 'en', $c, $heroSubDefault['en']) ?>">
    <input type="hidden" name="hero_sub_es" id="hero_sub_es_input" value="<?= edFallback('hero_subtitle', 'es', $c, $heroSubDefault['es']) ?>">
    <input type="hidden" name="amazon_link_url" id="amazon_link_url_input" value="<?= edFallback('amazon_link_url', 'en', $c, $amazonDefault) ?>">
    <input type="hidden" name="synopsis_en" id="synopsis_en_input" value="<?= edFallback('synopsis', 'en', $c, $synopsisDefault['en']) ?>">
    <input type="hidden" name="synopsis_es" id="synopsis_es_input" value="<?= edFallback('synopsis', 'es', $c, $synopsisDefault['es']) ?>">

    <?php for ($ci = 1; $ci <= 7; $ci++): $card = $cards[$ci]; ?>
    <input type="hidden" name="card_icon[<?= $ci ?>]" id="card_icon_input_<?= $ci ?>" value="<?= $card['icon'] ?>">
    <input type="hidden" name="card_en[<?= $ci ?>]" id="card_en_input_<?= $ci ?>" value="<?= $card['en'] ?>" data-original-val="<?= $card['en'] ?>">
    <input type="hidden" name="card_es[<?= $ci ?>]" id="card_es_input_<?= $ci ?>" value="<?= $card['es'] ?>">
    <?php endfor; ?>

    <input type="hidden" name="testimonial_en" id="testimonial_en_input"
           value="<?= edFallback('testimonial_quote', 'en', $c, $testimonialFallback['quote_en']) ?>"
           data-original-val="<?= edFallback('testimonial_quote', 'en', $c, $testimonialFallback['quote_en']) ?>">
    <input type="hidden" name="testimonial_es" id="testimonial_es_input" value="<?= edFallback('testimonial_quote', 'es', $c, $testimonialFallback['quote_es']) ?>">
    <input type="hidden" name="testimonial_author_en" id="testimonial_author_en_input"
           value="<?= edFallback('testimonial_author', 'en', $c, $testimonialFallback['author_en']) ?>"
           data-original-val="<?= edFallback('testimonial_author', 'en', $c, $testimonialFallback['author_en']) ?>">
    <input type="hidden" name="testimonial_author_es" id="testimonial_author_es_input" value="<?= edFallback('testimonial_author', 'es', $c, $testimonialFallback['author_es']) ?>">

    <input type="hidden" name="sample_chapter_en" id="sample_chapter_en_input" value="<?= ed('sample_chapter', 'en', $c) ?>">
    <input type="hidden" name="sample_chapter_es" id="sample_chapter_es_input" value="<?= ed('sample_chapter', 'es', $c) ?>">

    <?php for ($a = 1; $a <= 3; $a++): ?>
    <input type="hidden" name="article_<?= $a ?>_tag_en" id="article_<?= $a ?>_tag_en_input" value="<?= ed("article_{$a}_tag", 'en', $c) ?>">
    <input type="hidden" name="article_<?= $a ?>_tag_es" id="article_<?= $a ?>_tag_es_input" value="<?= ed("article_{$a}_tag", 'es', $c) ?>">
    <input type="hidden" name="article_<?= $a ?>_title_en" id="article_<?= $a ?>_title_en_input" value="<?= ed("article_{$a}_title", 'en', $c) ?>">
    <input type="hidden" name="article_<?= $a ?>_title_es" id="article_<?= $a ?>_title_es_input" value="<?= ed("article_{$a}_title", 'es', $c) ?>">
    <input type="hidden" name="article_<?= $a ?>_link" id="article_<?= $a ?>_link_input" value="<?= ed("article_{$a}_link", 'en', $c) ?>">
    <?php endfor; ?>

    <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png" hidden>
  </form>

  <!-- ══ WYSIWYG REPLICA OF book.php ═════════════════════════════════ -->
  <main>

    <!-- ══ BOOK HERO ══════════════════════════════════════════════════ -->
    <section class="section section-white book-hero">
      <div class="container">
        <div class="book-hero-inner">

          <div class="book-hero-copy">
            <h1 class="book-hero-title">
              <span data-lang="en" class="lly-editable" contenteditable="true" data-sync="hero_title_en_input"><?= edFallback('hero_title', 'en', $c, $heroTitleDefault['en']) ?></span>
              <span data-lang="es" class="lly-editable" contenteditable="true" data-sync="hero_title_es_input"><?= edFallback('hero_title', 'es', $c, $heroTitleDefault['es']) ?></span>
            </h1>
            <h2 class="book-hero-subtitle">
              <span data-lang="en" class="lly-editable" contenteditable="true" data-sync="hero_sub_en_input"><?= edFallback('hero_subtitle', 'en', $c, $heroSubDefault['en']) ?></span>
              <span data-lang="es" class="lly-editable" contenteditable="true" data-sync="hero_sub_es_input"><?= edFallback('hero_subtitle', 'es', $c, $heroSubDefault['es']) ?></span>
            </h2>

            <div class="book-authority-ribbon">
              <span class="book-authority-badge">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4h10l1.2 2.4H5.8L7 4zm-2.5 4h15a1 1 0 0 1 1 1v.6c0 .4-.3.7-.7.8-2.2.6-5.8 1.1-9.8 1.1s-7.6-.5-9.8-1.1c-.4-.1-.7-.4-.7-.8V9a1 1 0 0 1 1-1zM5 11.2c2.4.5 5.2.8 7.5.8s5.1-.3 7.5-.8V19a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7.8z"/></svg>
                <span data-lang="en">Amazon #1 New Release</span>
                <span data-lang="es">#1 Nuevo Lanzamiento en Amazon</span>
              </span>
              <div class="book-authority-logos" aria-hidden="true"></div>
            </div>

            <div class="book-feature-cta">
              <span class="lly-url-editor">
                <a href="<?= edFallback('amazon_link_url', 'en', $c, $amazonDefault) ?>" class="book-btn book-btn--primary lly-amazon-link" target="_blank" rel="noopener noreferrer">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4h10l1.2 2.4H5.8L7 4zm-2.5 4h15a1 1 0 0 1 1 1v.6c0 .4-.3.7-.7.8-2.2.6-5.8 1.1-9.8 1.1s-7.6-.5-9.8-1.1c-.4-.1-.7-.4-.7-.8V9a1 1 0 0 1 1-1zM5 11.2c2.4.5 5.2.8 7.5.8s5.1-.3 7.5-.8V19a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7.8z"/></svg>
                  <span data-lang="en">Buy on Amazon</span>
                  <span data-lang="es">Comprar en Amazon</span>
                </a>
                <button type="button" class="lly-url-edit-btn" data-hidden="amazon_link_url_input" data-targets=".lly-amazon-link" aria-label="Edit Amazon link">✏️</button>
              </span>
              <button type="button" class="book-btn book-btn--secondary" onclick="document.getElementById('lly-chapter-dialog').showModal()">
                <span data-lang="en">✏️ Read / Edit Sample Chapter</span>
                <span data-lang="es">✏️ Leer / Editar Capítulo de Muestra</span>
              </button>
            </div>
          </div>

          <div class="book-hero-visual">
            <div class="book-feature-frame">
              <label class="lly-img-edit" for="cover_image">
                <img src="<?= $currentCover ?>" alt="Nine Lives. One True Love — book cover" class="book-feature-cover lly-cover-img" loading="lazy" />
                <span class="lly-img-edit-overlay">
                  <span class="lly-img-edit-icon">🖼️</span>
                  <span data-lang="en">Click to replace cover</span>
                  <span data-lang="es">Clic para reemplazar portada</span>
                </span>
              </label>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ══ SHARE WIDGET (static — not content-managed) ═════════════════ -->
    <section class="section section-white">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">Spread the Story</span>
          <span data-lang="es">Comparte la Historia</span>
        </p>
        <h2 class="section-title">
          <span data-lang="en">Share This <em>Journey</em></span>
          <span data-lang="es">Comparte Este <em>Viaje</em></span>
        </h2>
        <div class="share-grid">
          <span class="share-btn share-btn--whatsapp"><span data-lang="en">WhatsApp</span><span data-lang="es">WhatsApp</span></span>
          <span class="share-btn share-btn--facebook"><span data-lang="en">Facebook</span><span data-lang="es">Facebook</span></span>
          <span class="share-btn share-btn--x"><span data-lang="en">X</span><span data-lang="es">X</span></span>
          <span class="share-btn share-btn--linkedin"><span data-lang="en">LinkedIn</span><span data-lang="es">LinkedIn</span></span>
        </div>
        <p class="editor-hint" data-lang="en">Share buttons are not editable — shown here for visual reference only.</p>
        <p class="editor-hint" data-lang="es">Los botones para compartir no son editables — se muestran aquí solo como referencia visual.</p>
      </div>
    </section>

    <!-- ══ CURIOSITY GRID ════════════════════════════════════════════ -->
    <section class="section section-truth">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">Inside the Story</span>
          <span data-lang="es">Dentro de la Historia</span>
        </p>
        <h2 class="section-title">
          <span data-lang="en">Seven Moments That <em>Shouldn't Exist</em></span>
          <span data-lang="es">Siete Momentos Que <em>No Deberían Existir</em></span>
        </h2>

        <div class="arf-grid">
          <?php for ($ci = 1; $ci <= 7; $ci++): $card = $cards[$ci]; ?>
          <div class="curiosity-card">
            <div class="curiosity-icon lly-editable" contenteditable="true" data-sync="card_icon_input_<?= $ci ?>" data-single-line="true"><?= $card['icon'] ?></div>
            <p class="curiosity-text">
              <span data-lang="en" class="lly-editable" contenteditable="true" data-sync="card_en_input_<?= $ci ?>"><?= $card['en'] ?></span>
              <span data-lang="es" class="lly-editable" contenteditable="true" data-sync="card_es_input_<?= $ci ?>"><?= $card['es'] ?></span>
            </p>
          </div>
          <?php endfor; ?>
        </div>

        <blockquote class="pull-quote-vip">
          <p data-lang="en">"So many memoirs flatten a life into a highlight reel. Yours doesn't flinch."</p>
          <p data-lang="es">"Tantas memorias reducen una vida a un resumen de mejores momentos. La tuya no se aparta de nada."</p>
          <footer class="pull-quote-vip-author">
            <span data-lang="en">Duane Hallock, Chief Communications &amp; Marketing Officer, Red Cross</span>
            <span data-lang="es">Duane Hallock, Director de Comunicación y Marketing, Cruz Roja</span>
          </footer>
        </blockquote>

      </div>
    </section>

    <!-- ══ FEATURED BOOK ═════════════════════════════════════════════ -->
    <section class="section section-white">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">Author Spotlight</span>
          <span data-lang="es">Reflector del Autor</span>
        </p>
        <h2 class="section-title">
          <span data-lang="en">From the Captain's <em>Bookshelf</em></span>
          <span data-lang="es">Desde la <em>Biblioteca</em> del Capitán</span>
        </h2>

        <div class="book-feature">
          <div class="book-feature-copy">
            <p class="book-feature-tag">
              <span data-lang="en">New Release · September 2, 2026</span>
              <span data-lang="es">Nuevo Lanzamiento · 2 de Septiembre, 2026</span>
            </p>
            <h3 class="book-feature-title">Nine Lives. One True Love.</h3>
            <p class="book-feature-subtitle">
              <span data-lang="en" class="lly-editable" contenteditable="true" data-sync="hero_sub_en_input"><?= edFallback('hero_subtitle', 'en', $c, $heroSubDefault['en']) ?></span>
              <span data-lang="es" class="lly-editable" contenteditable="true" data-sync="hero_sub_es_input"><?= edFallback('hero_subtitle', 'es', $c, $heroSubDefault['es']) ?></span>
            </p>

            <div class="book-feature-synopsis">
              <p><span data-lang="en" class="lly-editable" contenteditable="true" data-sync="synopsis_en_input"><?= edFallback('synopsis', 'en', $c, $synopsisDefault['en']) ?></span></p>
              <p><span data-lang="es" class="lly-editable" contenteditable="true" data-sync="synopsis_es_input"><?= edFallback('synopsis', 'es', $c, $synopsisDefault['es']) ?></span></p>
            </div>

            <p class="book-feature-sub-label">
              <span data-lang="en">Why This Book Matters</span>
              <span data-lang="es">Por Qué Este Libro Importa</span>
            </p>
            <ul class="book-feature-list">
              <li><span data-lang="en">A father who survived a Japanese prison camp during World War II</span><span data-lang="es">Un padre que sobrevivió a un campo de prisioneros japonés durante la Segunda Guerra Mundial</span></li>
              <li><span data-lang="en">A childhood spent in the jungles of New Guinea and Borneo</span><span data-lang="es">Una infancia vivida en las selvas de Nueva Guinea y Borneo</span></li>
              <li><span data-lang="en">Poisoned arrows, exploding bridges, and tribal warfare</span><span data-lang="es">Flechas envenenadas, puentes que explotaban y guerras tribales</span></li>
              <li><span data-lang="en">Four marriages, painful mistakes, and one true love</span><span data-lang="es">Cuatro matrimonios, errores dolorosos y un amor verdadero</span></li>
              <li><span data-lang="en">Open-heart surgery, liver cancer, and a liver transplant</span><span data-lang="es">Cirugía de corazón abierto, cáncer de hígado y un trasplante de hígado</span></li>
              <li><span data-lang="en">A battle with depression that nearly ended everything</span><span data-lang="es">Una batalla contra la depresión que casi terminó con todo</span></li>
              <li><span data-lang="en">Unexpected friendships with celebrities, world-class leaders, and music icon Eric Clapton</span><span data-lang="es">Amistades inesperadas con celebridades, líderes de talla mundial y el icono de la música Eric Clapton</span></li>
              <li><span data-lang="en">Encounters that brought me from jungle villages to boardrooms connected to Donald Trump</span><span data-lang="es">Encuentros que me llevaron desde aldeas en la selva hasta salas de juntas conectadas con Donald Trump</span></li>
              <li><span data-lang="en">The extraordinary journey from missionary kid to minister, CEO, transplant survivor, and yacht entrepreneur</span><span data-lang="es">El extraordinario viaje de hijo de misioneros a ministro, director ejecutivo, sobreviviente de trasplante y empresario náutico</span></li>
            </ul>
            <p class="editor-hint" data-lang="en">The list above is fixed page copy and isn't editable here.</p>
            <p class="editor-hint" data-lang="es">La lista de arriba es texto fijo de la página y no es editable aquí.</p>

            <blockquote class="book-feature-testimonial">
              <p><span data-lang="en" class="lly-editable" contenteditable="true" data-sync="testimonial_en_input"><?= edFallback('testimonial_quote', 'en', $c, $testimonialFallback['quote_en']) ?></span></p>
              <p><span data-lang="es" class="lly-editable" contenteditable="true" data-sync="testimonial_es_input"><?= edFallback('testimonial_quote', 'es', $c, $testimonialFallback['quote_es']) ?></span></p>
              <footer class="book-feature-testimonial-author">
                <span data-lang="en" class="lly-editable" contenteditable="true" data-sync="testimonial_author_en_input" data-single-line="true"><?= edFallback('testimonial_author', 'en', $c, $testimonialFallback['author_en']) ?></span>
                <span data-lang="es" class="lly-editable" contenteditable="true" data-sync="testimonial_author_es_input" data-single-line="true"><?= edFallback('testimonial_author', 'es', $c, $testimonialFallback['author_es']) ?></span>
              </footer>
            </blockquote>

            <div class="book-feature-cta">
              <span class="lly-url-editor">
                <a href="<?= edFallback('amazon_link_url', 'en', $c, $amazonDefault) ?>" class="book-btn book-btn--primary lly-amazon-link" target="_blank" rel="noopener noreferrer">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4h10l1.2 2.4H5.8L7 4zm-2.5 4h15a1 1 0 0 1 1 1v.6c0 .4-.3.7-.7.8-2.2.6-5.8 1.1-9.8 1.1s-7.6-.5-9.8-1.1c-.4-.1-.7-.4-.7-.8V9a1 1 0 0 1 1-1zM5 11.2c2.4.5 5.2.8 7.5.8s5.1-.3 7.5-.8V19a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7.8z"/></svg>
                  <span data-lang="en">Buy on Amazon</span>
                  <span data-lang="es">Comprar en Amazon</span>
                </a>
                <button type="button" class="lly-url-edit-btn" data-hidden="amazon_link_url_input" data-targets=".lly-amazon-link" aria-label="Edit Amazon link">✏️</button>
              </span>
              <button type="button" class="book-btn book-btn--secondary" onclick="document.getElementById('lly-chapter-dialog').showModal()">
                <span data-lang="en">✏️ Read / Edit Sample Chapter</span>
                <span data-lang="es">✏️ Leer / Editar Capítulo de Muestra</span>
              </button>
            </div>

            <nav class="book-feature-editorial-nav" aria-label="Book editorial links">
              <span><span data-lang="en">About the Author</span><span data-lang="es">Sobre el Autor</span></span>
              <span class="book-feature-editorial-sep">|</span>
              <span><span data-lang="en">Reviews</span><span data-lang="es">Reseñas</span></span>
              <span class="book-feature-editorial-sep">|</span>
              <span><span data-lang="en">Media</span><span data-lang="es">Prensa</span></span>
            </nav>
          </div>

          <div class="book-feature-visual">
            <div class="book-feature-frame">
              <label class="lly-img-edit" for="cover_image">
                <img src="<?= $currentCover ?>" alt="Nine Lives. One True Love — book cover" class="book-feature-cover lly-cover-img" loading="lazy" />
                <span class="lly-img-edit-overlay">
                  <span class="lly-img-edit-icon">🖼️</span>
                  <span data-lang="en">Click to replace cover</span>
                  <span data-lang="es">Clic para reemplazar portada</span>
                </span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ══ LATEST ARTICLES ═══════════════════════════════════════════ -->
    <section class="section section-truth">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">From the Blog</span>
          <span data-lang="es">Desde el Blog</span>
        </p>
        <h2 class="section-title">
          <span data-lang="en">Latest <em>Articles</em></span>
          <span data-lang="es">Últimos <em>Artículos</em></span>
        </h2>
        <div class="arf-grid">
          <?php
          $articleImgs = [
              1 => 'assets/img/news/article-summer-la-paz.jpg',
              2 => 'assets/img/news/article-espiritu-santo.jpg',
              3 => 'assets/img/news/article-balandra.jpg',
          ];
          for ($a = 1; $a <= 3; $a++):
          ?>
          <article class="arf-card">
            <div class="arf-card-media"><img src="<?= htmlspecialchars($articleImgs[$a], ENT_QUOTES, 'UTF-8') ?>" alt="Article <?= $a ?>" loading="lazy" /></div>
            <div class="arf-card-body">
              <p class="arf-card-tag">
                <span data-lang="en" class="lly-editable" contenteditable="true" data-sync="article_<?= $a ?>_tag_en_input" data-single-line="true"><?= ed("article_{$a}_tag", 'en', $c) ?></span>
                <span data-lang="es" class="lly-editable" contenteditable="true" data-sync="article_<?= $a ?>_tag_es_input" data-single-line="true"><?= ed("article_{$a}_tag", 'es', $c) ?></span>
              </p>
              <h3 class="arf-card-title">
                <span data-lang="en" class="lly-editable" contenteditable="true" data-sync="article_<?= $a ?>_title_en_input" data-single-line="true"><?= ed("article_{$a}_title", 'en', $c) ?></span>
                <span data-lang="es" class="lly-editable" contenteditable="true" data-sync="article_<?= $a ?>_title_es_input" data-single-line="true"><?= ed("article_{$a}_title", 'es', $c) ?></span>
              </h3>
              <span class="lly-url-editor">
                <a href="<?= ed("article_{$a}_link", 'en', $c) ?>" class="arf-card-link lly-article-link-<?= $a ?>" target="_blank" rel="noopener noreferrer">
                  <span data-lang="en">Read Article</span><span data-lang="es">Leer Artículo</span> →
                </a>
                <button type="button" class="lly-url-edit-btn" data-hidden="article_<?= $a ?>_link_input" data-targets=".lly-article-link-<?= $a ?>" aria-label="Edit article link">✏️</button>
              </span>
            </div>
          </article>
          <?php endfor; ?>
        </div>
      </div>
    </section>

    <!-- ══ BRIDGE BANNER (static — not content-managed) ════════════════ -->
    <section class="book-bridge-banner">
      <div class="container">
        <h2 class="book-bridge-title">
          <span data-lang="en">Meet the man behind Lover Lips Yachts.</span>
          <span data-lang="es">Conoce al hombre detrás de Lover Lips Yachts.</span>
        </h2>
        <p class="book-bridge-text">
          <span data-lang="en">Discover the 5-star charter experience in La Paz.</span>
          <span data-lang="es">Descubre la experiencia de charter 5 estrellas en La Paz.</span>
        </p>
        <span class="book-btn book-btn--primary">
          <span data-lang="en">Explore the Fleet</span>
          <span data-lang="es">Explorar la Flota</span>
        </span>
      </div>
    </section>

  </main>

  <!-- ══ SAMPLE CHAPTER DIALOG (always available in edit mode) ═══════ -->
  <dialog class="chapter-dialog" id="lly-chapter-dialog" aria-labelledby="chapter-dialog-title">
    <div class="chapter-dialog-inner">
      <button class="chapter-dialog-close" type="button" onclick="document.getElementById('lly-chapter-dialog').close()" aria-label="Close">✕</button>
      <p class="chapter-dialog-eyebrow">
        <span data-lang="en">Sample Chapter Preview</span>
        <span data-lang="es">Vista Previa del Capítulo de Muestra</span>
      </p>
      <h2 class="chapter-dialog-title" id="chapter-dialog-title">Nine Lives. One True Love.</h2>
      <div class="chapter-dialog-body">
        <p><span data-lang="en" class="lly-editable" contenteditable="true" data-sync="sample_chapter_en_input" data-placeholder-en="Click here to write the sample chapter…"><?= ed('sample_chapter', 'en', $c) ?></span></p>
        <p><span data-lang="es" class="lly-editable" contenteditable="true" data-sync="sample_chapter_es_input" data-placeholder-es="Haz clic aquí para escribir el capítulo de muestra…"><?= ed('sample_chapter', 'es', $c) ?></span></p>
      </div>
    </div>
  </dialog>

  <!-- ══ EDIT MODE BANNER — BOTTOM ════════════════════════════════════ -->
  <div class="lly-edit-banner lly-edit-banner--bottom">
    <span data-lang="en">✏️ YOU'RE STILL IN "MY BOOK" EDIT MODE — don't forget to save your changes.</span>
    <span data-lang="es">✏️ SIGUES EN LA ZONA DE EDICIÓN DE "MY BOOK" — no olvides guardar tus cambios.</span>
  </div>

  <footer class="footer" role="contentinfo">
    <div class="container">
      <div class="footer-logo">
        <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
        <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
      </div>
      <p>
        <strong>Lover Lips Yachts</strong> &nbsp;·&nbsp;
        <span data-lang="en">Book Editor Studio · Confidential</span>
        <span data-lang="es">Estudio Editor del Libro · Confidencial</span>
      </p>
    </div>
  </footer>

  <!-- ══ FLOATING SAVE BAR ═══════════════════════════════════════════ -->
  <div class="lly-savebar">
    <span class="lly-savebar-note">
      <span data-lang="en">Changes save to the live database and appear on book.php right away.</span>
      <span data-lang="es">Los cambios se guardan en la base de datos activa y aparecen en book.php de inmediato.</span>
    </span>
    <button type="button" class="lly-savebar-btn" id="lly-save-btn">
      <span class="lly-savebar-btn-idle">
        <span data-lang="en">💾 Save Changes</span>
        <span data-lang="es">💾 Guardar Cambios</span>
      </span>
      <span class="lly-savebar-btn-loading" aria-hidden="true">
        <span data-lang="en">Saving…</span>
        <span data-lang="es">Guardando…</span>
      </span>
    </button>
  </div>

  <div class="lly-toast" id="lly-toast" role="alert" aria-live="assertive"></div>

  <script src="assets/js/main.js" defer></script>

  <script>
  (function () {
    var form      = document.getElementById('book-editor-form');
    var saveBtn   = document.getElementById('lly-save-btn');
    var toast     = document.getElementById('lly-toast');
    var fileIn    = document.getElementById('cover_image');
    var csrfField = document.getElementById('csrf_token_field');
    if (!form) return;

    /* ── Toast helper ──────────────────────────────────────────────── */
    var toastTimer = null;
    function showToast(kind, msg) {
      if (!toast) return;
      toast.textContent = msg;
      toast.className = 'lly-toast lly-toast--visible lly-toast--' + kind;
      clearTimeout(toastTimer);
      toastTimer = setTimeout(function () {
        toast.className = 'lly-toast lly-toast--' + kind;
      }, 3500);
    }

    /* ── Contenteditable → hidden input sync ─────────────────────────
       Mirrors the same value across every element sharing the same
       data-sync id (fields like hero_subtitle repeat in two places on
       the public page and must stay identical). ── */
    function syncField(el) {
      var syncId = el.getAttribute('data-sync');
      if (!syncId) return;
      var hidden = document.getElementById(syncId);
      if (!hidden) return;
      var text = el.textContent.replace(/\s+/g, ' ').trim();
      hidden.value = text;
      document.querySelectorAll('[data-sync="' + syncId + '"]').forEach(function (other) {
        if (other !== el) { other.textContent = text; }
      });
    }

    document.querySelectorAll('.lly-editable').forEach(function (el) {
      el.addEventListener('input', function () { syncField(el); });
      el.addEventListener('blur', function () { syncField(el); });

      /* Single-line fields (titles, tags, byline) never accept Enter. */
      if (el.hasAttribute('data-single-line')) {
        el.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') { e.preventDefault(); el.blur(); }
        });
      }

      /* Paste as plain text everywhere — no rich formatting leaks in. */
      el.addEventListener('paste', function (e) {
        e.preventDefault();
        var text = (e.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, text);
      });
    });

    /* ── Cover image click-to-replace ─────────────────────────────── */
    if (fileIn) {
      fileIn.addEventListener('change', function () {
        var f = fileIn.files[0];
        if (!f) return;
        var reader = new FileReader();
        reader.onload = function (e) {
          document.querySelectorAll('.lly-cover-img').forEach(function (img) {
            img.src = e.target.result;
          });
        };
        reader.readAsDataURL(f);
        showToast('success', document.body.dataset.activeLang === 'es'
          ? 'Nueva portada lista — pulsa "Guardar Cambios" para publicarla.'
          : 'New cover ready — click "Save Changes" to publish it.');
      });
    }

    /* ── URL popovers (Amazon link, article links) ───────────────────── */
    document.querySelectorAll('.lly-url-edit-btn').forEach(function (btn) {
      var hiddenId  = btn.getAttribute('data-hidden');
      var targets   = btn.getAttribute('data-targets');
      var hidden    = document.getElementById(hiddenId);

      var pop = document.createElement('span');
      pop.className = 'lly-url-popover lly-url-popover--hidden';
      pop.innerHTML = '<input type="url" class="lly-url-popover-input" placeholder="https://…"><button type="button" class="lly-url-apply-btn">✓</button>';
      btn.insertAdjacentElement('afterend', pop);

      var input = pop.querySelector('.lly-url-popover-input');
      var apply = pop.querySelector('.lly-url-apply-btn');

      btn.addEventListener('click', function () {
        input.value = hidden ? hidden.value : '';
        pop.classList.toggle('lly-url-popover--hidden');
        if (!pop.classList.contains('lly-url-popover--hidden')) { input.focus(); }
      });

      function applyUrl() {
        var val = input.value.trim();
        if (val === '') return;
        if (hidden) { hidden.value = val; }
        if (targets) {
          document.querySelectorAll(targets).forEach(function (a) { a.href = val; });
        }
        pop.classList.add('lly-url-popover--hidden');
        showToast('success', document.body.dataset.activeLang === 'es' ? 'Enlace actualizado.' : 'Link updated.');
      }
      apply.addEventListener('click', applyUrl);
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); applyUrl(); }
      });
    });

    /* ── Save (AJAX, no page reload) ─────────────────────────────────── */
    saveBtn.addEventListener('click', function () {
      document.querySelectorAll('.lly-editable').forEach(syncField);

      saveBtn.classList.add('lly-savebar-btn--loading');

      fetch('api/book_editor.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: new FormData(form)
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        saveBtn.classList.remove('lly-savebar-btn--loading');
        if (data.status === 'success') {
          if (data.csrf_token && csrfField) { csrfField.value = data.csrf_token; }
          if (fileIn) { fileIn.value = ''; }
          showToast('success', document.body.dataset.activeLang === 'es' ? '✅ ¡Cambios guardados con éxito!' : '✅ Changes saved successfully!');
        } else {
          showToast('error', 'Error: ' + data.message);
        }
      })
      .catch(function () {
        saveBtn.classList.remove('lly-savebar-btn--loading');
        showToast('error', document.body.dataset.activeLang === 'es' ? 'Error de red — revisa tu conexión e intenta de nuevo.' : 'Network error — check your connection and try again.');
      });
    });

    /* ── Translate Missing Fields (dirty-checking) ──────────────────── */
    var translateBtn   = document.getElementById('editor-translate-btn');
    var translateAlert = document.getElementById('translate-alert');

    function setTranslateAlert(kind, msg) {
      if (!translateAlert) return;
      translateAlert.className = 'editor-alert editor-alert--' + kind + ' lly-container-alert';
      translateAlert.textContent = msg;
    }

    function translateOne(sourceEl) {
      return fetch('api/translate.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          text: sourceEl.value.trim(),
          source_lang: 'EN',
          target_lang: 'ES',
          csrf_token: csrfField ? csrfField.value : ''
        })
      }).then(function (res) { return res.json(); });
    }

    if (translateBtn) {
      translateBtn.addEventListener('click', function () {
        document.querySelectorAll('.lly-editable').forEach(syncField);

        var sourceIds = ['testimonial_en_input', 'testimonial_author_en_input'];
        for (var i = 1; i <= 7; i++) { sourceIds.push('card_en_input_' + i); }

        var queue = sourceIds.map(function (id) { return document.getElementById(id); }).filter(function (src) {
          if (!src) return false;
          var targetId = src.id.replace('_en_input', '_es_input');
          var target = document.getElementById(targetId);
          if (!target) return false;

          var currentEn  = src.value.trim();
          if (currentEn === '') return false;

          var originalEn = (src.getAttribute('data-original-val') || '').trim();
          var esEmpty    = target.value.trim() === '';
          var enChanged  = currentEn !== originalEn;

          if (!esEmpty && !enChanged) return false; // don't clobber a human translation
          return true;
        });

        if (queue.length === 0) {
          setTranslateAlert('success', 'Nothing to translate — every Spanish field is already up to date.');
          return;
        }

        translateBtn.classList.add('editor-translate-btn--loading');
        translateAlert.className = 'editor-alert editor-alert--hidden';

        var done = 0, failed = 0;

        (function next() {
          if (queue.length === 0) {
            translateBtn.classList.remove('editor-translate-btn--loading');
            if (failed === 0) {
              setTranslateAlert('success', done + ' field(s) translated successfully.');
            } else {
              setTranslateAlert('error', done + ' translated, ' + failed + ' failed — check the browser console.');
            }
            return;
          }
          var src    = queue.shift();
          var target = document.getElementById(src.id.replace('_en_input', '_es_input'));

          translateOne(src).then(function (json) {
            if (json && json.status === 'success' && json.data && json.data.translated_text) {
              target.value = json.data.translated_text;
              document.querySelectorAll('[data-sync="' + target.id + '"]').forEach(function (el) {
                el.textContent = json.data.translated_text;
              });
              src.setAttribute('data-original-val', src.value);
              done++;
            } else {
              failed++;
              console.error('Translate failed:', json && json.message);
            }
          }).catch(function (err) {
            failed++;
            console.error('Translate network error:', err);
          }).then(next);
        }());
      });
    }
  }());
  </script>

</body>
</html>
