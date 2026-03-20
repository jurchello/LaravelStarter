<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AbTesting\CreateAbTestAction;
use App\Application\AbTesting\DeleteAbTestAction;
use App\Application\AbTesting\GetAbTestManagementViewAction;
use App\Application\AbTesting\UpdateAbTestAction;
use App\Application\AbTesting\UpdateAbTestStatusAction;
use App\Domain\AbTesting\Dto\AbTestData;
use App\Domain\AbTesting\Enums\AbTestDistributionMode;
use App\Domain\AbTesting\Enums\AbTestStatus;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\AdminAbTestManagementResource;
use App\Models\AbTest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class AdminAbTestManagementApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly GetAbTestManagementViewAction $getTest,
        private readonly CreateAbTestAction $createTest,
        private readonly UpdateAbTestAction $updateTest,
        private readonly DeleteAbTestAction $deleteTest,
        private readonly UpdateAbTestStatusAction $updateStatus,
    ) {}

    public function show(AbTest $abTest): \Illuminate\Http\JsonResponse
    {
        return $this->ok($this->getTest->execute($abTest->id));
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'trafficPercent' => ['required', 'integer', 'between:0,100'],
            'distributionMode' => ['required', Rule::enum(AbTestDistributionMode::class)],
        ]);
        $slug = $this->normalizeSlug($validated['slug'] ?? null, $validated['name']);
        validator(
            ['slug' => $slug],
            ['slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:ab_tests,slug']],
        )->validate();

        $data = new AbTestData(
            $validated['name'],
            $slug,
            (int) $validated['trafficPercent'],
            AbTestDistributionMode::from($validated['distributionMode']),
        );

        $view = $this->createTest->execute($data);

        return $this->ok($view, 201);
    }

    public function update(Request $request, AbTest $abTest): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'trafficPercent' => ['required', 'integer', 'between:0,100'],
            'distributionMode' => ['required', Rule::enum(AbTestDistributionMode::class)],
        ]);
        $slug = array_key_exists('slug', $validated)
            ? $this->normalizeSlug($validated['slug'], $validated['name'])
            : $abTest->slug;
        validator(
            ['slug' => $slug],
            ['slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('ab_tests', 'slug')->ignore($abTest->id)]],
        )->validate();

        $data = new AbTestData(
            $validated['name'],
            $slug,
            (int) $validated['trafficPercent'],
            AbTestDistributionMode::from($validated['distributionMode']),
        );

        $view = $this->updateTest->execute($abTest->id, $data);

        return $this->ok($view);
    }

    public function destroy(AbTest $abTest): \Illuminate\Http\JsonResponse
    {
        $this->deleteTest->execute($abTest->id);

        return $this->respond(null);
    }

    public function updateStatus(Request $request, AbTest $abTest): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(AbTestStatus::class)],
        ]);

        $view = $this->updateStatus->execute($abTest->id, AbTestStatus::from($validated['status']));

        return $this->ok($view);
    }

    private function ok(\App\Domain\AbTesting\ReadModels\AbTestManagementView $view, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return $this->respond(new AdminAbTestManagementResource($view), status: $status);
    }

    private function normalizeSlug(?string $slug, string $name): string
    {
        return Str::slug($slug ?: $name, '-', 'uk');
    }
}
