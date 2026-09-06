<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Settlement;
use App\Services\SettlementService;
use App\Services\WalletService;
use Illuminate\Http\Request;

class SellerSettlementController extends Controller
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

        $query = Settlement::where('seller_id', $seller->id);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $settlements = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total_settled' => Settlement::where('seller_id', $seller->id)->completed()->sum('amount'),
            'pending_settlements' => Settlement::where('seller_id', $seller->id)->pending()->sum('amount'),
            'total_settlements' => Settlement::where('seller_id', $seller->id)->count(),
        ];

        return view('seller.wallet.settlements', compact('settlements', 'wallet', 'balance', 'stats', 'seller'));
    }
}
