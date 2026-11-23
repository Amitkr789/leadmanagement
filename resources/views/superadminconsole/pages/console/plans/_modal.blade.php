{{-- Create / edit plan. Included on Plans page and Add Company page; behaviour in plan-form.js --}}
<div class="modal fade" id="planModal" tabindex="-1" aria-labelledby="planModalTitle" aria-hidden="true" data-store-url="{{ route('console.plans.store') }}">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <form class="modal-content" id="planForm" novalidate>
      @csrf
      <div class="modal-header"><div><h2 class="h5 mb-0" id="planModalTitle">Create plan</h2><div class="caption">Set pricing, limits and the modules included.</div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body"><div class="row">
        <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="pf_name">Plan name <span class="text-danger">*</span></label><input id="pf_name" name="name" class="form-control" maxlength="60" placeholder="e.g. Growth" required><div class="invalid-feedback"></div></div>
        <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="pf_desc">Short description</label><input id="pf_desc" name="description" class="form-control" maxlength="140" placeholder="Who is this plan for?"><div class="invalid-feedback"></div></div>
        <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="pf_m">Monthly price <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">₹</span><input id="pf_m" name="monthly_price" type="number" min="0" class="form-control" placeholder="2499" required><span class="input-group-text">/ month</span><div class="invalid-feedback"></div></div></div>
        <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="pf_y">Yearly price <span class="text-danger">*</span></label><div class="input-group"><span class="input-group-text">₹</span><input id="pf_y" name="yearly_price" type="number" min="0" class="form-control" placeholder="24990" required><span class="input-group-text">/ year</span><div class="invalid-feedback"></div></div><div class="form-text">Auto-filled as 10 × monthly (2 months free). Edit if needed.</div></div>
        @foreach (['max_users' => ['Max users', 5], 'max_branches' => ['Max branches', 1]] as $name => [$label, $def])
          <div class="col-md-6 mb-3"><label class="form-label fw-medium" for="pf_{{ $name }}">{{ $label }} <span class="text-danger">*</span></label>
            <div class="input-group"><button type="button" class="btn btn-outline-secondary js-step" data-step="-1" aria-label="Decrease">−</button><input id="pf_{{ $name }}" name="{{ $name }}" type="number" min="1" value="{{ $def }}" class="form-control text-center" required><button type="button" class="btn btn-outline-secondary js-step" data-step="1" aria-label="Increase">+</button><div class="invalid-feedback"></div></div></div>
        @endforeach
        <div class="col-12 mb-3"><label class="form-label fw-medium">Included modules</label>
          <div class="d-flex flex-wrap gap-2">@foreach (\App\Models\Plan::FEATURES as $key => $f)<label class="feat"><input type="checkbox" name="features[]" value="{{ $key }}"><span><i class="bi {{ $f['icon'] }}"></i>{{ $f['label'] }}</span></label>@endforeach</div></div>
        <div class="col-md-6 mb-2"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="pf_pop" name="is_popular" value="1"><label class="form-check-label" for="pf_pop">Mark as "Popular"</label></div></div>
        <div class="col-md-6 mb-2"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="pf_act" name="is_active" value="1" checked><label class="form-check-label" for="pf_act">Active (available for new companies)</label></div></div>
      </div></div>
      <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><span class="js-save-label">Create plan</span> <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button></div>
    </form>
  </div>
</div>
