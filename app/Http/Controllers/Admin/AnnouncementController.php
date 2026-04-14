<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderByDesc('published_at')->orderByDesc('id')->paginate(20);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug']         = $this->uniqueSlug($data['slug'] ?: $data['title']);
        $data['published_at'] = $data['published_at'] ?: now();
        $data['is_featured']  = $request->boolean('is_featured');
        $data['show_public']  = $request->boolean('show_public', true);
        $data['show_client']  = $request->boolean('show_client', true);

        $ann = Announcement::create($data);
        ActivityLogger::log('announcement.created', 'announcement', $ann->id, $ann->title, null);

        return redirect()->route('admin.announcements.index')
            ->with('success', __('announcements.created'));
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $this->validateData($request, $announcement->id);
        $data['slug']         = $this->uniqueSlug($data['slug'] ?: $data['title'], $announcement->id);
        $data['published_at'] = $data['published_at'] ?: $announcement->published_at ?: now();
        $data['is_featured']  = $request->boolean('is_featured');
        $data['show_public']  = $request->boolean('show_public');
        $data['show_client']  = $request->boolean('show_client');

        $announcement->update($data);
        ActivityLogger::log('announcement.updated', 'announcement', $announcement->id, $announcement->title, null);

        return redirect()->route('admin.announcements.index')
            ->with('success', __('announcements.updated'));
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $title = $announcement->title;
        $announcement->delete();
        ActivityLogger::log('announcement.deleted', 'announcement', $announcement->id, $title, null);

        return redirect()->route('admin.announcements.index')
            ->with('success', __('announcements.deleted'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'        => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255',
            'content'      => 'required|string',
            'priority'     => 'required|in:' . implode(',', Announcement::PRIORITIES),
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date|after:published_at',
            'is_featured'  => 'boolean',
            'show_public'  => 'boolean',
            'show_client'  => 'boolean',
        ]);
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i    = 2;
        while (Announcement::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
