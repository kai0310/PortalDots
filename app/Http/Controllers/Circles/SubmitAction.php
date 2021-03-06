<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Services\Circles\CirclesService;
use Illuminate\Support\Facades\Auth;

class SubmitAction extends Controller
{
    private $circlesService;

    public function __construct(CirclesService $circlesService)
    {
        $this->circlesService = $circlesService;
    }

    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        if (!Auth::user()->isLeaderInCircle($circle)) {
            abort(403);
        }

        if (!$circle->canSubmit()) {
            return redirect()
                ->route('circles.users.index', ['circle' => $circle])
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '参加登録に必要な人数が揃っていないため、参加登録の提出はまだできません');
        }

        $this->circlesService->submit($circle);

        $circle->load('users');

        foreach ($circle->users as $user) {
            $this->circlesService->sendSubmitedEmail($user, $circle);
        }

        return redirect()
            ->route('home')
            ->with('topAlert.title', '企画参加登録を提出しました！');
    }
}
