<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;

class SellerWalletController extends Controller
{
    public function index(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $walletService = app(WalletService::class);
        $wallet = $walletService->getOrCreate($seller->id);
        $balance = $walletService->getBalance($seller->id);

        $query = WalletTransaction::where('seller_id', $seller->id);

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            'total_credits' => WalletTransaction::where('seller_id', $seller->id)->credits()->sum('amount'),
            'total_debits' => WalletTransaction::where('seller_id', $seller->id)->debits()->sum('amount'),
        ];

        return view('seller.wallet.index', compact('wallet', 'balance', 'transactions', 'summary', 'seller'));
    }
}
