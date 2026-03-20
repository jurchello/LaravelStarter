<?php

declare(strict_types=1);

namespace App\Application\FeatureFlags;

use App\Application\FeatureFlags\Exceptions\FeatureFlagNotFound;
use App\Domain\FeatureFlags\Contracts\FeatureFlagRuntime;
use App\Domain\FeatureFlags\Repositories\FeatureFlagRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class DeleteFeatureFlagAction
{
    public function __construct(
        private FeatureFlagRepository $flags,
        private FeatureFlagRuntime $runtime,
    ) {}

    public function execute(int $id): void
    {
        $existing = $this->flags->findById($id);

        if ($existing === null) {
            throw FeatureFlagNotFound::forId($id);
        }

        try {
            $this->flags->delete($id);
        } catch (ModelNotFoundException) {
            throw FeatureFlagNotFound::forId($id);
        }

        $this->runtime->purge($existing->key);
    }
}
