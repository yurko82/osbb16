<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Генератор платіжного QR-коду за стандартом Національного банку України (НБУ)
 * Сумісний із застосунками: Monobank, Приват24, ПУМБ, Sense, Ощадбанк тощо.
 */
class NbuQrService {

    /**
     * Створює текст за офіційним форматом НБУ (BCD 002)
     * 
     * @param string $recipientName Назва ОСББ
     * @param string $edrpou Код ЄДРПОУ
     * @param string $iban Рахунок IBAN (UA...)
     * @param float $amount Сума до сплати
     * @param string $purpose Призначення платежу (Особовий рахунок, ПІБ, адреса)
     * @return string
     */
    public static function buildPayload(
        string $recipientName,
        string $edrpou,
        string $iban,
        float $amount,
        string $purpose
    ): string {
        $iban = strtoupper(str_replace(' ', '', $iban));
        $edrpou = trim($edrpou);
        $amountFormatted = number_format($amount, 2, '.', '');

        // Формат НБУ для платежів за реквізитами:
        // BCD
        // 002
        // 1
        // UAH
        // Amount
        // Name
        // IBAN
        // EDRPOU
        // Purpose
        $lines = [
            'BCD',
            '002',
            '1',
            'UAH',
            $amountFormatted,
            mb_substr(trim($recipientName), 0, 70),
            $iban,
            $edrpou,
            mb_substr(trim($purpose), 0, 140),
        ];

        return implode("\n", $lines);
    }

    /**
     * Повертає URL картинки QR-коду через швидкий надійний QR-сервіс (SVG/PNG)
     */
    public static function getQrImageUrl(string $payload, int $size = 280): string {
        $encoded = urlencode($payload);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded}&charset-source=UTF-8&ecc=M";
    }

    /**
     * Генерує QR-код для конкретного рахунку ОСББ
     */
    public static function generateForBill(array $bill, array $apartment, array $osbbConfig): array {
        $amount = (float)$bill['total_amount'] - (float)$bill['paid_amount'];
        if ($amount < 0) {
            $amount = 0.00;
        }

        $purpose = sprintf(
            'Оплата ЖКП, кв. %d, о/р %s, рах. %s',
            $apartment['number'],
            $apartment['account_number'] ?? ('OSBB-' . str_pad((string)$apartment['number'], 4, '0', STR_PAD_LEFT)),
            $bill['bill_number'] ?? 'без номера'
        );

        $payload = self::buildPayload(
            $osbbConfig['name'] ?? 'ОСББ',
            $osbbConfig['edrpou'] ?? '',
            $osbbConfig['iban'] ?? '',
            $amount,
            $purpose
        );

        return [
            'amount' => $amount,
            'purpose' => $purpose,
            'payload' => $payload,
            'qr_url' => self::getQrImageUrl($payload),
            'iban' => $osbbConfig['iban'] ?? '',
            'edrpou' => $osbbConfig['edrpou'] ?? '',
            'recipient' => $osbbConfig['name'] ?? '',
        ];
    }
}
