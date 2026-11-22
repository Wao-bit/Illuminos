<section class="container auth">
    <aside class="auth__art" aria-hidden="true">
        <?= Poster::art(['id' => 1, 'genre_id' => 1]) ?>
        <p class="auth__slogan">Смотри. Слушай. Чувствуй.</p>
    </aside>
    <form class="auth__form" id="register-form" data-component="auth-form" data-mode="register" novalidate>
        <h1 class="display">Регистрация</h1>
        <p class="form-error" data-form-error role="alert"></p>

        <div class="auth__pair">
            <label class="field">
                <span class="field__label">Имя *</span>
                <input class="field__input" name="first_name" autocomplete="given-name" required>
                <span class="field__error" data-error-for="first_name"></span>
            </label>
            <label class="field">
                <span class="field__label">Фамилия *</span>
                <input class="field__input" name="last_name" autocomplete="family-name" required>
                <span class="field__error" data-error-for="last_name"></span>
            </label>
        </div>
        <label class="field">
            <span class="field__label">Отчество</span>
            <input class="field__input" name="patronymic" autocomplete="additional-name">
        </label>
        <label class="field">
            <span class="field__label">Логин *</span>
            <input class="field__input" name="login" autocomplete="username" required>
            <span class="field__error" data-error-for="login"></span>
            <span class="field__hint" data-login-hint aria-live="polite"></span>
        </label>
        <label class="field">
            <span class="field__label">E-mail *</span>
            <input class="field__input" type="email" name="email" autocomplete="email" required>
            <span class="field__error" data-error-for="email"></span>
        </label>
        <label class="field">
            <span class="field__label">Пароль (мин. 6 символов) *</span>
            <span class="field__wrap">
                <input class="field__input" type="password" name="password" autocomplete="new-password" required>
                <button class="field__toggle" type="button" data-toggle-password aria-pressed="false">Показать</button>
            </span>
            <span class="strength" data-strength data-level="0" aria-hidden="true"><i></i><i></i><i></i><i></i><b></b></span>
            <span class="field__error" data-error-for="password"></span>
        </label>
        <label class="field">
            <span class="field__label">Повторите пароль *</span>
            <span class="field__wrap">
                <input class="field__input" type="password" name="password2" autocomplete="new-password" required>
                <button class="field__toggle" type="button" data-toggle-password aria-pressed="false">Показать</button>
            </span>
            <span class="field__error" data-error-for="password2"></span>
        </label>
        <div class="field">
            <label class="check">
                <input type="checkbox" name="agree">
                <span>Согласен с правилами регистрации *</span>
            </label>
            <span class="field__error" data-error-for="agree"></span>
        </div>

        <button class="btn btn--primary btn--block" type="submit">Зарегистрироваться</button>
        <p class="auth__alt">Уже есть аккаунт? <a href="/login.php">Войти</a></p>
    </form>
</section>
