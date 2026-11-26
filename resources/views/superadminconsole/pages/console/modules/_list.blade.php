{{-- Module table. Re-rendered by PlanModuleController after every change. --}}
<div class="table-responsive">
  <table class="table align-middle mb-0 mm-table">
    <thead>
      <tr>
        <th>Module</th>
        <th>Key</th>
        <th>Used in</th>
        <th class="text-center">Active</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($modules as $m)
        @php($used = $m->used_in ?? collect())
        <tr class="js-mod-row"
            data-search="{{ mb_strtolower($m->label . ' ' . $m->key . ' ' . $m->description) }}"
            data-type="{{ $m->is_system ? 'system' : 'custom' }}"
            data-active="{{ $m->is_active ? 1 : 0 }}">
          <td>
            <div class="d-flex align-items-center gap-3">
              <span class="mm-tile"><i class="bi {{ $m->icon }}"></i></span>
              <div>
                <div class="fw-semibold">
                  {{ $m->label }}
                  @if ($m->is_system)<span class="status tone-primary ms-1">Built-in</span>@endif
                </div>
                <div class="caption">{{ $m->description ?: 'No description' }}</div>
              </div>
            </div>
          </td>
          <td><code class="mm-key">{{ $m->key }}</code></td>
          <td>
            @if ($used->count())
              <span class="status tone-primary" title="{{ $used->implode(', ') }}">{{ $used->count() }} {{ Str::plural('plan', $used->count()) }}</span>
            @else
              <span class="caption">Not used</span>
            @endif
          </td>
          <td class="text-center">
            <div class="form-check form-switch d-inline-block m-0">
              <input class="form-check-input js-mod-status" type="checkbox" role="switch"
                     data-url="{{ route('console.modules.status', $m) }}"
                     aria-label="Active: {{ $m->label }}" @checked($m->is_active)>
            </div>
          </td>
          <td class="text-end text-nowrap">
            <button type="button" class="btn btn-sm btn-light border js-mod-open" title="Edit module"
                    data-update-url="{{ route('console.modules.update', $m) }}"
                    data-module="{{ json_encode($m->only(['key', 'label', 'description', 'icon', 'is_active', 'is_system'])) }}">
              <i class="bi bi-pencil"></i>
            </button>
            @if ($m->is_system)
              <button type="button" class="btn btn-sm btn-light border" disabled
                      title="Built-in modules can't be deleted. Switch it off instead."><i class="bi bi-trash"></i></button>
            @else
              <button type="button" class="btn btn-sm btn-light border text-danger js-mod-delete" title="Delete module"
                      data-url="{{ route('console.modules.destroy', $m) }}"
                      data-name="{{ $m->label }}" data-used="{{ $used->count() }}">
                <i class="bi bi-trash"></i>
              </button>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="text-center p-5 d-none js-mod-empty">
  <i class="bi bi-grid-1x2 fs-1 text-muted"></i>
  <h5 class="mt-3">No modules found</h5>
  <p class="caption mb-0">Try a different search, or create a new module.</p>
</div>