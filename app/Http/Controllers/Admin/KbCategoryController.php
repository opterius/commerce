<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KbCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KbCategoryController extends Controller
{
    public function index()
    {
        $categories = KbCategory::withCount('articles')->orderBy('sort_order')->get();

        return view('admin.kb.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.kb.categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug']       = $this->uniqueSlug($data['slug'] ?: $data['name']);
        $data['is_visible'] = $request->boolean('is_visible', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $cat = KbCategory::create($data);
        ActivityLogger::log('kb_category.created', 'kb_category', $cat->id, $cat->name, null);

        return redirect()->route('admin.kb-categories.index')
            ->with('success', __('kb.category_created', ['name' => $cat->name]));
    }

    public function edit(KbCategory $kbCategory)
    {
        return view('admin.kb.categories.edit', ['category' => $kbCategory]);
    }

    public function update(Request $request, KbCategory $kbCategory)
    {
        $data = $this->validateData($request, $kbCategory->id);
        $data['slug']       = $this->uniqueSlug($data['slug'] ?: $data['name'], $kbCategory->id);
        $data['is_visible'] = $request->boolean('is_visible');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $kbCategory->update($data);
        ActivityLogger::log('kb_category.updated', 'kb_category', $kbCategory->id, $kbCategory->name, null);

        return redirect()->route('admin.kb-categories.index')
            ->with('success', __('kb.category_updated', ['name' => $kbCategory->name]));
    }

    public function destroy(Request $request, KbCategory $kbCategory)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $name = $kbCategory->name;
        $kbCategory->delete();
        ActivityLogger::log('kb_category.deleted', 'kb_category', $kbCategory->id, $name, null);

        return redirect()->route('admin.kb-categories.index')
            ->with('success', __('kb.category_deleted', ['name' => $name]));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0',
            'is_visible'  => 'boolean',
        ]);
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i    = 2;
        while (KbCategory::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
