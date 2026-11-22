<section class="container auth">
    <aside class="auth__art" aria-hidden="true">
        <?= Poster::art(['id' => 3, 'genre_id' => 0]) ?>
        <p class="auth__slogan">Смотри. Слушай. Чувствуй.</p>
    </aside>
    <form class="auth__form" id="login-form" data-component="auth-form" data-mode="login" novalidate>
        <h1 class="display">Вход</h1>
        <p class="form-error" data-form-error role="alert"></p>

        <label class="field">
            <span class="field__label">Логин</span>
            <input class="field__input" name="login" autocomplete="username" required autofocus>
            <span class="field__error" data-error-for="login"></span>
        </label>
        <label class="field">
            <span class="field__label">Пароль</span>
            <span class="field__wrap">
                <input class="field__input" type="password" name="password" autocomplete="current-password" required>
                <button class="field__toggle" type="button" data-toggle-password aria-pressed="false">Показать</button>
            </span>
            <span class="field__error" data-error-for="password"></span>
        </label>

        <button class="btn btn--primary btn--block" type="submit">Войти</button>
        <p class="auth__alt">Нет аккаунта? <a href="/register.php">Зарегистрироваться</a></p>
    </form>
</section>
