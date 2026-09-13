<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\CommissionRecord;
use Illuminate\Http\Request;

class SellerCommissionController extends Controller
{
    public function index(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $query = CommissionRecord::where('seller_id', $seller->id)
            ->with(['order:id,order_number,created_at', 'commissionRule:id,name,type,value']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $records = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total_commission' => CommissionRecord::where('seller_id', $seller->id)->sum('commission_amount'),
            'total_earnings' => CommissionRecord::where('seller_id', $seller->id)->sum('seller_earnings'),
            'pending_commission' => CommissionRecord::where('seller_id', $seller->id)->pending()->sum('commission_amount'),
            'settled_commission' => CommissionRecord::where('seller_id', $seller->id)->settled()->sum('commission_amount'),
            'total_orders' => CommissionRecord::where('seller_id', $seller->id)->count(),
        ];

        return view('seller.commission.index', compact('records', 'stats', 'seller'));
    }

    public function earnings(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $wallet = app(\App\Services\WalletService::class)->getOrCreate($seller->id);
        $balance = app(\App\Services\WalletService::class)->getBalance($seller->id);

        $monthlyEarnings = CommissionRecord::where('seller_id', $seller->id)
            ->where('status', '!=', 'cancelled')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(seller_earnings) as earnings, COUNT(*) as orders")
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        return view('seller.commission.earnings', compact('wallet', 'balance', 'monthlyEarnings', 'seller'));
    }
}
