<?php

namespace LBHurtado\XRider\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use LBHurtado\XRider\Contracts\RiderAnalyticsRecorderContract;
use LBHurtado\XRider\Contracts\RiderExperienceResolverContract;
use LBHurtado\XRider\Data\RiderAnalyticsEventData;
use LBHurtado\XRider\Data\RiderSubjectData;

class RiderSuccessPageController extends Controller
{
    public function __construct(
        protected RiderExperienceResolverContract $resolver,
        protected RiderAnalyticsRecorderContract $analytics,
    ) {}

    public function __invoke(Request $request, string $reference)
    {
        $subject = new RiderSubjectData(
            reference: $reference,
            sourceType: $request->query('source_type'),
            sourceId: $request->query('source_id'),
            code: $request->query('code'),
            payload: $request->query(),
        );

        $experience = $this->resolver->resolve($subject, [
            'state' => $request->query('state'),
            'rider' => $request->query('rider', []),
            'campaign' => $request->query('campaign', []),
            'ads' => $request->query('ads', []),
            'analytics' => $request->query('analytics', []),
        ]);

        $this->analytics->record(new RiderAnalyticsEventData(
            event: 'rider.success.viewed',
            reference: $reference,
            sourceType: $subject->sourceType,
            sourceId: $subject->sourceId,
            context: ['state' => $experience->normalizedState()],
        ));

        if (! class_exists(Inertia::class)) {
            return response()->json(['data' => $experience->toArray()]);
        }

        return Inertia::render('x-rider/Success', [
            'rider' => $experience->toArray(),
            'redirectEndpoint' => route('x-rider.redirect', ['reference' => $reference]),
        ]);
    }
}
