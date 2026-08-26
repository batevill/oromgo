<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Notification;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBookingRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 1-day advance booking reminders to customers and dacha owners via Telegram & In-App';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram)
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $this->info("Scanning confirmed bookings for start_date = {$tomorrow}...");

        $bookings = Booking::with(['dacha.user', 'user'])
            ->where('status', 'confirmed')
            ->whereDate('start_date', $tomorrow)
            ->get();

        if ($bookings->isEmpty()) {
            $this->info("No bookings scheduled for tomorrow ({$tomorrow}).");
            return self::SUCCESS;
        }

        $sentCount = 0;

        foreach ($bookings as $booking) {
            // Check if reminder was already created today to prevent duplicate runs
            $alreadyReminded = Notification::where('booking_id', $booking->id)
                ->where('type', 'booking_reminder')
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if ($alreadyReminded) {
                $this->line("Booking #{$booking->id} already reminded today. Skipping.");
                continue;
            }

            // 1. Notify Customer (In-App)
            if ($booking->user) {
                Notification::create([
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->id,
                    'type' => 'booking_reminder',
                    'title' => '⏰ Eslatma: Ertaga dam olish kuni!',
                    'message' => "Ertaga \"{$booking->dacha->name}\" dachasiga kirish kuningiz boshlanadi. Maroqli hordiq tilaymiz!",
                    'data' => [
                        'booking_id' => $booking->id,
                        'dacha_name' => $booking->dacha->name,
                        'start_date' => $booking->start_date->format('Y-m-d'),
                        'end_date' => $booking->end_date->format('Y-m-d'),
                        'recipient' => 'customer',
                    ],
                ]);

                // Customer Telegram Reminder
                $telegram->sendBookingReminder($booking, 'customer');
            }

            // 2. Notify Owner (In-App)
            $owner = $booking->dacha->user ?? null;
            if ($owner) {
                Notification::create([
                    'user_id' => $owner->id,
                    'booking_id' => $booking->id,
                    'type' => 'booking_reminder',
                    'title' => '⏰ Eslatma: Ertaga mehmonlar keladi!',
                    'message' => "Ertaga \"{$booking->dacha->name}\" dachangizga {$booking->user->name} tashrif buyuradi ({$booking->guests_count} kishi).",
                    'data' => [
                        'booking_id' => $booking->id,
                        'dacha_name' => $booking->dacha->name,
                        'guest_name' => $booking->user->name ?? 'Mijoz',
                        'guest_phone' => $booking->user->phone ?? '-',
                        'start_date' => $booking->start_date->format('Y-m-d'),
                        'end_date' => $booking->end_date->format('Y-m-d'),
                        'recipient' => 'owner',
                    ],
                ]);

                // Owner Telegram Reminder
                $telegram->sendBookingReminder($booking, 'owner');
            }

            $sentCount++;
        }

        $this->info("Successfully processed reminders for {$sentCount} bookings.");
        return self::SUCCESS;
    }
}
