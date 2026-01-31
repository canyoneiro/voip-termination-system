<?php

namespace App\Console\Commands;

use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class NotifyTestTelegram extends Command
{
    protected $signature = 'notify:test-telegram {chat_id?}';
    protected $description = 'Send a test notification to Telegram';

    public function handle(): int
    {
        $botToken = SystemSetting::getValue('notifications', 'telegram_bot_token', '');

        if (!$botToken) {
            $this->error('Telegram bot token not configured.');
            $this->info('Set it with: php artisan tinker');
            $this->info('SystemSetting::setValue("notifications", "telegram_bot_token", "YOUR_TOKEN");');
            return 1;
        }

        $chatId = $this->argument('chat_id')
            ?? SystemSetting::getValue('notifications', 'admin_telegram_chat_ids', '');

        if (!$chatId) {
            $this->error('No chat_id provided and no admin chat IDs configured.');
            return 1;
        }

        // Take first chat_id if multiple
        $chatId = explode(',', $chatId)[0];

        $message = "✅ *VoIP Panel - Test de Notificacion*\n\n"
            . "🔔 Las notificaciones de Telegram funcionan correctamente.\n\n"
            . "📍 *Servidor:* " . config('app.url') . "\n"
            . "🕐 *Fecha:* " . now()->format('d/m/Y H:i:s') . "\n\n"
            . "_Este es un mensaje de prueba._";

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful() && $response->json('ok')) {
                $this->info("✓ Test message sent successfully to chat_id: {$chatId}");
                return 0;
            }

            $this->error("API Error: " . $response->json('description', 'Unknown error'));
            return 1;

        } catch (\Exception $e) {
            $this->error("Failed to send message: " . $e->getMessage());
            return 1;
        }
    }
}
