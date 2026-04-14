<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Currency;
use App\Models\Faq;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\ProductGroup;
use App\Models\Setting;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function home()
    {
        $currency = Currency::getDefault();

        $groups = ProductGroup::with([
            'products' => fn($q) => $q->where('status', 'active')->orderBy('sort_order'),
            'products.pricing',
        ])
        ->whereHas('products', fn($q) => $q->where('status', 'active'))
        ->orderBy('sort_order')
        ->get();

        return view('portal.home', compact('groups', 'currency'));
    }

    // ── Knowledge Base ─────────────────────────────────────────────────────────

    public function kbIndex(Request $request)
    {
        $this->assertEnabled('portal_show_kb');

        $query = $request->string('q')->trim()->value();

        $categories = KbCategory::with(['publishedArticles' => fn($q) => $q->limit(5)])
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();

        $searchResults = null;
        if ($query) {
            $searchResults = KbArticle::where('is_published', true)
                ->where(fn($q) => $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%"))
                ->with('category')
                ->limit(30)
                ->get();
        }

        return view('portal.kb.index', compact('categories', 'query', 'searchResults'));
    }

    public function kbCategory(KbCategory $category)
    {
        $this->assertEnabled('portal_show_kb');
        abort_if(! $category->is_visible, 404);

        $articles = $category->publishedArticles()->get();

        return view('portal.kb.category', compact('category', 'articles'));
    }

    public function kbArticle(KbCategory $category, KbArticle $article)
    {
        $this->assertEnabled('portal_show_kb');
        abort_if(! $category->is_visible, 404);
        abort_if(! $article->is_published, 404);
        abort_if($article->category_id !== $category->id, 404);

        $article->incrementViews();

        $related = KbArticle::where('category_id', $category->id)
            ->where('is_published', true)
            ->where('id', '!=', $article->id)
            ->orderBy('sort_order')
            ->limit(5)
            ->get();

        return view('portal.kb.article', compact('category', 'article', 'related'));
    }

    // ── FAQ ────────────────────────────────────────────────────────────────────

    public function faq()
    {
        $this->assertEnabled('portal_show_faq');

        $faqs = Faq::where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('portal.faq', compact('faqs'));
    }

    // ── Contact ────────────────────────────────────────────────────────────────

    public function contact()
    {
        $this->assertEnabled('portal_show_contact');

        return view('portal.contact');
    }

    public function contactSubmit(Request $request)
    {
        $this->assertEnabled('portal_show_contact');

        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $data['ip_address'] = $request->ip();

        ContactMessage::create($data);

        return redirect()->route('portal.contact')
            ->with('success', __('contact.message_sent'));
    }

    private function assertEnabled(string $key): void
    {
        $settings = Setting::getGroup('portal');
        abort_if(($settings[$key] ?? '0') !== '1', 404);
    }
}
