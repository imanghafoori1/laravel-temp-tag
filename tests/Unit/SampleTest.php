<?php

namespace Imanghafoori\TempTagTests\Unit;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Imanghafoori\Tags\Models\TempTag;
use Imanghafoori\TempTagTests\Requirements\Stubs\Models\User;
use Imanghafoori\TempTagTests\TestCase;

class SampleTest extends TestCase
{
    /** @test */
    public function main()
    {
        TempTag::query()->delete();
        $user = new User();
        $user->id = 1;

        // =================== test no tag =====================

        $res = [
            tempTags($user)->getExpiredTag('banned'),
            tempTags($user)->getTag('banned'),
            tempTags($user)->getAllTags('banned')->isEmpty(),
            tempTags($user)->getActiveTag('banned'),
        ];

        assert($res === [null, null, true, null]);

        // =================== test active tag =====================

        $tomorrow = Carbon::now()->addDay();
        tempTags($user)->tagIt('banned', $tomorrow);

        $res = [
            tempTags($user)->getExpiredTag('banned'),
            tempTags($user)->getTag('banned')->isActive(),
            tempTags($user)->getActiveTag('banned')->isActive(),
            tempTags($user)->getAllTags()->first()->title,
            tempTags($user)->getActiveTag('banned')->isPermanent(),
        ];
        assert($res === [null, true, true, 'banned', false]);

        // =================== test expired tag =====================

        // travel through time
        Carbon::setTestNow(Carbon::now()->addDay()->addMinute());

        $res = [
            tempTags($user)->getExpiredTag('banned')->isActive(),
            tempTags($user)->getTag('banned')->isActive(),
            tempTags($user)->getActiveTag('banned'),
            tempTags($user)->getAllTags()->first()->title,
        ];
        assert($res === [false, false, null, 'banned']);

        // =================== test deleted tag =====================

        tempTags($user)->unTag('banned');
        $res = [
            tempTags($user)->getExpiredTag('banned'),
            tempTags($user)->getTag('banned'),
            tempTags($user)->getActiveTag('banned'),
        ];

        assert($res === [null, null, null]);

        // =================== test deleted tag =====================

        tempTags($user)->tagIt(['banned', 'man', 'superman', 'covid19']);
        tempTags($user)->tagIt('covid19', Carbon::now()->subSeconds(1));
        tempTags($user)->unTag(['banned', 'man']);
        $res = [
            tempTags($user)->getExpiredTag('banned'),
            tempTags($user)->getTag('banned'),
            tempTags($user)->getActiveTag('banned'),

            tempTags($user)->getExpiredTag('man'),
            tempTags($user)->getTag('man'),
            tempTags($user)->getActiveTag('man'),

            tempTags($user)->getExpiredTag('covid19')->title,
            tempTags($user)->getTag('covid19')->title,
            tempTags($user)->getActiveTag('covid19'),

            tempTags($user)->getActiveTag('superman')->title,
            tempTags($user)->getAllTags()->count(),
        ];

        assert($res === [
            null,
            null,
            null,

            null,
            null,
            null,

            'covid19',
            'covid19',
            null,

            'superman',
            2,
        ]);

        // =================== test deleted tag =====================

        tempTags($user)->tagIt('banned');
        tempTags($user)->unTag();
        $res = [
            tempTags($user)->getExpiredTag('banned'),
            tempTags($user)->getTag('banned'),
            tempTags($user)->getActiveTag('banned'),
            tempTags($user)->getAllTags()->isEmpty(),
        ];

        assert($res === [null, null, null, true]);

        tempTags($user)->tagIt('banned');
        tempTags($user)->unTag('manned');
        $res = [
            tempTags($user)->getExpiredTag('banned'),
            tempTags($user)->getTag('banned')->isActive(),
            tempTags($user)->getActiveTag('banned')->isActive(),
            tempTags($user)->getActiveTag('banned')->isPermanent(),
            tempTags($user)->getAllTags()->count(),
        ];

        assert($res === [null, true, true, true, 1]);

        // =================== test expire tag =====================

        tempTags($user)->expireNow('banned');
        $res = [
            tempTags($user)->getExpiredTag('banned')->title,
            tempTags($user)->getTag('banned')->isActive(),
            tempTags($user)->getActiveTag('banned'),
            tempTags($user)->getAllTags()->count(),
        ];

        assert($res === ['banned', false, null, 1]);

        // ================== make permanent ======================

        $tags = tempTags($user)->tagIt('banned', Carbon::now()->addDay());
        tempTags($user)->getTag('banned')->expiresAt();

        // ================== make permanent ======================
        tempTags($user)->unTag();
        tempTags($user)->tagIt(['banned']);
        Event::fake();
        tempTags($user)->tagIt(['rut'], Carbon::now()->subSecond());

        $actives = tempTags($user)->getAllActiveTags();
        $expired = tempTags($user)->getAllExpiredTags();
        $all = tempTags($user)->getAllTags();
        assert(($actives[0])->title === 'banned');
        assert(($expired[0])->title === 'rut');
        assert(count($all) === 2);
        Event::assertDispatched('tmp_tagged:users,rut');

        // ================== fetching records ======================
        User::query()->delete();
        User::query()->insert(
            [
                'email'    => 'iman@gmail.com',
                'password' => bcrypt('111'),
            ]
        );
        $user = User::where('email', 'iman@gmail.com')->first();
        tempTags($user)->tagIt('banned');

        $r = User::query()->hasActiveTempTags(['banned', 'aaa'])->first();
        assert($r->email === 'iman@gmail.com');

        $r = User::query()->hasTempTags(['banned', 'aaa'])->first();
        assert($r->email === 'iman@gmail.com');

        // expire the tag
        tempTags($user)->expireNow('banned');

        $r = User::query()->hasActiveTempTags(['banned', 'aaa'])->first();
        assert(is_null($r));

        $r = User::query()->hasExpiredTempTags(['banned', 'aaa'])->first();
        assert($r->email === 'iman@gmail.com');

        $r = User::query()->hasTempTags(['banned', 'aaa'])->first();
        assert($r->email === 'iman@gmail.com');

        tempTags($user)->unTag();

        tempTags($user)->tagIt('banned', null, ['by' => 'admin']);
        $r = User::query()->hasTempTags('banned', ['by' => 'admin'])->first();
        assert($r->email === 'iman@gmail.com');
        $r = User::query()->hasTempTags('banned', ['by' => 'non-admin'])->first();
        assert(is_null($r));
        $r = User::query()->hasTempTags('banned-', ['by' => 'admin'])->first();
        assert(is_null($r));
        $r = User::query()->hasTempTags('banned', ['by' => 'admin', 'some' => 'value'])->first();
        assert(is_null($r));
    }
}
