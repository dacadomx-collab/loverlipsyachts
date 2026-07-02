<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — book_editor.php
 * Standalone Book Spotlight CMS — authenticated admin page.
 * Auth: session + remember-me (same contract as strategy.php).
 * Submits to api/book_editor.php via AJAX FormData.
 */

require __DIR__ . '/api/conexion.php';
require __DIR__ . '/core/auth_check.php';

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
   Seeds curiosity cards + testimonial before the DB overrides them, so the
   editor pre-fills with real content even on empty/partial DB rows. ── */
$cardFallback = [
    1 => ['icon' => '🌴', 'en' => 'A childhood survival in the jungles of Borneo',       'es' => 'Supervivencia infantil en las selvas de Borneo'],
    2 => ['icon' => '🏹', 'en' => 'Poisoned arrows and tribal warfare',                   'es' => 'Flechas envenenadas y guerra tribal'],
    3 => ['icon' => '🎸', 'en' => 'An unexpected friendship with Eric Clapton',            'es' => 'Una amistad inesperada con Eric Clapton'],
    4 => ['icon' => '🏛️', 'en' => 'Boardrooms connected to Donald Trump',                'es' => 'Salas de juntas conectadas con Donald Trump'],
    5 => ['icon' => '🫀', 'en' => 'Open-heart surgery that should have ended it all',      'es' => 'Cirugía a corazón abierto que debió terminar con todo'],
    6 => ['icon' => '🔬', 'en' => 'Test',                                                 'es' => 'Prueba'],
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
$cards = [];  // $cards[1..7]  = ['icon' => '', 'en' => '', 'es' => '', 'img' => '']
for ($i = 1; $i <= 7; $i++) {
    $cards[$i] = [
        'icon' => htmlspecialchars($cardFallback[$i]['icon'], ENT_QUOTES, 'UTF-8'),
        'en'   => htmlspecialchars($cardFallback[$i]['en'],   ENT_QUOTES, 'UTF-8'),
        'es'   => htmlspecialchars($cardFallback[$i]['es'],   ENT_QUOTES, 'UTF-8'),
        'img'  => '',
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
            $idx = (int) $m[1];
            if ($idx >= 1 && $idx <= 7) { $cards[$idx]['img'] = $en; }
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
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Book Editor Studio" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Book Editor Studio</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="icon" type="image/png" href="assets/img/logo.png" />
  <script src="assets/js/theme-init.js"></script>
</head>

<body data-active-lang="en">

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

          <nav class="topbar-nav" role="navigation" aria-label="Dashboard Views">
            <a class="topbar-nav-link" href="index.php">
              <span data-lang="en">← Dashboard</span>
              <span data-lang="es">← Panel</span>
            </a>
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

  <main>
  <section class="section section-white" aria-labelledby="book-editor-title">
    <div class="container">

      <p class="section-label">
        <span data-lang="en">Book Spotlight Studio</span>
        <span data-lang="es">Estudio del Libro en Vivo</span>
      </p>
      <h1 class="section-title" id="book-editor-title">
        <span data-lang="en">Book Editor <em>Studio</em></span>
        <span data-lang="es">Estudio de Edición <em>del Libro</em></span>
      </h1>
      <p class="section-subtitle" data-lang="en">
        All fields are pre-filled from the live database. Edit and click "Save to Database" — changes go live on <a href="book.php" target="_blank" rel="noopener">book.php</a> immediately on next page load.
      </p>
      <p class="section-subtitle" data-lang="es">
        Todos los campos están pre-cargados desde la base de datos activa. Edita y haz clic en "Guardar en Base de Datos" — los cambios se reflejan en <a href="book.php" target="_blank" rel="noopener">book.php</a> al instante en la próxima carga.
      </p>

      <!-- Status alert -->
      <div class="editor-alert editor-alert--hidden" id="editor-alert" role="alert" aria-live="polite"></div>

      <!-- Auto-translate bar -->
      <div class="editor-translate-bar">
        <div class="editor-translate-copy">
          <span data-lang="en">Fill the Spanish fields automatically from your English text — respects manual translations already in place.</span>
          <span data-lang="es">Completa los campos en español automáticamente desde tu texto en inglés — respeta las traducciones manuales ya existentes.</span>
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
      <div class="editor-alert editor-alert--hidden" id="translate-alert" role="status" aria-live="polite"></div>

      <form id="book-editor-form" class="editor-form" method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" id="csrf_token_field" value="<?= $csrf ?>">

        <!-- ══ HERO ════════════════════════════════════════════════════ -->
        <fieldset class="editor-fieldset">
          <legend class="editor-legend">
            <span class="editor-legend-icon">🎭</span>
            <span data-lang="en">Hero Section</span>
            <span data-lang="es">Sección Principal</span>
          </legend>

          <div class="editor-row editor-row--2col">
            <div class="editor-field">
              <label class="editor-label" for="hero_title_en">
                <span data-lang="en">Main Title — English</span>
                <span data-lang="es">Título Principal — Inglés</span>
              </label>
              <input id="hero_title_en" name="hero_title_en" type="text" class="editor-input"
                     value="<?= ed('hero_title', 'en', $c) ?>" placeholder="The true story of a man…" />
            </div>
            <div class="editor-field">
              <label class="editor-label" for="hero_title_es">
                <span data-lang="en">Main Title — Spanish</span>
                <span data-lang="es">Título Principal — Español</span>
              </label>
              <input id="hero_title_es" name="hero_title_es" type="text" class="editor-input"
                     value="<?= ed('hero_title', 'es', $c) ?>" placeholder="La historia real…" />
            </div>
          </div>

          <div class="editor-row editor-row--2col">
            <div class="editor-field">
              <label class="editor-label" for="hero_sub_en">
                <span data-lang="en">Subtitle — English</span>
                <span data-lang="es">Subtítulo — Inglés</span>
              </label>
              <input id="hero_sub_en" name="hero_sub_en" type="text" class="editor-input"
                     value="<?= ed('hero_subtitle', 'en', $c) ?>" placeholder="I Died Nine Times…" />
            </div>
            <div class="editor-field">
              <label class="editor-label" for="hero_sub_es">
                <span data-lang="en">Subtitle — Spanish</span>
                <span data-lang="es">Subtítulo — Español</span>
              </label>
              <input id="hero_sub_es" name="hero_sub_es" type="text" class="editor-input"
                     value="<?= ed('hero_subtitle', 'es', $c) ?>" placeholder="Morí Nueve Veces…" />
            </div>
          </div>

          <!-- Amazon Conversion URL -->
          <div class="editor-row">
            <div class="editor-field">
              <label class="editor-label" for="amazon_link_url">
                <span data-lang="en">Amazon Buy Link (full URL or /dp/ASIN path)</span>
                <span data-lang="es">Enlace de Compra en Amazon (URL completa o ruta /dp/ASIN)</span>
              </label>
              <input id="amazon_link_url" name="amazon_link_url" type="url" class="editor-input"
                     value="<?= ed('amazon_link_url', 'en', $c) ?>"
                     placeholder="https://www.amazon.com/dp/ASIN_HERE" />
              <p class="editor-hint" data-lang="en">This URL powers all "Buy on Amazon" buttons on the public page.</p>
              <p class="editor-hint" data-lang="es">Esta URL alimenta todos los botones "Comprar en Amazon" de la página pública.</p>
            </div>
          </div>
        </fieldset>

        <!-- ══ SYNOPSIS ════════════════════════════════════════════════ -->
        <fieldset class="editor-fieldset">
          <legend class="editor-legend">
            <span class="editor-legend-icon">📖</span>
            <span data-lang="en">Book Synopsis</span>
            <span data-lang="es">Sinopsis del Libro</span>
          </legend>
          <div class="editor-row editor-row--2col">
            <div class="editor-field">
              <label class="editor-label" for="synopsis_en">
                <span data-lang="en">Synopsis — English</span>
                <span data-lang="es">Sinopsis — Inglés</span>
              </label>
              <textarea id="synopsis_en" name="synopsis_en" class="editor-textarea editor-textarea--tall" rows="7"><?= ed('synopsis', 'en', $c) ?></textarea>
            </div>
            <div class="editor-field">
              <label class="editor-label" for="synopsis_es">
                <span data-lang="en">Synopsis — Spanish</span>
                <span data-lang="es">Sinopsis — Español</span>
              </label>
              <textarea id="synopsis_es" name="synopsis_es" class="editor-textarea editor-textarea--tall" rows="7"><?= ed('synopsis', 'es', $c) ?></textarea>
            </div>
          </div>
        </fieldset>

        <!-- ══ CURIOSITY CARDS ══════════════════════════════════════════ -->
        <fieldset class="editor-fieldset">
          <legend class="editor-legend">
            <span class="editor-legend-icon">🃏</span>
            <span data-lang="en">Seven Curiosity Cards</span>
            <span data-lang="es">Siete Tarjetas de Curiosidad</span>
          </legend>
          <p class="editor-hint" data-lang="en">Each card has an emoji icon, bilingual text, and an optional custom image (JPEG/PNG → auto-converted to WebP).</p>
          <p class="editor-hint" data-lang="es">Cada tarjeta tiene un emoji, texto bilingüe y una imagen personalizada opcional (JPEG/PNG → convertida a WebP).</p>

          <div class="editor-cards-grid">
            <?php for ($ci = 1; $ci <= 7; $ci++): $card = $cards[$ci]; ?>
            <div class="editor-card-row">
              <span class="editor-card-num"><?= $ci ?></span>
              <div class="editor-card-fields">
                <input type="text" name="card_icon[<?= $ci ?>]"
                       class="editor-input editor-input--emoji"
                       value="<?= $card['icon'] ?>"
                       placeholder="🌴"
                       aria-label="Card <?= $ci ?> emoji icon" />
                <input type="text" name="card_en[<?= $ci ?>]" id="card_en_<?= $ci ?>"
                       class="editor-input js-translate-source"
                       value="<?= $card['en'] ?>"
                       data-original-val="<?= $card['en'] ?>"
                       data-target="card_es_<?= $ci ?>"
                       placeholder="English text…"
                       aria-label="Card <?= $ci ?> English text" />
                <input type="text" name="card_es[<?= $ci ?>]" id="card_es_<?= $ci ?>"
                       class="editor-input js-translate-target"
                       value="<?= $card['es'] ?>"
                       placeholder="Texto en español…"
                       aria-label="Card <?= $ci ?> Spanish text" />
              </div>
              <!-- Card image upload -->
              <div class="editor-card-img-zone">
                <?php if ($card['img'] !== ''): ?>
                <img src="<?= $card['img'] ?>" alt="Card <?= $ci ?> image" class="editor-card-img-preview" loading="lazy" />
                <?php endif; ?>
                <label class="editor-card-img-label" for="card_img_<?= $ci ?>">
                  <span data-lang="en">🖼️ Replace image</span>
                  <span data-lang="es">🖼️ Reemplazar imagen</span>
                </label>
                <input type="file" id="card_img_<?= $ci ?>" name="card_img[<?= $ci ?>]"
                       accept="image/jpeg,image/png"
                       class="editor-upload-input editor-card-img-input"
                       aria-label="Card <?= $ci ?> image upload" />
              </div>
            </div>
            <?php endfor; ?>
          </div>
        </fieldset>

        <!-- ══ TESTIMONIAL ══════════════════════════════════════════════ -->
        <fieldset class="editor-fieldset">
          <legend class="editor-legend">
            <span class="editor-legend-icon">💬</span>
            <span data-lang="en">Duane Hallock Master Testimonial</span>
            <span data-lang="es">Testimonio Maestro — Duane Hallock</span>
          </legend>
          <div class="editor-row editor-row--2col">
            <div class="editor-field">
              <label class="editor-label" for="testimonial_en">
                <span data-lang="en">Testimonial — English</span>
                <span data-lang="es">Testimonio — Inglés</span>
              </label>
              <textarea id="testimonial_en" name="testimonial_en" class="editor-textarea editor-textarea--xl js-translate-source" rows="9"
                        data-target="testimonial_es"
                        data-original-val="<?= edFallback('testimonial_quote', 'en', $c, $testimonialFallback['quote_en']) ?>"><?= edFallback('testimonial_quote', 'en', $c, $testimonialFallback['quote_en']) ?></textarea>
            </div>
            <div class="editor-field">
              <label class="editor-label" for="testimonial_es">
                <span data-lang="en">Testimonial — Spanish</span>
                <span data-lang="es">Testimonio — Español</span>
              </label>
              <textarea id="testimonial_es" name="testimonial_es" class="editor-textarea editor-textarea--xl js-translate-target" rows="9"><?= edFallback('testimonial_quote', 'es', $c, $testimonialFallback['quote_es']) ?></textarea>
            </div>
          </div>

          <div class="editor-row editor-row--2col">
            <div class="editor-field">
              <label class="editor-label" for="testimonial_author_en">
                <span data-lang="en">Author Byline — English</span>
                <span data-lang="es">Firma del Autor — Inglés</span>
              </label>
              <input id="testimonial_author_en" name="testimonial_author_en" type="text" class="editor-input js-translate-source"
                     value="<?= edFallback('testimonial_author', 'en', $c, $testimonialFallback['author_en']) ?>"
                     data-original-val="<?= edFallback('testimonial_author', 'en', $c, $testimonialFallback['author_en']) ?>"
                     data-target="testimonial_author_es"
                     placeholder="Duane Hallock, Chief Communications and Marketing Officer, Red Cross" />
            </div>
            <div class="editor-field">
              <label class="editor-label" for="testimonial_author_es">
                <span data-lang="en">Author Byline — Spanish</span>
                <span data-lang="es">Firma del Autor — Español</span>
              </label>
              <input id="testimonial_author_es" name="testimonial_author_es" type="text" class="editor-input js-translate-target"
                     value="<?= edFallback('testimonial_author', 'es', $c, $testimonialFallback['author_es']) ?>"
                     placeholder="Duane Hallock, Director de Comunicación y Marketing, Cruz Roja" />
            </div>
          </div>
        </fieldset>

        <!-- ══ SAMPLE CHAPTER ═══════════════════════════════════════════ -->
        <fieldset class="editor-fieldset editor-fieldset--highlight">
          <legend class="editor-legend">
            <span class="editor-legend-icon">📚</span>
            <span data-lang="en">Sample Chapter Preview (Lightbox)</span>
            <span data-lang="es">Vista Previa del Capítulo de Muestra (Lightbox)</span>
          </legend>
          <p class="editor-hint" data-lang="en">Feeds the "Read Sample Chapter" dialog on the public page. Plain text — paragraph breaks are preserved.</p>
          <p class="editor-hint" data-lang="es">Alimenta el diálogo "Leer Capítulo de Muestra" en la página pública. Texto plano — los saltos de párrafo se preservan.</p>
          <div class="editor-row editor-row--2col">
            <div class="editor-field">
              <label class="editor-label" for="sample_chapter_en">
                <span data-lang="en">Sample Chapter — English</span>
                <span data-lang="es">Capítulo de Muestra — Inglés</span>
              </label>
              <textarea id="sample_chapter_en" name="sample_chapter_en" class="editor-textarea editor-textarea--chapter" rows="14" placeholder="Paste your sample chapter text here…"><?= ed('sample_chapter', 'en', $c) ?></textarea>
            </div>
            <div class="editor-field">
              <label class="editor-label" for="sample_chapter_es">
                <span data-lang="en">Sample Chapter — Spanish</span>
                <span data-lang="es">Capítulo de Muestra — Español</span>
              </label>
              <textarea id="sample_chapter_es" name="sample_chapter_es" class="editor-textarea editor-textarea--chapter" rows="14" placeholder="Pega aquí el texto del capítulo de muestra…"><?= ed('sample_chapter', 'es', $c) ?></textarea>
            </div>
          </div>
        </fieldset>

        <!-- ══ BLOG SYNERGY CLUSTER ════════════════════════════════════ -->
        <fieldset class="editor-fieldset">
          <legend class="editor-legend">
            <span class="editor-legend-icon">📰</span>
            <span data-lang="en">Blog Synergy Cluster — Latest Articles</span>
            <span data-lang="es">Cluster de Sinergía del Blog — Últimos Artículos</span>
          </legend>
          <p class="editor-hint" data-lang="en">Controls the "From the Blog / Latest Articles" grid on the public page (3 article cards).</p>
          <p class="editor-hint" data-lang="es">Controla la cuadrícula "Desde el Blog / Últimos Artículos" en la página pública (3 tarjetas de artículo).</p>

          <?php for ($a = 1; $a <= 3; $a++): ?>
          <div class="editor-article-block">
            <p class="editor-article-num" data-lang="en">Article <?= $a ?></p>
            <p class="editor-article-num" data-lang="es">Artículo <?= $a ?></p>
            <div class="editor-row editor-row--2col">
              <div class="editor-field">
                <label class="editor-label" for="article_<?= $a ?>_tag_en">
                  <span data-lang="en">Category Tag — English</span>
                  <span data-lang="es">Etiqueta de Categoría — Inglés</span>
                </label>
                <input id="article_<?= $a ?>_tag_en" name="article_<?= $a ?>_tag_en" type="text" class="editor-input"
                       value="<?= ed("article_{$a}_tag", 'en', $c) ?>" placeholder="Travel Tips" />
              </div>
              <div class="editor-field">
                <label class="editor-label" for="article_<?= $a ?>_tag_es">
                  <span data-lang="en">Category Tag — Spanish</span>
                  <span data-lang="es">Etiqueta de Categoría — Español</span>
                </label>
                <input id="article_<?= $a ?>_tag_es" name="article_<?= $a ?>_tag_es" type="text" class="editor-input"
                       value="<?= ed("article_{$a}_tag", 'es', $c) ?>" placeholder="Consejos de Viaje" />
              </div>
            </div>
            <div class="editor-row editor-row--2col">
              <div class="editor-field">
                <label class="editor-label" for="article_<?= $a ?>_title_en">
                  <span data-lang="en">Title — English</span>
                  <span data-lang="es">Título — Inglés</span>
                </label>
                <input id="article_<?= $a ?>_title_en" name="article_<?= $a ?>_title_en" type="text" class="editor-input"
                       value="<?= ed("article_{$a}_title", 'en', $c) ?>" placeholder="Article title in English…" />
              </div>
              <div class="editor-field">
                <label class="editor-label" for="article_<?= $a ?>_title_es">
                  <span data-lang="en">Title — Spanish</span>
                  <span data-lang="es">Título — Español</span>
                </label>
                <input id="article_<?= $a ?>_title_es" name="article_<?= $a ?>_title_es" type="text" class="editor-input"
                       value="<?= ed("article_{$a}_title", 'es', $c) ?>" placeholder="Título del artículo en español…" />
              </div>
            </div>
            <div class="editor-row">
              <div class="editor-field">
                <label class="editor-label" for="article_<?= $a ?>_link">
                  <span data-lang="en">Article Link URL</span>
                  <span data-lang="es">URL de Enlace del Artículo</span>
                </label>
                <input id="article_<?= $a ?>_link" name="article_<?= $a ?>_link" type="url" class="editor-input"
                       value="<?= ed("article_{$a}_link", 'en', $c) ?>" placeholder="https://loverlipsyachts.com/blog/article-slug" />
              </div>
            </div>
          </div>
          <?php endfor; ?>
        </fieldset>

        <!-- ══ BOOK COVER UPLOAD ════════════════════════════════════════ -->
        <fieldset class="editor-fieldset">
          <legend class="editor-legend">
            <span class="editor-legend-icon">🖼️</span>
            <span data-lang="en">Book Cover Image</span>
            <span data-lang="es">Imagen de Portada del Libro</span>
          </legend>
          <div class="editor-upload-layout">
            <div class="editor-upload-zone" id="editor-upload-zone">
              <input type="file" id="cover_image" name="cover_image"
                     accept="image/jpeg,image/png"
                     class="editor-upload-input"
                     aria-describedby="upload-hint" />
              <label for="cover_image" class="editor-upload-label">
                <span class="editor-upload-icon">⬆️</span>
                <span class="editor-upload-text" data-lang="en">Click to upload a new cover<br><small>JPEG or PNG · max 10 MB · auto-converted to WebP at 80%</small></span>
                <span class="editor-upload-text" data-lang="es">Haz clic para subir una nueva portada<br><small>JPEG o PNG · máx 10 MB · convertido a WebP al 80%</small></span>
              </label>
              <p class="editor-upload-selected" id="upload-selected-name" data-lang="en">No file selected</p>
            </div>
            <div class="editor-cover-preview">
              <p class="editor-cover-preview-label" data-lang="en">Current Cover</p>
              <p class="editor-cover-preview-label" data-lang="es">Portada Actual</p>
              <img src="<?= htmlspecialchars($currentCover, ENT_QUOTES, 'UTF-8') ?>"
                   alt="Current book cover"
                   class="editor-cover-thumb"
                   id="editor-cover-thumb"
                   loading="lazy" />
            </div>
          </div>
          <p class="editor-hint" id="upload-hint" data-lang="en">
            EXIF metadata is stripped automatically. The new cover appears on the public page immediately after saving.
          </p>
          <p class="editor-hint" data-lang="es">
            Los metadatos EXIF se eliminan automáticamente. La nueva portada aparece en la página pública inmediatamente al guardar.
          </p>
        </fieldset>

        <!-- ══ PUBLISH BAR ══════════════════════════════════════════════ -->
        <div class="editor-publish-bar">
          <span class="editor-publish-note" data-lang="en">Changes are saved to the database and reflected on book.php immediately.</span>
          <span class="editor-publish-note" data-lang="es">Los cambios se guardan en la base de datos y se reflejan en book.php de inmediato.</span>
          <button type="submit" class="editor-publish-btn" id="editor-publish-btn">
            <span class="editor-publish-btn-idle">
              <span data-lang="en">💾 Save to Database</span>
              <span data-lang="es">💾 Guardar en Base de Datos</span>
            </span>
            <span class="editor-publish-btn-loading" aria-hidden="true">
              <span data-lang="en">Saving…</span>
              <span data-lang="es">Guardando…</span>
            </span>
          </button>
        </div>

      </form>

    </div>
  </section>
  </main>

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

  <button id="back-to-top" class="back-to-top" aria-label="Back to top" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <script src="assets/js/main.js" defer></script>

  <script>
  (function () {
    var form    = document.getElementById('book-editor-form');
    var btn     = document.getElementById('editor-publish-btn');
    var alertEl = document.getElementById('editor-alert');
    var fileIn  = document.getElementById('cover_image');
    var thumb   = document.getElementById('editor-cover-thumb');
    var selName = document.getElementById('upload-selected-name');

    if (!form) return;

    /* Live cover preview */
    if (fileIn) {
      fileIn.addEventListener('change', function () {
        var f = fileIn.files[0];
        if (!f) return;
        if (selName) { selName.textContent = f.name; }
        var reader = new FileReader();
        reader.onload = function (e) { if (thumb) { thumb.src = e.target.result; } };
        reader.readAsDataURL(f);
      });
    }

    /* Card image previews */
    document.querySelectorAll('.editor-card-img-input').forEach(function (inp) {
      inp.addEventListener('change', function () {
        var f = inp.files[0];
        if (!f) return;
        var preview = inp.closest('.editor-card-img-zone').querySelector('.editor-card-img-preview');
        var reader = new FileReader();
        reader.onload = function (e) {
          if (preview) {
            preview.src = e.target.result;
          } else {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'editor-card-img-preview';
            img.alt = 'Card preview';
            inp.closest('.editor-card-img-zone').prepend(img);
          }
        };
        reader.readAsDataURL(f);
      });
    });

    /* AJAX submit */
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      btn.classList.add('editor-publish-btn--loading');
      if (alertEl) { alertEl.className = 'editor-alert editor-alert--hidden'; }

      fetch('api/book_editor.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: new FormData(form)
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        btn.classList.remove('editor-publish-btn--loading');
        if (data.status === 'success') {
          alert(data.message);
          window.location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(function () {
        btn.classList.remove('editor-publish-btn--loading');
        alert('Network error — check your connection and try again.');
      });
    });

    /* ── Translate Missing Fields (dirty-checking) ──────────────── */
    var translateBtn   = document.getElementById('editor-translate-btn');
    var translateAlert = document.getElementById('translate-alert');
    var csrfField       = document.getElementById('csrf_token_field');

    function setTranslateAlert(kind, msg) {
      if (!translateAlert) return;
      translateAlert.className = 'editor-alert editor-alert--' + kind;
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
        var sources = Array.prototype.slice.call(document.querySelectorAll('.js-translate-source'));

        /* Dirty-checking: a field qualifies for translation when
           (Spanish is empty) OR (English changed since page load) —
           and never when Spanish already has manual text with English
           unchanged (rule 3 — don't clobber a human translation). */
        var queue = sources.filter(function (src) {
          var target = document.getElementById(src.getAttribute('data-target') || '');
          if (!target) return false;

          var currentEn  = src.value.trim();
          if (currentEn === '') return false;

          var originalEn = (src.getAttribute('data-original-val') || '').trim();
          var esEmpty    = target.value.trim() === '';
          var enChanged  = currentEn !== originalEn;

          if (!esEmpty && !enChanged) return false; // rule 3 — leave intact
          return true;                              // rule 1 or rule 2
        });

        if (queue.length === 0) {
          setTranslateAlert('success', 'Nothing to translate — every Spanish field is already up to date.');
          return;
        }

        translateBtn.classList.add('editor-translate-btn--loading');
        translateAlert.className = 'editor-alert editor-alert--hidden';

        var done = 0;
        var failed = 0;

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
          var target = document.getElementById(src.getAttribute('data-target'));

          translateOne(src).then(function (json) {
            if (json && json.status === 'success' && json.data && json.data.translated_text) {
              target.value = json.data.translated_text;
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
