<?php

namespace App\Containers\AppSection\Authorization\Tests\Functional\API;

use App\Containers\AppSection\Authorization\Data\Factories\PermissionFactory;
use App\Containers\AppSection\Authorization\Data\Factories\RoleFactory;
use App\Containers\AppSection\Authorization\Tests\Functional\ApiTestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;

#[Group('authorization')]
#[CoversNothing]
final class RevokeRolePermissionsTest extends ApiTestCase
{
    protected string $endpoint = 'delete@v1/roles/{role_id}/permissions';

    protected array $access = [
        'permissions' => 'manage-roles',
        'roles' => null,
    ];

    public function testDetachSinglePermissionFromRole(): void
    {
        $permissionA = PermissionFactory::new()->createOne();
        $permissionB = PermissionFactory::new()->createOne();
        $role = RoleFactory::new()->createOne();
        $role->givePermissionTo([$permissionA, $permissionB]);
        $data = [
            'permission_ids' => [$permissionA->getHashedKey()],
        ];

        $response = $this->injectId($role->id, replace: '{role_id}')->makeCall($data);

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json->has('data')
                ->where('data.object', 'Role')
                ->where('data.id', $role->getHashedKey())
                ->count('data.permissions.data', 1)
                ->where('data.permissions.data.0.id', $permissionB->getHashedKey())
                ->etc(),
        );
    }

    public function testDetachMultiplePermissionFromRole(): void
    {
        $permissionA = PermissionFactory::new()->createOne();
        $permissionB = PermissionFactory::new()->createOne();
        $permissionC = PermissionFactory::new()->createOne();
        $role = RoleFactory::new()->createOne();
        $role->givePermissionTo([$permissionA, $permissionB, $permissionC]);
        $data = [
            'permission_ids' => [$permissionA->getHashedKey(), $permissionC->getHashedKey()],
        ];

        $response = $this->injectId($role->id, replace: '{role_id}')->makeCall($data);

        $response->assertOk();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json->has('data')
                ->where('data.object', 'Role')
                ->where('data.id', $role->getHashedKey())
                ->count('data.permissions.data', 1)
                ->where('data.permissions.data.0.id', $permissionB->getHashedKey())
                ->etc(),
        );
    }

    public function testDetachPermissionFromNonExistingRole(): void
    {
        $permission = PermissionFactory::new()->createOne();
        $invalidId = 7777777;
        $data = [
            'permission_ids' => [$permission->getHashedKey()],
        ];

        $response = $this->injectId($invalidId, replace: '{role_id}')->makeCall($data);

        $response->assertUnprocessable();
        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json->has('errors')
                ->where('errors.role_id.0', 'The selected role id is invalid.')
                ->etc(),
        );
    }

    public function testDetachNonExistingPermissionFromRole(): void
    {
        $role = RoleFactory::new()->createOne();
        $invalidId = 7777777;
        $data = [
            'permission_ids' => [$this->encode($invalidId)],
        ];

        $response = $this->injectId($role->id, replace: '{role_id}')->makeCall($data);

        $response->assertJson(
            static fn (AssertableJson $json): AssertableJson => $json->has(
                'errors',
                static fn (AssertableJson $errors) => $errors->has(
                    'permission_ids.0',
                    static fn (AssertableJson $permissionIds) => $permissionIds->where('0', 'The selected permission_ids.0 is invalid.'),
                )->etc(),
            )->etc(),
        );
    }
}
