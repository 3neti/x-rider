<?php

namespace LBHurtado\XRider\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LBHurtado\XRider\Contracts\RiderAnalyticsRecorderContract;
use LBHurtado\XRider\Contracts\RiderExperienceResolverContract;
use LBHurtado\XRider\Contracts\SuccessRedirectResolverContract;
use LBHurtado\XRider\Data\RiderAnalyticsEventData;
use LBHurtado\XRider\Data\RiderSubjectData;

class RiderRedirectController extends Controller
{
    public function __construct(
        protected RiderExperienceResolverContract $experiences,
        protected SuccessRedirectResolverContract $redirects,
        protected RiderAnalyticsRecorderContract $analytics,
    ) {}

    public function __invoke(Request $request, string $reference)
    {
        $subject = new RiderSubjectData(
            type: $request->query('source_type', 'reference'),
            id: $request->query('source_id', $reference),
            code: $request->query('code'),
            meta: $request->query(),
        );

        $experience = $this->experiences->resolve($subject, [
            'state' => $request->query('state'),
            'rider' => $request->query('rider', []),
            'campaign' => $request->query('campaign', []),
            'ads' => $request->query('ads', []),
            'analytics' => $request->query('analytics', []),
        ]);

        $target = $this->redirects->resolve($experience);

        $this->analytics->record(new RiderAnalyticsEventData(
            event: 'rider.redirect.started',
            reference: $subject->reference(),
            sourceType: $subject->type,
            sourceId: $subject->id,
            context: [
                'state' => $experience->normalizedState(),
                'target' => $target,
            ],
        ));

        return redirect()->away($target);
    }
}
