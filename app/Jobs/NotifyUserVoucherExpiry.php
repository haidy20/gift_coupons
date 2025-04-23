<?php

namespace App\Jobs;

use App\Models\UserAccount;
use App\Models\UsersAccount;
use App\Models\Voucher;
use App\Notifications\GeneralNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotifyUserVoucherExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $voucher;
    protected $expiryDate;

    /**
     * Create a new job instance.
     */
    public function __construct(UsersAccount $user, Voucher $voucher, $expiryDate)
    {
        $this->user = $user;
        $this->voucher = $voucher;
        $this->expiryDate = $expiryDate;
    }

    public function handle(): void
    {
        // $remainingDays = Carbon::now()->diffInDays(Carbon::parse($this->expiryDate), false);
        // $remainingHours = Carbon::now()->diffInHours(Carbon::parse($this->expiryDate), false);
        $remainingDays = Carbon::now()->diffInDays(Carbon::parse($this->expiryDate), false);

        // Log::info('Voucher Expiry Debug:', [
        //     'expiry_date' => $this->expiryDate,
        //     'remaining_days' => $remainingDays,
        //     'now' => Carbon::now(),
        // ]);


        // إرسال إشعار للمستخدم
        $this->user->notify(new GeneralNotification(
            'Voucher Expiry Reminder',
            "Dear {$this->user->username}, your voucher '{$this->voucher->name}' will expire in {$remainingDays} days. Make sure to use it before {$this->expiryDate}!"
        ));
    }
}
