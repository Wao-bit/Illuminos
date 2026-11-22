<?php
// Форма настроек профиля. Параметр: $profile.
?>
<form class="settings-form" id="settings-form" data-component="settings-form" novalidate>
    <p class="form-error" data-form-error role="alert"></p>

    <h3 class="section-label">Личные данные</h3>
    <div class="auth__pair">
        <label class="field">
            <span class="field__label">Имя *</span>
            <input class="field__input" name="first_name" value="<?= e($profile['first_name']) ?>" required>
        </label>
        <label class="field">
            <span class="field__label">Фамилия *</span>
            <input class="field__input" name="last_name" value="<?= e($profile['last_name']) ?>" required>
        </label>
    </div>
    <label class="field">
        <span class="field__label">Отчество</span>
        <input class="field__input" name="patronymic" value="<?= e($profile['patronymic'] ?? '') ?>">
    </label>

    <h3 class="section-label" style="margin-top:1.5rem">Учётные данные</h3>
    <label class="field">
        <span class="field__label">Логин *</span>
        <input class="field__input" name="login" value="<?= e($profile['login']) ?>" autocomplete="username" required>
    </label>
    <label class="field">
        <span class="field__label">E-mail *</span>
        <input class="field__input" type="email" name="email" value="<?= e($profile['email']) ?>" autocomplete="email" required>
    </label>

    <h3 class="section-label" style="margin-top:1.5rem">Смена пароля</h3>
    <p class="muted" style="margin-bottom:.75rem;font-size:.9rem">Заполните, только если хотите сменить пароль.</p>
    <label class="field">
        <span class="field__label">Текущий пароль</span>
        <input class="field__input" type="password" name="current_password" autocomplete="current-password">
    </label>
    <div class="auth__pair">
        <label class="field">
            <span class="field__label">Новый пароль</span>
            <input class="field__input" type="password" name="new_password" autocomplete="new-password">
        </label>
        <label class="field">
            <span class="field__label">Повторите новый</span>
            <input class="field__input" type="password" name="new_password2" autocomplete="new-password">
        </label>
    </div>

    <button class="btn btn--primary" type="submit">Сохранить изменения</button>
</form>