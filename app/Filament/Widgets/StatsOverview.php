<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget

{
    private function getPercentage(int $from, int $to){
        return $to - $from / ($to + $from / 2) * 100;    
    }

    protected function getStats(): array
    {
        $newListing = Listing::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count();
        $transactions = Transaction::whereStatus('approved')->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        $previousTrx = Transaction::whereStatus('approved')->whereMonth('created_at', Carbon::now()->subMonth()->month)->whereYear('created_at', Carbon::now()->subMonth()->year);
        $trxPercentage = $this->getPercentage($previousTrx->count(), $transactions->count());
        $revenuePercentage = $this->getPercentage($previousTrx->sum('total_price'), $transactions->sum('total_price'));

        return [
            Stat::make('New listing of them month', $newListing),
            Stat::make('Transaction of them month', $transactions->count())
                ->description($trxPercentage > 0 ? "{$trxPercentage}% increased" : "{$trxPercentage}% decreased")
                ->descriptionIcon($trxPercentage > 0 ? "heroicon-m-arrow-trending-up" : "heroicon-m-arrow-trending-down")
                ->color($trxPercentage > 0 ? "success" : "danger"),
            Stat::make('Revenue of them month', Number::currency($transactions->sum('total_price'), "USD"))
                ->description($revenuePercentage > 0 ? "{$revenuePercentage}% increased" : "{$revenuePercentage}% decreased")
                ->descriptionIcon($revenuePercentage > 0 ? "heroicon-m-arrow-trending-up" : "heroicon-m-arrow-trending-down")
                ->color($revenuePercentage > 0 ? "success" : "danger"),
        ];
    }
}
