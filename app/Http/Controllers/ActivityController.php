<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Place;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Events\UpdateActivity;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Plan $plan, Request $request)
    {
        if (!Gate::allows('edit-plan', $plan)) {
            abort(403);
        }
        $data = $request->input('data');
        $activities = $request->input('activities');
        $newActivity = [
            'plan_id' => $plan->id,
            'place_id' => $data['place']['id'],
            'time' => $data['time']
        ];
        Place::upsert([
            'id' => $data['place']['id'],
            'name' => $data['place']['displayName']['text'],
            'types' => json_encode($data['place']['types']),
            'address' => $data['place']['formattedAddress']  ?? null,
            'latitude' => $data['place']['location']['latitude'],
            'longitude' => $data['place']['location']['longitude'],
            'rating' => $data['place']['rating'] ?? null,
            'url' => $data['place']['googleMapsUri'],
            'summary' => $data['place']['editorialSummary']['text'] ?? null,
            'photo' => $data['place']['photos'][0]['name'] ?? null,
            'province' => collect($data['place']['addressComponents'])->filter(function($value) {
                return in_array('administrative_area_level_1', $value['types']);
            })->first()['shortText'] ?? null
        ], ['id']);

        Activity::create($newActivity);
        $updated = $plan->activities()->with('place')->orderBy('time', 'ASC')->get();
        // broadcast(new UpdateActivity($updated, $plan->id))->toOthers();

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        //
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Plan $plan, Activity $activity, Request $request)
    {
        $req = $request->all();
        // return response()->json($request->all());
        $updatedActivity = Activity::find($activity->id);
        if ($req['place']) {
            Place::upsert([
                'id' => $req['place']['id'],
                'name' => $req['place']['displayName']['text'],
                'types' => json_encode($req['place']['types']),
                'address' => $req['place']['formattedAddress'],
                'latitude' => $req['place']['location']['latitude'],
                'longitude' => $req['place']['location']['longitude'],
                'rating' => $req['place']['rating'],
                'url' => $req['place']['googleMapsUri'],
                'summary' => $req['place']['editorialSummary']['text'] ?? '',
                'photo' => $req['place']['photos'][0]['name'],
            ], ['id']);
            $updatedActivity->place_id = $req['place']['id'];
        }
        $updatedActivity->time = $req['time'];
        
        $updatedActivity->save();
        
        $updated = $plan->activities()->with('place')->orderBy('time', 'ASC')->get();
        broadcast(new UpdateActivity($updated, $plan->id))->toOthers();

        return response()->json(
            [
                'message' => 'Success'
            ],
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan, Activity $activity, Request $request)
    {
        if (!Gate::allows('edit-plan', $plan)) {
            abort(403);
        }
        $data = $request->all();
        $activity->delete();
        $updated = $plan->activities()->with('place')->orderBy('time', 'asc')->get();
        // broadcast(new UpdateActivity($updated, $plan->id))->toOthers();
        return response()->json($data['activities']);
    }
}