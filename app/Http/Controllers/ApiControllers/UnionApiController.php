<?php

namespace App\Http\Controllers\ApiControllers;

use App\Models\Union;
use App\Models\UnionMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnionApiController
{
    public function index(Request $request)
    {
        $userUnionIds = Auth::user()->creator->union_memberships->pluck('id');
        return response()->json([
            "unions" => Union::all(),
            "userUnionIds" => $userUnionIds
        ]);
    }

    public function join(Request $request) {
        $request->validate([
            'union_id' => 'required|exists:unions,id'
        ]);
        $union = Union::find($request->union_id);
        if (Auth::user()->creator->union_memberships->contains('id', $union->id)) {
            return response()->json([
                'message' => 'Already a member of ' . $union->name,
                'toastType' => 'warning'
            ], 400);
        }

        UnionMembership::firstOrCreate([
            'member_id' =>  Auth::user()->creator->id,
            'union_id' => $union->id,
        ]);
        $union->member_count++;
        $union->save();
        return response()->json([
            'message' => 'Joined ' . $union->name,
            'toastType' => 'success'
        ]);
    }

    public function leave(Request $request) {
        $request->validate([
            'union_id' => 'required|exists:unions,id'
        ]);
        $union = Union::find($request->union_id);
        if (!Auth::user()->creator->union_memberships->contains('id', $union->id)) {
            return response()->json([
                'message' => 'Not a member of ' . $union->name,
                'toastType' => 'warning'
            ], 400);
        }

        UnionMembership::query()
            ->where([
                [
                    'member_id', '=', Auth::user()->creator->id,
                ],
                [
                    'union_id', '=', $union->id
                ]
            ])
            ->delete();
        $union->member_count--;
        $union->save();
        return response()->json([
            'message' => 'Left ' . $union->name,
            'toastType' => 'undo'
        ]);
    }

}
