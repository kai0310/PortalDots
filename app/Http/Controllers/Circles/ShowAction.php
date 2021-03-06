<?php

namespace App\Http\Controllers\Circles;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ShowAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.belongsTo', $circle);

        $reauthorized_at = new CarbonImmutable(session()->get('user_reauthorized_at'));

        if (
            !$circle->hasSubmitted()
            || (session()->has('user_reauthorized_at') && $reauthorized_at->addHours(2)->gte(now()))
        ) {
            $circle->load('users', 'places');

            return view('circles.show')
                ->with('circle', $circle);
        }
        return redirect()
            ->route('circles.auth', ['circle' => $circle]);
    }
}
