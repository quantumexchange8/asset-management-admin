<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BrokerController extends Controller
{
    public function getBrokerList()
    {
        return Inertia::render('Broker/Listing/BrokerListing', [
            'brokerCounts' => Broker::count(),
        ]);
    }

    public function getBrokerData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            $query = Broker::query()
                ->with([
                    'user:id,name',
                ]);

            //global filter
            if ($data['filters']['global']['value']) {
                $query->where(function ($q) use ($data) { //function() allow to add more condition' use ($data) means $data is passed into the clause to be use
                    $keyword = $data['filters']['global']['value'];

                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            }

            $brokers = $query->paginate($data['rows']);

            foreach ($brokers as $broker) {
                $broker->broker_image = $broker->getMedia('broker_image')->map(function ($media) {
                    return $media->getUrl();  // Return the media URL
                });

                $broker->broker_qr_image = $broker->getMedia('broker_qr_image')->map(function ($media) {
                    return $media->getUrl();  // Return the media URL
                });
            }

            return response()->json([
                'success' => true,
                'data' => $brokers,
            ]);
        }
        return response()->json(['success' => false, 'data' => []]);
    }

    public function addNewBroker(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'url' => ['required'],
            'description' => ['required', 'max:500'],
            'note' => ['required', 'max:500'],
            'broker_image' => ['required', 'mimes:jpg,jpeg,png', 'max:2084'],
            'broker_qr_image' => ['required', 'mimes:jpg,jpeg,png', 'max:2084'],
        ]);

        $broker = new Broker();
        $broker->name = $validatedData['name'];
        $broker->url = $validatedData['url'];
        $broker->description = $validatedData['description'];
        $broker->note = $validatedData['note'];
        $broker->user_id = Auth::id();
        $broker->save();

        if ($request->hasFile('broker_image')) {
            $broker->addMediaFromRequest('broker_image')->toMediaCollection('broker_image');
        }

        if ($request->hasFile('broker_qr_image')) {
            $broker->addMediaFromRequest('broker_qr_image')->toMediaCollection('broker_qr_image');
        }

        return redirect()->back()->with('toast');
    }

    public function brokerDetail($id)
    {

        $broker = Broker::where('id', $id)
            ->with([
                'user:id,name',
            ])->first();

        $broker_image = $broker->getMedia('broker_image')->map(fn($image) => $image->getUrl());

        $broker_qr_image = $broker->getMedia('broker_qr_image')->map(fn($image) => $image->getUrl());

        return Inertia::render('Broker/Listing/Detail/BrokerDetail', [
            'broker' => $broker,
            'broker_image' => $broker_image,
            'broker_qr_image' => $broker_qr_image,
        ]);
    }

    public function updateBrokerInfo(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'url' => ['required'],
            'description' => ['required', 'max:500'],
            'note' => ['required', 'max:500'],
            'broker_image' => ['nullable', 'mimes:jpg,jpeg,png', 'max:2084'],
            'broker_qr_image' => ['nullable', 'mimes:jpg,jpeg,png', 'max:2084'],
        ]);

        $broker = Broker::find($id);
        $broker->name = $validatedData['name'];
        $broker->url = $validatedData['url'];
        $broker->description = $validatedData['description'];
        $broker->note = $validatedData['note'];

        $broker->update();

        if ($request->hasFile('broker_image')) {
            $broker->clearMediaCollection('broker_image');
            $broker->addMediaFromRequest('broker_image')->toMediaCollection('broker_image');
        }

        if ($request->hasFile('broker_qr_image')) {
            $broker->clearMediaCollection('broker_qr_image');
            $broker->addMediaFromRequest('broker_qr_image')->toMediaCollection('broker_qr_image');
        }

        // return redirect()->back()->with('toast');
    }
}
