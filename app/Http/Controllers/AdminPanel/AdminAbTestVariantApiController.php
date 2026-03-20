<?php

declare(strict_types=1);

namespace App\Http\Controllers\AdminPanel;

use App\Application\AbTesting\CreateAbTestVariantAction;
use App\Application\AbTesting\DeleteAbTestVariantAction;
use App\Application\AbTesting\UpdateAbTestVariantAction;
use App\Domain\AbTesting\Dto\AbTestVariantData;
use App\Domain\AbTesting\ReadModels\AbTestManagementView;
use App\Http\Controllers\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\AdminAbTestManagementResource;
use App\Models\AbTest;
use App\Models\AbTestVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class AdminAbTestVariantApiController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly CreateAbTestVariantAction $createVariant,
        private readonly UpdateAbTestVariantAction $updateVariant,
        private readonly DeleteAbTestVariantAction $deleteVariant,
    ) {}

    public function store(Request $request, AbTest $abTest): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);
        $slug = $this->normalizeSlug($validated['slug'] ?? null, $validated['name']);
        validator(
            ['slug' => $slug],
            ['slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('ab_test_variants', 'slug')->where('ab_test_id', $abTest->id)]],
        )->validate();

        $data = new AbTestVariantData(
            $validated['name'],
            $slug,
            (int) $validated['weight'],
        );

        $view = $this->createVariant->execute($abTest->id, $data);

        return $this->ok($view, 201);
    }

    public function update(Request $request, AbTest $abTest, AbTestVariant $variant): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);
        $slug = array_key_exists('slug', $validated)
            ? $this->normalizeSlug($validated['slug'], $validated['name'])
            : $variant->slug;
        validator(
            ['slug' => $slug],
            ['slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('ab_test_variants', 'slug')->where('ab_test_id', $abTest->id)->ignore($variant->id)]],
        )->validate();

        $data = new AbTestVariantData(
            $validated['name'],
            $slug,
            (int) $validated['weight'],
        );

        $view = $this->updateVariant->execute($abTest->id, $variant->id, $data);

        return $this->ok($view);
    }

    public function destroy(AbTest $abTest, AbTestVariant $variant): JsonResponse
    {
        $view = $this->deleteVariant->execute($abTest->id, $variant->id);

        return $this->ok($view);
    }

    private function ok(AbTestManagementView $view, int $status = 200): JsonResponse
    {
        return $this->respond(new AdminAbTestManagementResource($view), status: $status);
    }

    private function normalizeSlug(?string $slug, string $name): string
    {
        return Str::slug($slug ?: $name, '-', 'uk');
    }
}
