<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RewardCatalog;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        return [
            'points' => $request->user()->reward_points,
            'catalog' => RewardCatalog::where('is_active', true)->orderBy('points_required')->get(),
            'redemptions' => RewardRedemption::with('reward', 'approver')->where('user_id', $request->user()->id)->latest()->get(),
        ];
    }

    public function redeem(Request $request, RewardCatalog $reward)
    {
        abort_unless($reward->is_active, 422, 'Reward is unavailable.');
        abort_if($request->user()->reward_points < $reward->points_required, 422, 'Not enough reward points.');
        return DB::transaction(function () use ($request, $reward) {
            $request->user()->decrement('reward_points', $reward->points_required);
            return response()->json(RewardRedemption::create([
                'user_id' => $request->user()->id, 'reward_catalog_id' => $reward->id,
                'points' => $reward->points_required, 'status' => 'Requested',
            ])->load('reward'), 201);
        });
    }

    public function redemptions()
    {
        return RewardRedemption::with('user.role', 'reward', 'approver')->latest()->paginate(50);
    }

    public function decide(Request $request, RewardRedemption $redemption)
    {
        $data = $request->validate(['status' => 'required|in:Approved,Rejected,Fulfilled', 'notes' => 'nullable|string']);
        if ($data['status'] === 'Rejected' && $redemption->status !== 'Rejected') {
            $redemption->user()->increment('reward_points', $redemption->points);
        }
        $redemption->update($data + ['approved_by' => $request->user()->id, 'decided_at' => now()]);
        return $redemption->load('user', 'reward', 'approver');
    }
}
