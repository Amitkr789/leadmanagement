<?php
namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanModule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlanModuleController extends Controller
{
    public function index()
    {
        return view('superadminconsole.pages.console.modules.index', ['modules' => $this->rows()]);
    }

    public function store(Request $r)
    {
        $d = $this->validated($r);

        // Key is generated once and never changes: "GPS Tracking" -> gps_tracking
        $base = Str::slug($d['label'], '_') ?: 'module';
        $key = $base;
        $i = 2;
        while (isset(Plan::FEATURES[$key]) || PlanModule::where('key', $key)->exists()) {
            $key = $base . '_' . $i++;
        }

        $module = PlanModule::create([
            'key' => $key,
            'label' => $d['label'],
            'description' => $d['description'] ?? null,
            'icon' => $d['icon'] ?? 'bi-puzzle',
            // The plan modal's quick-create sends no is_active: new modules start active.
            'is_active' => $r->has('is_active') ? $r->boolean('is_active') : true,
            'is_system' => false,
            'sort_order' => (PlanModule::max('sort_order') ?? 0) + 1,
        ]);

        return $this->respond('Module "' . $module->label . '" created.', 201, [
            'module' => [
                'key' => $module->key,
                'label' => $module->label,
                'icon' => $module->icon,
                'destroy_url' => route('console.modules.destroy', $module),
            ],
        ]);
    }

    public function update(Request $r, PlanModule $module)
    {
        $d = $this->validated($r, $module);

        $module->update([
            'label' => $d['label'],
            'description' => $d['description'] ?? null,
            'icon' => $d['icon'] ?? $module->icon,
            'is_active' => $r->boolean('is_active'),
        ]);

        return $this->respond('Module updated.');
    }

    public function status(PlanModule $module)
    {
        $module->update(['is_active' => ! $module->is_active]);

        return $this->respond($module->is_active ? 'Module activated.' : 'Module switched off. Existing plans keep it.');
    }

    public function destroy(PlanModule $module)
    {
        if ($module->is_system) {
            return response()->json(['message' => 'Built-in modules cannot be deleted. Switch it off instead.'], 422);
        }

        $plans = $module->plansUsing();
        if ($plans->isNotEmpty()) {
            return response()->json([
                'message' => 'Used by ' . $plans->implode(', ') . '. Remove it from those plans first.',
            ], 422);
        }

        $module->delete();

        return $this->respond('Module deleted.');
    }

    private function validated(Request $r, ?PlanModule $module = null): array
    {
        $d = $r->validate([
            'label' => 'required|string|max:40',
            'description' => 'nullable|string|max:120',
            'icon' => ['nullable', 'string', 'max:40', 'regex:/^bi-[a-z0-9-]+$/'],
        ]);

        $d['label'] = trim($d['label']);

        $taken = PlanModule::whereRaw('LOWER(label) = ?', [mb_strtolower($d['label'])])
            ->when($module, fn ($q) => $q->where('id', '!=', $module->id))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['label' => 'A module with this name already exists.']);
        }

        return $d;
    }

    private function rows()
    {
        $plans = Plan::get(['id', 'name', 'features']);

        return PlanModule::orderBy('sort_order')->orderBy('id')->get()->each(function ($m) use ($plans) {
            $m->used_in = $plans
                ->filter(fn ($p) => in_array($m->key, $p->features ?? [], true))
                ->pluck('name')->values();
        });
    }

    private function respond(string $message, int $code = 200, array $extra = [])
    {
        return response()->json([
            'message' => $message,
            'list' => view('superadminconsole.pages.console.modules._list', ['modules' => $this->rows()])->render(),
        ] + $extra, $code);
    }
}