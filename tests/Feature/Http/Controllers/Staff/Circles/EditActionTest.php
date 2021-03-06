<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Permission;

class EditActionTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $staff;

    /** @var User */
    private $user;

    /** @var Circle */
    private $circle;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->states('staff')->create();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create();

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);
    }

    /**
     * @test
     */
    public function 参加登録が未完了の企画情報は編集できない()
    {
        Permission::create(['name' => 'staff.circles.edit']);
        $this->staff->syncPermissions(['staff.circles.edit']);

        $notSubmitted = factory(Circle::class)->states('notSubmitted')->create();
        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.circles.edit', ['circle' => $notSubmitted]));

        $response->assertStatus(404);
    }
}
