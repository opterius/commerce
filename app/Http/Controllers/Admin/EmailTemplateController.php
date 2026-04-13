<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('mailable')->orderBy('locale')->get()
            ->groupBy('mailable');

        $mailables = EmailTemplate::mailables();

        return view('admin.email-templates.index', compact('templates', 'mailables'));
    }

    public function create()
    {
        $mailables = EmailTemplate::mailables();
        $locales   = config('commerce.available_locales', ['en' => 'English']);

        return view('admin.email-templates.create', compact('mailables', 'locales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mailable'  => ['required', 'string', Rule::in(array_keys(EmailTemplate::mailables()))],
            'locale'    => ['required', 'string', 'max:10'],
            'subject'   => ['required', 'string', 'max:255'],
            'body'      => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        EmailTemplate::updateOrCreate(
            ['mailable' => $validated['mailable'], 'locale' => $validated['locale']],
            $validated,
        );

        return redirect()->route('admin.email-templates.index')
            ->with('success', __('email_templates.saved'));
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $variables = EmailTemplate::mailables()[$emailTemplate->mailable] ?? [];
        $locales   = config('commerce.available_locales', ['en' => 'English']);

        return view('admin.email-templates.edit', compact('emailTemplate', 'variables', 'locales'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'locale'    => ['required', 'string', 'max:10'],
            'subject'   => ['required', 'string', 'max:255'],
            'body'      => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $emailTemplate->update($validated);

        return redirect()->route('admin.email-templates.index')
            ->with('success', __('email_templates.saved'));
    }

    public function destroy(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $emailTemplate->delete();

        return redirect()->route('admin.email-templates.index')
            ->with('success', __('email_templates.deleted'));
    }
}
