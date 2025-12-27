<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Story;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Notifications\InvitePlan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // dd(auth()->user()->plans()->wherePivotNotNull('accepted_at')->with(['activities', 'users'])->get());
        return Inertia::render('Plans', ['plans' => Auth::user()->plans()->wherePivotNotNull('accepted_at')->with(['activities', 'users', 'activities.place'])->orderBy('start_date', 'desc')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        try {
            $createdPlan = Plan::create(
                [
                    'name' => $request->name,
                    'start_date' => date("Y-m-d", strtotime($request->startDate)),
                    'end_date' => date("Y-m-d", strtotime($request->endDate)),
                    'public_id' => Str::uuid(),
                ]
            );
            $createdPlan->users()->attach($request->userId, ['accepted_at' => date("Y-m-d H:i:s")]);
            return response()->json($createdPlan, 200);
        } catch (Throwable $e) {
            return response()->json(
                [
                    'message' => 'Something went wrong.' . $e->getMessage(),
                ],
                400
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Plan $plan, Request $request)
    {
        if (!Gate::allows('edit-plan', $plan)) {
            abort(403);
        }
        $plan->load(['activities' => function ($query) {
            $query->orderBy('time', 'asc');
        }, 'users' => function ($query) {
            $query->wherePivotNotNull('accepted_at');
        }, 'activities.place']);
        if ($request->input('reload')) {
            return response()->json(['plan' => $plan->refresh()], 200);
        }
        return Inertia::render('Plan', ['plan' => $plan]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            Plan::where('id', $id)->update([
                'name' => $request->name,
                'start_date' => date("Y-m-d", strtotime($request->startDate)),
                'end_date' => date("Y-m-d", strtotime($request->endDate)),
            ]);

            return response()->json(
                [
                    'message' => 'Success'
                ],
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                [
                    'message' => 'Something went wrong. ' . $err->getMessage(),
                ],
                400
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Plan::find($id)->delete();
            return redirect('/dashboard/plans')->with('alert', ['success', 'Delete Plan', 'Plan has been deleted']);
            return response()->json(
                [
                    'message' => 'Success'
                ],
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                [
                    'message' => 'Something went wrong. ' . $err->getMessage(),
                ],
                400
            );
        }
    }

    public function join(Plan $plan)
    {
        $user = $plan->users()->wherePivot('user_id', auth()->id())->first();
        if ($user) {
            DB::table('plan_user')->where('user_id', $user->id)->update(['accepted_at' => now()]);
            $user->notifications()->where('data->plan_id', $plan->id)->update(['read_at' => now()]);
            return redirect('/dashboard/plans/' . $plan->public_id)->with('alert', ['success', 'Join Plan', 'You successfully joined plan']);
        } else {
            abort(403);
        }
    }
    public function invite(Plan $plan, Request $request)
    {
        if (Gate::allows('invite-delete-plan', $plan)) {
            $user_id = $request->input('user');
            $userexist = $plan->users()->wherePivot('user_id', $user_id)->first();
            if (!$userexist) {
                $user = User::find($user_id);
                $user->notify(new InvitePlan($plan, auth()->user()));
                // Notification::send($users, new InvitePlan($plan, auth()->user()));
                $plan->users()->attach($user, ['role' => 'Member']);
            } else {
                // $userexist->notifications()->where('plan_id', $plan)->latest()->first()->update(['updated_at', now()]);
            }
        }
    }

    public function getActivities(Plan $plan)
    {
        $activity =  DB::table('activities')
            ->where('plan_id', '=', $plan->id)
            ->join('places', 'activities.place_id', '=', 'places.id')
            ->select(['activities.id', 'place_id', 'plan_id', 'time', 'name', 'types', 'address', 'province', 'latitude', 'longitude', 'rating', 'url', 'summary', 'photo'])
            ->get();
        return $activity;
    }

    public function extendPlan(Plan $plan, Request $request)
    {
        $plan->end_date = date("Y-m-d", strtotime($request->endDate));
        $plan->save();
    }
}