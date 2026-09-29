<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class AutoCompleteOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically complete orders that have been delivered for more than 3 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding delivered orders older than 3 days...');

        // Find orders delivered more than 3 days ago and still in 'delivered' status
        $orders = Order::where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', Carbon::now()->subDays(3))
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            // Check if there's an active return request, if so, we probably shouldn't auto-complete
            if ($order->returnRequest && !in_array($order->returnRequest->status, ['rejected', 'completed'])) {
                continue; // Skip because return is in progress
            }

            $order->update(['status' => 'completed']);
            $count++;
        }

        $this->info("Successfully auto-completed {$count} orders.");
    }
}
