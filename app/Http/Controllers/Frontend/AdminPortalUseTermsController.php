<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminPortalUseTermsController extends Controller
{
    /**
     * Shows admin portal use terms
     *
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function terms()
    {
        //allow to apply only members. If user has more than one role then he/she already has access
        Gate::allowIf(fn ($user) => $user->hasRole(Role::MEMBER) && $user->roles->containsOneItem());
        $settings = auth()->user()->settings()->data;

        //user already accepted the terms
        if (isset($settings['accept_admin_portal_use_terms'])) {
            return to_route('frontend.user.applications');
        }

        return view('frontend.myPortal.adminPortalUseTerms');
    }

    public function accept(Request $request)
    {
        Gate::allowIf(fn ($user) => $user->hasRole(Role::MEMBER) && $user->roles->containsOneItem());
        $settings = auth()->user()->settings()->data;

        //user already accepted the terms
        if (isset($settings['accept_admin_portal_use_terms'])) {
            return to_route('frontend.user.applications');
        }

        $validated = $request->validate([
            'accept_use_terms' => ['required', 'accepted'],
        ]);

        if ($validated['accept_use_terms'] === 'on') {
            $settings = auth()->user()->settings();
            $data = $settings->data;
            $data['accept_admin_portal_use_terms'] = true;
            $data['admin_portal_application_status'] = ApplicationStatus::IN_PROGRESS;
            $settings->data = $data;
            $settings->save();
        }

        return to_route('frontend.userSettings.edit');
    }
}
