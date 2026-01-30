<?php

namespace App\Jobs;

use App\Mail\AlertMail;
use App\Models\Alert;
use App\Models\Customer;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAlertNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Alert $alert,
    ) {}

    public function handle(): void
    {
        $recipients = $this->getRecipients();

        // Send email notifications
        if (!empty($recipients['emails'])) {
            $this->sendEmailNotifications($recipients['emails']);
        }

        // Send Telegram notifications
        if (!empty($recipients['telegram'])) {
            $this->sendTelegramNotifications($recipients['telegram']);
        }
    }

    protected function getRecipients(): array
    {
        $emails = [];
        $telegram = [];

        // Determine who should receive this alert based on type
        $alertType = $this->alert->type;
        $sourceType = $this->alert->source_type;
        $sourceId = $this->alert->source_id;

        // Admin-only alerts
        $adminOnlyTypes = [
            'carrier_down',
            'carrier_recovered',
            'high_failure_rate',
            'security_ip_blocked',
            'security_flood_detected',
            'system_error',
        ];

        // Alerts that also notify the affected customer
        $customerNotifyTypes = [
            'cps_exceeded',
            'channels_exceeded',
            'minutes_warning',
            'minutes_exhausted',
        ];

        // Always notify global admins for critical/warning alerts
        $globalAdminEmails = SystemSetting::getValue('notifications', 'admin_emails', '');
        if ($globalAdminEmails) {
            $emails = array_merge($emails, array_map('trim', explode(',', $globalAdminEmails)));
        }

        // Get admin users with email
        $adminUsers = User::where('role', 'admin')
            ->where('active', true)
            ->whereNotNull('email')
            ->pluck('email')
            ->toArray();
        $emails = array_merge($emails, $adminUsers);

        // Global admin Telegram chat IDs
        $globalAdminTelegram = SystemSetting::getValue('notifications', 'admin_telegram_chat_ids', '');
        if ($globalAdminTelegram) {
            $telegram = array_merge($telegram, array_map('trim', explode(',', $globalAdminTelegram)));
        }

        // If it's a customer-related alert, also notify the customer
        if (in_array($alertType, $customerNotifyTypes) && $sourceType === 'customer' && $sourceId) {
            $customer = Customer::find($sourceId);
            if ($customer) {
                // Email notification preference
                if ($customer->alert_email && $this->shouldNotifyCustomer($customer, $alertType)) {
                    $emails[] = $customer->alert_email;
                }

                // Telegram notification preference
                if ($customer->alert_telegram_chat_id && $this->shouldNotifyCustomer($customer, $alertType)) {
                    $telegram[] = $customer->alert_telegram_chat_id;
                }
            }
        }

        return [
            'emails' => array_unique(array_filter($emails)),
            'telegram' => array_unique(array_filter($telegram)),
        ];
    }

    protected function shouldNotifyCustomer(Customer $customer, string $alertType): bool
    {
        // Check customer notification preferences
        if (in_array($alertType, ['minutes_warning', 'minutes_exhausted'])) {
            return $customer->notify_low_balance;
        }

        if (in_array($alertType, ['channels_exceeded', 'cps_exceeded'])) {
            return $customer->notify_channels_warning;
        }

        return true;
    }

    protected function sendEmailNotifications(array $emails): void
    {
        try {
            foreach ($emails as $email) {
                Mail::to($email)->send(new AlertMail($this->alert));
            }

            $this->alert->update(['notified_email' => true]);
            Log::info("Alert email sent", [
                'alert_id' => $this->alert->id,
                'recipients' => $emails,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send alert email", [
                'alert_id' => $this->alert->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function sendTelegramNotifications(array $chatIds): void
    {
        $botToken = SystemSetting::getValue('notifications', 'telegram_bot_token', '');
        if (!$botToken) {
            Log::warning("Telegram bot token not configured, skipping notification");
            return;
        }

        $message = $this->formatTelegramMessage();

        foreach ($chatIds as $chatId) {
            try {
                $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [[
                            [
                                'text' => '📊 Ver en Panel',
                                'url' => config('app.url') . '/alerts',
                            ],
                        ]],
                    ]),
                ]);

                if (!$response->successful()) {
                    Log::error("Telegram API error", [
                        'chat_id' => $chatId,
                        'response' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send Telegram notification", [
                    'chat_id' => $chatId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->alert->update(['notified_telegram' => true]);
    }

    protected function formatTelegramMessage(): string
    {
        $severityEmoji = match ($this->alert->severity) {
            'critical' => '🚨',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            default => '📢',
        };

        $typeLabel = str_replace('_', ' ', ucwords($this->alert->type, '_'));

        $message = "{$severityEmoji} *{$this->alert->title}*\n\n";
        $message .= "📋 *Tipo:* {$typeLabel}\n";
        $message .= "⏰ *Hora:* {$this->alert->created_at->format('d/m/Y H:i:s')}\n";

        if ($this->alert->source_name) {
            $message .= "🎯 *Origen:* {$this->alert->source_name}\n";
        }

        $message .= "\n{$this->alert->message}";

        if ($this->alert->metadata) {
            $message .= "\n\n📎 *Detalles:*\n";
            foreach ($this->alert->metadata as $key => $value) {
                $displayValue = is_array($value) ? json_encode($value) : $value;
                $message .= "• {$key}: `{$displayValue}`\n";
            }
        }

        return $message;
    }
}
