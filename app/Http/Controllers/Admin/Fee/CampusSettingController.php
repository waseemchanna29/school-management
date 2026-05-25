<?php

namespace App\Http\Controllers\Admin\Fee;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\CampusSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CampusSettingController extends Controller
{
    public function edit()
    {
        $campus  = Campus::findOrFail(CampusContext::id());
        $setting = CampusSetting::firstOrNew(['campus_id' => $campus->id]);
        return view('admin.fee.settings', compact('campus', 'setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'email'    => ['nullable', 'email'],
            'address'  => ['nullable', 'string', 'max:500'],
            'tagline'  => ['nullable', 'string', 'max:200'],
        ]);

        $setting = CampusSetting::firstOrNew(['campus_id' => CampusContext::id()]);

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $setting->logo = $request->file('logo')->store('campus-logos', 'public');
        }

        $setting->campus_id = CampusContext::id();
        $setting->phone     = $request->phone;
        $setting->email     = $request->email;
        $setting->address   = $request->address;
        $setting->tagline   = $request->tagline;
        $setting->save();

        return back()->with('success', 'Campus settings saved.');
    }

    public function removeLogo()
    {
        $setting = CampusSetting::where('campus_id', CampusContext::id())->first();
        if ($setting && $setting->logo) {
            Storage::disk('public')->delete($setting->logo);
            $setting->update(['logo' => null]);
        }
        return back()->with('success', 'Logo removed.');
    }
}