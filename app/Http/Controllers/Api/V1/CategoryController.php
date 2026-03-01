<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesOrganizationContext;
use App\Http\Requests\Api\V1\Categories\CategoryStoreRequest;
use App\Http\Requests\Api\V1\Categories\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    use ResolvesOrganizationContext;

    public function index(Request $request)
    {
        $organization = $this->organizationFromRequest($request);
        $this->authorize('viewAny', [Category::class, $organization]);

        $query = Category::query()->where('organization_id', $organization->id);

        if ($request->filled('type')) {
            $query->where('type', (string) $request->string('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $categories = $query->orderBy('name')->get();

        return $this->success(CategoryResource::collection($categories));
    }

    public function store(CategoryStoreRequest $request)
    {
        $organization = $this->organizationFromRequest($request);
        $this->authorize('create', [Category::class, $organization]);

        $category = Category::query()->create([
            'organization_id' => $organization->id,
            'type' => (string) $request->string('type'),
            'name' => (string) $request->string('name'),
            'color' => (string) $request->string('color', '#3b82f6'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->success(CategoryResource::make($category), status: 201);
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $organization = $this->organizationFromRequest($request);

        if ($category->organization_id !== $organization->id) {
            return $this->error('Category not found in current organization.', 404);
        }

        $this->authorize('update', $category);

        $category->update($request->validated());

        return $this->success(CategoryResource::make($category));
    }

    public function destroy(Request $request, Category $category)
    {
        $organization = $this->organizationFromRequest($request);

        if ($category->organization_id !== $organization->id) {
            return $this->error('Category not found in current organization.', 404);
        }

        $this->authorize('delete', $category);

        $category->delete();

        return $this->success([
            'deleted_id' => $category->id,
        ]);
    }
}
