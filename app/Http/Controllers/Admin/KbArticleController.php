<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KbArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = KbArticle::with('category');

        if ($categoryId = $request->integer('category')) {
            $query->where('category_id', $categoryId);
        }
        if ($search = $request->string('q')->trim()->value()) {
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%"));
        }

        $articles = $query->orderBy('sort_order')->orderByDesc('id')->paginate(20)->withQueryString();

        $categories = KbCategory::orderBy('sort_order')->get();

        return view('admin.kb.articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $categories = KbCategory::orderBy('sort_order')->get();

        return view('admin.kb.articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug']         = $this->uniqueSlug($data['slug'] ?: $data['title']);
        $data['is_published'] = $request->boolean('is_published', true);
        $data['sort_order']   = $data['sort_order'] ?? 0;

        $article = KbArticle::create($data);
        ActivityLogger::log('kb_article.created', 'kb_article', $article->id, $article->title, null);

        return redirect()->route('admin.kb-articles.index')
            ->with('success', __('kb.article_created', ['title' => $article->title]));
    }

    public function edit(KbArticle $kbArticle)
    {
        $categories = KbCategory::orderBy('sort_order')->get();

        return view('admin.kb.articles.edit', ['article' => $kbArticle, 'categories' => $categories]);
    }

    public function update(Request $request, KbArticle $kbArticle)
    {
        $data = $this->validateData($request, $kbArticle->id);
        $data['slug']         = $this->uniqueSlug($data['slug'] ?: $data['title'], $kbArticle->id);
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order']   = $data['sort_order'] ?? 0;

        $kbArticle->update($data);
        ActivityLogger::log('kb_article.updated', 'kb_article', $kbArticle->id, $kbArticle->title, null);

        return redirect()->route('admin.kb-articles.index')
            ->with('success', __('kb.article_updated', ['title' => $kbArticle->title]));
    }

    public function destroy(Request $request, KbArticle $kbArticle)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $title = $kbArticle->title;
        $kbArticle->delete();
        ActivityLogger::log('kb_article.deleted', 'kb_article', $kbArticle->id, $title, null);

        return redirect()->route('admin.kb-articles.index')
            ->with('success', __('kb.article_deleted', ['title' => $title]));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category_id'  => 'nullable|exists:kb_categories,id',
            'title'        => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255',
            'excerpt'      => 'nullable|string|max:500',
            'content'      => 'required|string',
            'sort_order'   => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i    = 2;
        while (KbArticle::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
