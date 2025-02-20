<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class BrokerController extends Controller
{
    public function getBrokerList()
    {
        Gate::authorize('access', Broker::class);

        return Inertia::render('Broker/Listing/BrokerListing', [
            'brokerCounts' => Broker::count(),
            'locales' => config('app.setting_locales'),
        ]);
    }

    public function getBrokerData(Request $request)
    {
        Gate::authorize('access', Broker::class);

        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true); //only() extract parameters in lazyEvent

            $query = Broker::query()
                ->with([
                    'user:id,name',
                ])
                ->withCount(['connections as connections_count' => function($query) {
                    $query->select(DB::raw('count(distinct(user_id))'));
                }])
                ->withSum('connections', 'capital_fund');

            //global filter
            if ($data['filters']['global']['value']) {
                $query->where(function ($q) use ($data) { //function() allow to add more condition' use ($data) means $data is passed into the clause to be use
                    $keyword = $data['filters']['global']['value'];

                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            }

            //status filter
            if ($data['filters']['status']['value']) {
                $query->where('status', $data['filters']['status']['value']);
            }

            //sort order
            if ($data['sortOrder']) {
                $sortType = $data['sortOrder'];
                switch ($sortType) {
                    case 'latest':
                        $query->orderBy('created_at', 'desc');
                        break;

                    case 'largest_fund':
                        $query->orderByDesc('connections_sum_capital_fund');
                        break;

                    case 'most_investors':
                        $query->orderByDesc('connections_count');
                        break;

                    default:
                        return response()->json(['error' => 'Invalid filter'], 400);
                }
            } else {
                $query->latest();
            }

            $brokers = $query->paginate($data['rows']);

            foreach ($brokers as $broker) {
                $broker->broker_image = $broker->getMedia('broker_image')->map(function ($media) {
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
        Gate::authorize('create', Broker::class);

        $locales = $request->input('locales', []);

        $rules = [
            'locales' => ['required', 'array'],
            'locales.*' => ['in:' . implode(',', config('app.setting_locales'))],
            'name' => ['required', 'max:255'],
            'url' => ['required'],
            'description_translation' => ['required', 'array', 'max:500'],
            'broker_image' => ['required', 'max:2084'],
        ];

        foreach ($locales as $locale) {
            $rules["description_translation.$locale"] = ['required'];
        }

        $attributeNames = [
            'name' => trans('public.name'),
            'url' => trans('public.url'),
            'locales' => trans('public.languages'),
            'description_translation.*' => trans('public.description'),
        ];

        $validator = Validator::make($request->all(), $rules)
            ->setAttributeNames($attributeNames);

        $validator->validate();

        $broker = new Broker();
        $broker->name = $request->name;
        $broker->url = $request->url;
        $broker->description = $request->description_translation;
        $broker->user_id = Auth::id();
        $broker->save();

        if ($request->hasFile('broker_image')) {
            $broker->addMediaFromRequest('broker_image')->toMediaCollection('broker_image');
        }

        return back()->with('toast');
    }

    public function updateBrokerInfo(Request $request)
    {
        Gate::authorize('update', Broker::class);

        $locales = $request->input('locales', []);

        $rules = [
            'locales' => ['required', 'array'],
            'locales.*' => ['in:' . implode(',', config('app.setting_locales'))],
            'name' => ['required', 'max:255'],
            'url' => ['required'],
            'description_translation' => ['required', 'array', 'max:500'],
            'broker_image' => ['nullable', 'max:2084'],
        ];

        foreach ($locales as $locale) {
            $rules["description_translation.$locale"] = ['required'];
        }

        $attributeNames = [
            'name' => trans('public.name'),
            'url' => trans('public.url'),
            'locales' => trans('public.languages'),
            'description_translation.*' => trans('public.description'),
        ];

        $validator = Validator::make($request->all(), $rules)
            ->setAttributeNames($attributeNames);

        $validator->validate();

        $broker = Broker::find($request->id);
        $broker->name = $request->name;
        $broker->url = $request->url;
        $broker->description = $request->description_translation;

        $broker->update();

        if ($request->hasFile('broker_image')) {
            $broker->clearMediaCollection('broker_image');
            $broker->addMediaFromRequest('broker_image')->toMediaCollection('broker_image');
        }

         return back()->with('toast');
    }

    public function updateBrokerStatus(Request $request)
    {
        Gate::authorize('update', Broker::class);

        $broker = Broker::find($request->id);

        $broker->status = $broker->status == 'active' ? 'inactive' : 'active';
        $broker->update();
    }
}
