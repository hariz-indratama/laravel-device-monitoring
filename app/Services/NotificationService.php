<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceAlert;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendAlert(DeviceAlert $alert): void
    {
        $device = $alert->device;
        $outlet = $device?->outlet;
        $user   = $outlet?->user ?? $device?->user;

        $outletName = $outlet?->name ?? 'Unassigned';

        $message = "🚨 *{$alert->severity->label()} Alert*\n\n";
        $message .= "Device: {$device?->name}\n";
        $message .= "Lokasi: {$outletName}\n";
        $message .= "Tipe: {$alert->type->label()}\n";
        $message .= "Pesan: {$alert->message}";

        if (config('services.telegram.bot_token') && config('services.telegram.chat_id')) {
            $this->sendTelegram($message);
        }

        Log::info('Alert notification sent', [
            'alert_id'   => $alert->id,
            'device_id'  => $device?->id,
            'user_id'    => $user?->id,
            'severity'   => $alert->severity->value,
        ]);
    }

    private function sendTelegram(string $message): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        try {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text'    => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Throwable $e) {
            Log::error('Telegram send failed: ' . $e->getMessage());
        }
    }

    public function sendOfflineNotification(Device $device): void
    {
        $outlet = $device->outlet;
        $outletName = $outlet?->name ?? 'Unassigned';

        $message = "⚠️ *Device Offline*\n\n";
        $message .= "Device: {$device->name}\n";
        $message .= "Lokasi: {$outletName}\n";
        $message .= "Offline sejak: {$device->updated_at->diffForHumans()}";

        if (config('services.telegram.bot_token')) {
            $this->sendTelegram($message);
        }
    }
}
