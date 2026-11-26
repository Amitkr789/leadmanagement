{{-- Create / edit module. Behaviour in module-manager.js --}}
<div class="modal fade" id="moduleModal" tabindex="-1" aria-labelledby="moduleModalTitle" aria-hidden="true"
     data-store-url="{{ route('console.modules.store') }}">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="moduleForm" novalidate>
      @csrf

      <div class="modal-header">
        <div>
          <h2 class="h5 mb-0" id="moduleModalTitle">New module</h2>
          <div class="caption">Name it, pick an icon, and it is ready to add to plans.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label" for="mm_label">Module name <span class="text-danger">*</span></label>
          <input id="mm_label" name="label" class="form-control" maxlength="40" placeholder="e.g. GPS Tracking" autocomplete="off" required>
          <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="mm_desc">Short description</label>
          <input id="mm_desc" name="description" class="form-control" maxlength="120" placeholder="What does this module unlock?">
          <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3 d-none js-mm-key-row">
          <label class="form-label">Key</label>
          <div><code class="mm-key js-mm-key"></code></div>
          <div class="form-text">Fixed. This is what the code checks, so it never changes when you rename the module.</div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="mm_icon">Icon</label>
          <div class="input-group mb-2">
            <span class="input-group-text mm-icon-preview"><i class="bi bi-puzzle"></i></span>
            <input id="mm_icon" name="icon" class="form-control" maxlength="40" value="bi-puzzle" placeholder="bi-puzzle" autocomplete="off">
            <div class="invalid-feedback"></div>
          </div>
          <div class="pf-icons">
            @foreach (\App\Models\PlanModule::ICONS as $icon)
              <button type="button" class="pf-icon-btn" data-icon="{{ $icon }}" aria-label="{{ $icon }}"><i class="bi {{ $icon }}"></i></button>
            @endforeach
          </div>
          <div class="form-text">Pick one, or type any Bootstrap Icons name such as <code>bi-truck</code>.</div>
        </div>

        <div class="form-check form-switch mb-0">
          {{-- The hidden 0 makes an unchecked switch arrive as false --}}
          <input type="hidden" name="is_active" value="0">
          <input class="form-check-input" type="checkbox" role="switch" id="mm_active" name="is_active" value="1" checked>
          <label class="form-check-label" for="mm_active">Active (can be added to new plans)</label>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"><span class="js-save-label">Create module</span> <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
      </div>
    </form>
  </div>
</div>