<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['is_published'] = $request->boolean('is_published', true);
        $data['sort_order']   = $data['sort_order'] ?? 0;

        $faq = Faq::create($data);
        ActivityLogger::log('faq.created', 'faq', $faq->id, $faq->question, null);

        return redirect()->route('admin.faqs.index')->with('success', __('faq.created'));
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $this->validateData($request);
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order']   = $data['sort_order'] ?? 0;

        $faq->update($data);
        ActivityLogger::log('faq.updated', 'faq', $faq->id, $faq->question, null);

        return redirect()->route('admin.faqs.index')->with('success', __('faq.updated'));
    }

    public function destroy(Request $request, Faq $faq)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $question = $faq->question;
        $faq->delete();
        ActivityLogger::log('faq.deleted', 'faq', $faq->id, $question, null);

        return redirect()->route('admin.faqs.index')->with('success', __('faq.deleted'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'question'     => 'required|string|max:500',
            'answer'       => 'required|string',
            'sort_order'   => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);
    }
}
