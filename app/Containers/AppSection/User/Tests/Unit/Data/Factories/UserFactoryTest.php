<?php

namespace App\Containers\AppSection\User\Tests\Unit\Data\Factories;

use App\Containers\AppSection\User\Data\Factories\UserFactory;
use App\Containers\AppSection\User\Enums\Gender;
use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\User\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[Group('user')]
#[CoversClass(UserFactory::class)]
final class UserFactoryTest extends UnitTestCase
{
    public function testCanCreateUser(): void
    {
        $user = UserFactory::new()->createOne();

        $this->assertInstanceOf(User::class, $user);
    }

    public function testCanCreateAdminUser(): void
    {
        $user = UserFactory::new()->admin()->createOne();

        $this->assertTrue($user->hasRole(config('appSection-authorization.admin_role')));
    }

    public function testCanCreateUnverifiedUser(): void
    {
        $user = UserFactory::new()->unverified()->createOne();

        $this->assertNull($user->email_verified_at);
    }

    public function testCanCreateVerifiedUser(): void
    {
        $user = UserFactory::new()->verified()->createOne();

        $this->assertNotNull($user->email_verified_at);
    }

    public function testCanSetGender(): void
    {
        $user = UserFactory::new()->gender(Gender::MALE)->createOne();

        $this->assertSame(Gender::MALE, $user->gender);
    }
}
