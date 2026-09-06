<?php

namespace App\Services;

use App\Models\RecentlyViewed;
use Illuminate\Support\Facades\Session;

class RecentlyViewedService
{
    private const MAX_ITEMS = 20;

    public function getSessionId(): string
    {
        if (!Session::has('recently_viewed_session_id')) {
            Session::put('recently_viewed_session_id', md5(uniqid('rv_', true) . time()));
        }
        return Session::get('recently_viewed_session_id');
    }

    public function track(int $productId): void
    {
        if (auth()->check()) {
            $this->trackForUser($productId);
        } else {
            $this->trackForGuest($productId);
        }
    }

    protected function trackForUser(int $productId): void
    {
        RecentlyViewed::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->delete();

        RecentlyViewed::create([
            'user_id' => auth()->id(),
            'product_id' => $productId,
        ]);

        $this->trimUserItems(auth()->id());
    }

    protected function trackForGuest(int $productId): void
    {
        $sessionId = $this->getSessionId();

        RecentlyViewed::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->where('product_id', $productId)
            ->delete();

        RecentlyViewed::create([
            'session_id' => $sessionId,
            'product_id' => $productId,
        ]);

        $this->trimGuestItems($sessionId);
    }

    public function getItems(int $limit = 8): \Illuminate\Support\Collection
    {
        $query = RecentlyViewed::with(['product.seller', 'product.category', 'product.brand', 'product.images'])
            ->where('product_id', '!=', request()->route('product'));

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', $this->getSessionId())
                  ->whereNull('user_id');
        }

        return $query->latest()
            ->limit($limit)
            ->get()
            ->pluck('product')
            ->filter();
    }

    public function mergeGuestData(int $userId): void
    {
        $sessionId = $this->getSessionId();

        $guestItems = RecentlyViewed::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        foreach ($guestItems as $guestItem) {
            $exists = RecentlyViewed::where('user_id', $userId)
                ->where('product_id', $guestItem->product_id)
                ->exists();

            if (!$exists) {
                $guestItem->update([
                    'user_id' => $userId,
                    'session_id' => null,
                ]);
            } else {
                $guestItem->delete();
            }
        }

        $this->trimUserItems($userId);
    }

    protected function trimUserItems(int $userId): void
    {
        $keep = RecentlyViewed::where('user_id', $userId)
            ->latest()
            ->limit(self::MAX_ITEMS)
            ->pluck('id')
            ->toArray();

        RecentlyViewed::where('user_id', $userId)
            ->whereNotIn('id', $keep)
            ->delete();
    }

    protected function trimGuestItems(string $sessionId): void
    {
        $keep = RecentlyViewed::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->latest()
            ->limit(self::MAX_ITEMS)
            ->pluck('id')
            ->toArray();

        RecentlyViewed::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->whereNotIn('id', $keep)
            ->delete();
    }
}
