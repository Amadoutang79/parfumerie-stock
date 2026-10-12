<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index', ['settings' => Setting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'tax_id' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'country' => ['required', Rule::in(array_keys(Setting::COUNTRIES))],
            'description' => ['nullable', 'string', 'max:1500'],
            'currency' => ['required', Rule::in(array_keys(Setting::CURRENCIES))],
            'currency_symbol' => ['required', 'string', 'max:12'],
            'vat_rate' => ['required', 'numeric', 'between:0,100'],
            'timezone' => ['required', Rule::in(Setting::TIMEZONES)],
            'date_format' => ['required', Rule::in(array_keys(Setting::DATE_FORMATS))],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'invoice_email' => ['nullable', 'email', 'max:255'],
            'email_notifications' => ['required', 'boolean'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'tab' => ['nullable', Rule::in(['information', 'preferences', 'appearance'])],
        ], [
            'logo.uploaded' => 'Le téléversement du logo a échoué. Choisissez une image de 5 Mo maximum et réessayez.',
            'logo.mimes' => 'Le logo doit être au format JPG, PNG, GIF ou WEBP.',
            'logo.max' => 'Le logo ne doit pas dépasser 5 Mo.',
        ]);

        $settings = Setting::current();
        $oldLogoPath = $settings->logo_path;
        $logo = $data['logo'] ?? null;
        unset($data['logo'], $data['tab']);

        if ($logo) {
            $logoPath = $logo->store('logos', 'public');

            if (! $logoPath) {
                throw new RuntimeException('Le logo n’a pas pu être enregistré.');
            }

            $data['logo_path'] = $logoPath;
        }

        $settings->update($data);

        if ($logo && $oldLogoPath && $oldLogoPath !== $settings->logo_path) {
            $this->deleteStoredLogo($oldLogoPath);
        }

        return redirect()
            ->route('settings.index', ['tab' => $request->input('tab', 'information')])
            ->with('success', 'Les paramètres de l’entreprise ont été enregistrés.');
    }

    public function destroyLogo(): RedirectResponse
    {
        $settings = Setting::current();
        $logoPath = $settings->logo_path;

        if ($logoPath) {
            $settings->update(['logo_path' => null]);
            $this->deleteStoredLogo($logoPath);
        }

        return redirect()
            ->route('settings.index', ['tab' => 'appearance'])
            ->with('success', 'Le logo a été supprimé.');
    }

    private function deleteStoredLogo(string $path): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($path) && ! $disk->delete($path)) {
            throw new RuntimeException('Le fichier du logo n’a pas pu être supprimé.');
        }
    }
}
