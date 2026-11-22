<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F3EEE3">
    <title><?= e($title) ?></title>
    <script>document.documentElement.classList.add('js');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Playfair+Display:ital,wght@0,700;0,900;1,700&display=swap">
    <link rel="stylesheet" href="<?= asset('css/tokens.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages.css') ?>">
    <script type="module" src="<?= asset('js/main.js') ?>"></script>
</head>
<body>
<a class="skip" href="#content">Перейти к содержимому</a>

<header class="masthead">
    <?php if ($ticker): ?>
    <div class="ticker" aria-hidden="true">
        <div class="ticker__track">
            <?php for ($i = 0; $i < 2; $i++): foreach ($ticker as $t): ?>
                <span class="ticker__item"><?= e($t['title']) ?> <i>★ <?= e($t['rating']) ?></i></span>
            <?php endforeach; endfor; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="masthead__bar container">
        <a class="logo" href="/index.php" aria-label="Illuminos — на главную">
            <svg class="logo__mark" viewBox="0 0 32 32" width="28" height="28" aria-hidden="true"><circle cx="16" cy="16" r="13" fill="none" stroke="currentColor" stroke-width="2.5"/><path d="M13 10.5l9 5.5-9 5.5z" fill="currentColor"/></svg>
            <span class="logo__word">Illuminos</span>
        </a>

        <button class="burger" type="button" data-nav-toggle aria-expanded="false" aria-controls="main-nav">
            <span class="visually-hidden">Меню</span><i></i><i></i><i></i>
        </button>

        <nav class="nav" id="main-nav" data-component="nav" aria-label="Основная навигация">
            <a class="nav__link" href="/index.php"<?= $active === 'home' ? ' aria-current="page"' : '' ?>>Главная</a>
            <a class="nav__link" href="/films.php"<?= $active === 'film' ? ' aria-current="page"' : '' ?>>Фильмы</a>
            <a class="nav__link" href="/series.php"<?= $active === 'serial' ? ' aria-current="page"' : '' ?>>Сериалы</a>
            <details class="menu">
                <summary class="nav__link"<?= $active === 'genre' ? ' aria-current="page"' : '' ?>>Категории</summary>
                <div class="menu__panel">
                    <?php foreach ($genresMenu as $g): ?>
                        <a href="/index.php?genre=<?= (int)$g['id'] ?>#catalog"><?= e($g['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </details>
            <a class="nav__link" href="/payment.php"<?= $active === 'pricing' ? ' aria-current="page"' : '' ?>>Тарифы</a>
        </nav>

        <div class="masthead__tools">
            <button class="icon-btn" type="button" data-search-open aria-label="Открыть поиск" aria-expanded="false" aria-controls="searchbar">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
            </button>
            <?php if ($user): ?>
                <a class="btn btn--ghost btn--sm" href="/cabinet.php"><?= e($user['first_name']) ?> <small>(<?= e($roleLabel) ?>)</small></a>
                <a class="link-quiet" href="/api/auth.php?action=logout">Выход</a>
            <?php else: ?>
                <a class="link-quiet" href="/login.php">Вход</a>
                <a class="btn btn--primary btn--sm" href="/register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Панель поиска: раскрывается под шапкой -->
    <div class="searchbar" id="searchbar" data-component="search" hidden>
        <div class="container">
            <form class="searchbar__form" action="/index.php#catalog" method="get" role="search">
                <label class="visually-hidden" for="search-input">Поиск по фильмам и сериалам</label>
                <input class="searchbar__input" id="search-input" type="search" name="q" autocomplete="off"
                       placeholder="Поиск по фильмам и сериалам…" value="<?= e($_GET['q'] ?? '') ?>"
                       role="combobox" aria-expanded="false" aria-controls="search-results" aria-autocomplete="list">
                <button class="btn btn--primary" type="submit">Найти</button>
                <button class="btn btn--ghost" type="button" data-search-close>Закрыть</button>
            </form>
            <ul class="searchbar__results" id="search-results" role="listbox" hidden></ul>
        </div>
    </div>
</header>

<main id="content">
    <?php if ($notice): ?>
        <div class="container"><div class="notice notice--<?= e($notice['kind']) ?>" role="status">
            <span><?= e($notice['text']) ?></span>
            <?php if ($notice['kind'] === 'success'): ?><a href="/cabinet.php">Открыть кабинет</a><?php endif; ?>
        </div></div>
    <?php endif; ?>
    <?= $content ?>
</main>

<footer class="footer">
    <div class="container footer__grid">
        <div>
            <p class="footer__brand">Illuminos</p>
            <p class="footer__text">Фильмы и сериалы онлайн: подписка, избранное и история просмотров в одном месте.</p>
        </div>
        <nav aria-label="Разделы">
            <p class="footer__head">Смотреть</p>
            <a href="/index.php?type=film#catalog">Фильмы</a>
            <a href="/index.php?type=serial#catalog">Сериалы</a>
            <a href="/payment.php">Тарифы</a>
        </nav>
        <nav aria-label="Аккаунт">
            <p class="footer__head">Аккаунт</p>
            <?php if ($user): ?>
                <a href="/cabinet.php">Личный кабинет</a>
            <?php else: ?>
                <a href="/login.php">Вход</a>
                <a href="/register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="container footer__bar">
        <span>© <?= date('Y') ?> Illuminos — онлайн-кинотеатр</span>
        <span>Смотри. Слушай. Чувствуй.</span>
    </div>
</footer>

<div class="toasts" id="toasts" aria-live="polite"></div>
</body>
</html>
