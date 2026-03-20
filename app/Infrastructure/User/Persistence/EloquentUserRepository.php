<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Persistence;

use App\Domain\User\Entities\User;
use App\Domain\User\ReadModels\PaginatedUsers;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\ValueObjects\UserListQuery;
use App\Models\User as UserModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentUserRepository implements UserRepository
{
    public function countAudience(): int
    {
        return UserModel::query()->count();
    }

    public function paginate(UserListQuery $query): PaginatedUsers
    {
        $sortableColumns = [
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'role' => 'is_admin',
            'registeredAt' => 'created_at',
        ];

        $column = $sortableColumns[$query->sortBy] ?? $sortableColumns['registeredAt'];
        $sortDirection = $query->direction === 'asc' ? 'asc' : 'desc';

        $paginator = UserModel::query()
            ->with('roles:id,name')
            ->when($query->search, function ($builder, string $term): void {
                $builder->where(function ($innerQuery) use ($term): void {
                    $like = '%'.$term.'%';

                    $innerQuery
                        ->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->when($query->role === 'superadmin', fn (Builder $builder) => $builder->where('is_admin', true))
            ->when($query->role === 'unassigned', function (Builder $builder): void {
                $builder->where('is_admin', false)->whereDoesntHave('roles');
            })
            ->when(! in_array($query->role, ['all', 'superadmin', 'unassigned'], true), function (Builder $builder) use ($query): void {
                $builder->whereHas('roles', fn (Builder $roles) => $roles->where('name', $query->role));
            })
            ->orderBy($column, $sortDirection)
            ->when($column !== 'id', fn ($builder) => $builder->orderByDesc('id'))
            ->paginate($query->perPage, ['*'], 'page', $query->page);

        return new PaginatedUsers(
            items: $paginator->getCollection()
                ->map(fn (UserModel $user): User => $this->toDomainUser($user))
                ->all(),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }

    public function suggest(string $query, int $limit = 8): array
    {
        $term = trim($query);

        if ($term === '') {
            return [];
        }

        $like = '%'.$term.'%';

        return UserModel::query()
            ->where(function ($innerQuery) use ($like): void {
                $innerQuery
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->flatMap(static fn (UserModel $user): array => [$user->name, $user->email])
            ->unique()
            ->values()
            ->all();
    }

    public function syncRoles(int $userId, array $roles): User
    {
        $user = UserModel::query()->with('roles:id,name')->findOrFail($userId);
        $user->syncRoles($roles);
        $user->load('roles:id,name');

        return $this->toDomainUser($user);
    }

    private function toDomainUser(UserModel $user): User
    {
        return new User(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            isAdmin: $user->is_admin,
            isSuperadmin: $user->is_admin,
            roles: $user->roles->pluck('name')->sort()->values()->all(),
            registeredAt: new DateTimeImmutable($user->created_at->toDateTimeString()),
        );
    }
}
