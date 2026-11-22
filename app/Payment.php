<?php
// Способы оплаты и валидация реквизитов.
final class Payment
{
    public const METHODS = [
        'card'     => ['label' => 'Банковская карта', 'icon' => '💳', 'hint' => 'Visa, Mastercard, МИР'],
        'sbp'      => ['label' => 'СБП',              'icon' => '📱', 'hint' => 'По номеру телефона'],
        'yookassa' => ['label' => 'ЮKassa',            'icon' => '🌐', 'hint' => 'Все способы'],
    ];

    public static function banks(): array
    {
        return [
            'Сбербанк', 'Т-Банк', 'ВТБ', 'Альфа-Банк', 'Райффайзен',
            'Газпромбанк', 'Открытие', 'Совкомбанк', 'Другой',
        ];
    }

    public static function validMethod(string $m): bool
    {
        return isset(self::METHODS[$m]);
    }

    /** @return array{error: ?string, field: ?string} */
    public static function validateDetails(string $method, array $in): array
    {
        if (!self::validMethod($method)) {
            return ['error' => 'Выберите способ оплаты.', 'field' => 'method'];
        }

        if ($method === 'card') {
            $number = preg_replace('/\D/', '', (string)($in['card_number'] ?? ''));
            if (strlen($number) < 13 || strlen($number) > 19 || !self::luhn($number)) {
                return ['error' => 'Введите корректный номер карты.', 'field' => 'card_number'];
            }
            $expiry = trim((string)($in['card_expiry'] ?? ''));
            if (!preg_match('#^(\d{2})/(\d{2})$#', $expiry, $m)) {
                return ['error' => 'Срок действия в формате ММ/ГГ.', 'field' => 'card_expiry'];
            }
            $month = (int)$m[1];
            $year  = 2000 + (int)$m[2];
            if ($month < 1 || $month > 12) {
                return ['error' => 'Неверный месяц.', 'field' => 'card_expiry'];
            }
            $lastDay = mktime(0, 0, 0, $month + 1, 1, $year) - 1;
            if ($lastDay < time()) {
                return ['error' => 'Срок действия карты истёк.', 'field' => 'card_expiry'];
            }
            if (!preg_match('/^\d{3,4}$/', (string)($in['card_cvv'] ?? ''))) {
                return ['error' => 'CVV — 3 или 4 цифры.', 'field' => 'card_cvv'];
            }
        } elseif ($method === 'sbp') {
            $phone = preg_replace('/\D/', '', (string)($in['sbp_phone'] ?? ''));
            if (strlen($phone) < 10 || strlen($phone) > 11) {
                return ['error' => 'Введите корректный номер телефона.', 'field' => 'sbp_phone'];
            }
            if (!in_array(trim((string)($in['sbp_bank'] ?? '')), self::banks(), true)) {
                return ['error' => 'Выберите банк.', 'field' => 'sbp_bank'];
            }
        }
        // yookassa: без реквизитов
        return ['error' => null, 'field' => null];
    }

    private static function luhn(string $n): bool
    {
        $sum = 0;
        $alt = false;
        for ($i = strlen($n) - 1; $i >= 0; $i--) {
            $d = (int)$n[$i];
            if ($alt) { $d *= 2; if ($d > 9) $d -= 9; }
            $sum += $d;
            $alt = !$alt;
        }
        return $sum % 10 === 0;
    }
}