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
        config()->set('cache.stores.temp_tag', ['driver' => 'array']);
        TempTag::query()->delete();
        $user = new User();
        $user->id = 1;

        // =================== test no tag =====================

        $res = [
            tempTags($user)->getExpiredTag('banned'),
            tempTags($user)->getTag('banned'),
            tempTags($user)->getTagsLike('*')->isEmpty(),
            tempTags($user)->getAllTags()->isEmpty(),
            tempTags($user)->getActiveTag('banned'),
        ];

        $this->assertTrue($res === [null, null, true, true, null]);

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
        $this->assertTrue($res === [null, true, true, 'banned', false]);

        cache()->store('temp_tag')->flush();
        $this->assertTrue(tempTags($user)->getTag('banned')->isActive());

        tempTags($user)->tagIt('banned_for', $tomorrow, ['count' => 1, 'ban_level' => 'hard']);
        $tag = tempTags($user)->getTag('banned_for');
        $this->assertEquals(1, $tag->getPayload('count'));
        $this->assertEquals(1, $tag->count);
        $this->assertEquals('hard', $tag->ban_level);
        $this->assertEquals(['count' => 1, 'ban_level' => 'hard'], $tag->getPayload());
        $this->assertEquals(['count' => 1, 'ban_level' => 'hard'], $tag->payload);
        tempTags($user)->unTag('banned_for');
        // =================== test expired tag =====================

        // travel through time
        Carbon::setTestNow(Carbon::now()->addDay()->addMinute());

        $this->assertFalse(tempTags($user)->getExpiredTag('banned')->isActive());
        $this->assertFalse(tempTags($user)->getTag('banned')->isActive());
        $this->assertNull(tempTags($user)->getActiveTag('banned'));
        $this->assertEquals('banned', tempTags($user)->getAllTags()->first()->title);

        // =================== test deleted tag =====================

        tempTags($user)->unTag('banned');

        $this->assertNull(tempTags($user)->getExpiredTag('banned'));
        $this->assertNull(tempTags($user)->getTag('banned'));
        $this->assertNull(tempTags($user)->getActiveTag('banned'));

        // =================== test deleted tag =====================

        tempTags($user)->tagIt(['banned', 'man', 'superman', 'covid19', 'hello1', 'hello2']);
        $this->assertEquals(6, tempTags($user)->getTagCount());

        tempTags($user)->tagIt('covid19', Carbon::now()->subSeconds(1));
        tempTags($user)->unTag(['banned', 'man']);

        $tags = tempTags($user)->getTagsLike('hello*');
        $this->assertCount(2, $tags);
        $this->assertEquals('hello1', $tags[0]->title);
        $this->assertEquals('hello2', $tags[1]->title);

        $this->assertEquals(4, tempTags($user)->getTagCount());
        $this->assertEquals(1, tempTags($user)->getTagCount('sup*'));
        $this->assertEquals(2, tempTags($user)->getTagCount('hello*'));
        $this->assertEquals(1, tempTags($user)->getTagCount('superman'));
        $this->assertEquals(0, tempTags($user)->getTagCount('super-woman'));
        $this->assertEquals(3, tempTags($user)->getActiveTagCount());
        $this->assertEquals(1, tempTags($user)->getExpiredTagCount());
        $this->assertEquals(1, tempTags($user)->getExpiredTagCount('covid*'));

        $this->assertNotNull(tempTags($user)->getActiveTag('hello1'));

        tempTags($user)->unTag('hell*');

        $this->assertNull(tempTags($user)->getActiveTag('hello1'));
        $this->assertNull(tempTags($user)->getActiveTag('hello2'));
        $this->assertNull(tempTags($user)->getExpiredTag('hello2'));

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

        $this->assertTrue($res === [
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

        $this->assertTrue($res === [null, null, null, true]);

        tempTags($user)->tagIt('banned');
        tempTags($user)->unTag('manned');
        $this->assertNull(tempTags($user)->getExpiredTag('banned'));
        $res = [
            tempTags($user)->getTag('banned')->isActive(),
            tempTags($user)->getActiveTag('banned')->isActive(),
            tempTags($user)->getActiveTag('banned')->isPermanent(),
            tempTags($user)->getAllTags()->count(),
        ];
        $this->assertTrue($res === [true, true, true, 1]);

        // =================== test expire tag =====================

        tempTags($user)->expireNow('banned');
        $res = [
            tempTags($user)->getExpiredTag('banned')->title,
            tempTags($user)->getTag('banned')->isActive(),
            tempTags($user)->getActiveTag('banned'),
            tempTags($user)->getAllTags()->count(),
        ];

        $this->assertTrue($res === ['banned', false, null, 1]);

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
        $this->assertTrue($actives[0]->title === 'banned');
        $this->assertTrue($expired[0]->title === 'rut');
        $this->assertTrue(count($all) === 2);
        Event::assertDispatched('tmp_tagged:users,rut');

        // ================== fetching records ======================
        User::query()->delete();
        User::query()->insert(
            [
                'email' => 'iman@gmail.com',
                'password' => bcrypt('111'),
            ]
        );
        $user = User::where('email', 'iman@gmail.com')->first();
        tempTags($user)->tagIt('banned', now()->addDays(2));

        $r = User::query()->hasActiveTags(['banned', 'aaa'])->first();
        $this->assertTrue($r->email === 'iman@gmail.com');

        $r = User::query()->hasActiveTagsAt(['banned', 'aaa'], now()->addDay())->first();
        $this->assertTrue($r->email === 'iman@gmail.com');

        $r = User::query()->hasActiveTagsAt(['banned', 'aaa'], now())->first();
        $this->assertTrue($r->email === 'iman@gmail.com');

        $r = User::query()->hasActiveTagsAt(['banned', 'aaa'], now()->addDays(3))->first();
        $this->assertNull($r);

        $r = User::query()->hasNotActiveTagsAt(['banned', 'aaa'], now()->addDays(3))->first();
        $this->assertTrue($r->email === 'iman@gmail.com');

        $r = User::query()->hasNotActiveTagsAt(['banned', 'aaa'], now()->addDays(1))->first();
        $this->assertNull($r);

        $r = User::query()->hasActiveTags('ban*')->first();
        $this->assertTrue($r->email === 'iman@gmail.com');

        $r = User::query()->hasTags('ban*')->first();
        $this->assertTrue($r->email === 'iman@gmail.com');

        $r = User::query()->hasTags(['banned', 'aaa'])->first();
        $this->assertTrue($r->email === 'iman@gmail.com');

        // expire the tag
        tempTags($user)->expireNow('banned');

        $r = User::query()->hasActiveTags(['banned', 'aaa'])->first();
        $this->assertNull($r);

        $r = User::query()->hasExpiredTags(['banned', 'aaa'])->first();
        $this->assertTrue($r->email === 'iman@gmail.com');

        $r = User::query()->hasTags(['banned', 'aaa'])->first();
        $this->assertTrue($r->email === 'iman@gmail.com');

        tempTags($user)->unTag();

        tempTags($user)->tagIt('banned', null, ['by' => 'admin']);
        $r = User::query()->hasTags('banned', ['by' => 'admin'])->first();
        $this->assertTrue($r->email === 'iman@gmail.com');
        $r = User::query()->hasTags('banned', ['by' => 'non-admin'])->first();
        $this->assertNull($r);
        $r = User::query()->hasTags('banned-', ['by' => 'admin'])->first();
        $this->assertNull($r);
        $r = User::query()->hasTags('banned', ['by' => 'admin', 'some' => 'value'])->first();
        $this->assertNull($r);

        tempTags($user)->unTag('banned');
    }
}
