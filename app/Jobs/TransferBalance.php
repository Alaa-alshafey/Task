<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaction;
class TransferBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

        private $fromclientId;
        private $toclientId;
        private $product_id;
        private $amount;
        private $balance_before;
        private $balance_after;
        private $details;

 
     public function __construct($fromclientId,$toclientId,$product_id, $amount,$balance_before,$balance_after,$details)
     {
        $this->fromclientId = $fromclientId;
        $this->toclientId = $toclientId;
        $this->product_id = $product_id;
        $this->amount = $amount;
        $this->balance_before = $balance_before;
        $this->balance_after = $balance_after;
        $this->details = $details;
         
     }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
            \Log::debug('TransferBalance job handling');

        $transaction = new Transaction();
        $transaction->from_client_id = $this->fromclientId;
        $transaction->to_client_id = $this->toclientId;
        $transaction->product_id = $this->product_id;
        $transaction->amount = $this->amount;
        $transaction->balance_before = $this->balance_before;
        $transaction->balance_after = $this->balance_after;
        $transaction->details = $this->details;
        $transaction->save();

    }
}
