<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopupCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PopupCampaignController extends Controller
{
    public function edit()
    {
        $popup = PopupCampaign::first() ?? new PopupCampaign();

        return view('admin.popup-campaign.edit', compact('popup'));
    }

    public function update(Request $request)
    {
        $popup = PopupCampaign::first() ?? new PopupCampaign();

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($request->hasFile('image')) {
            if ($popup->image) {
                Storage::disk('public')->delete($popup->image);
            }

            $validated['image'] = $request->file('image')->store('popup-campaigns', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['show_once_per_user'] = $request->boolean('show_once_per_user');

        $popup->fill($validated);
        $popup->save();

        return redirect()->back()->with('success', 'تم حفظ الإعلان المنبثق بنجاح.');
    }
}
