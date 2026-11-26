{{-- Create / edit plan. Included on Plans page and Add Company page; behaviour in plan-form.js --}}
@php($featureMap = \App\Models\Plan::featureMap())
<div class="modal fade" id="planModal" tabindex="-1" aria-labelledby="planModalTitle" aria-hidden="true"
     data-store-url="{{ route('console.plans.store') }}"
     data-module-url="{{ route('console.modules.store') }}">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <form class="modal-content" id="planForm" novalidate>
      @csrf

      <div class="modal-header">
        <div>
          <h2 class="h5 mb-0" id="planModalTitle">Create plan</h2>
          <div class="caption">Set the price, free period, limits and the modules included.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        {{-- 1. Plan details --}}
        <section class="pf-section">
          <div class="pf-section-head">
            <span class="pf-ico"><i class="bi bi-card-heading"></i></span>
            <div><h3>Plan details</h3></div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="pf_name">Plan name <span class="text-danger">*</span></label>
              <input id="pf_name" name="name" class="form-control" maxlength="60" placeholder="e.g. Growth" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="pf_desc">Short description</label>
              <input id="pf_desc" name="description" class="form-control" maxlength="140" placeholder="Who is this plan for?">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-12">
              <div class="form-label">Plan type</div>
              <div class="pf-segment" role="radiogroup" aria-label="Plan type">
                <label>
                  <input type="radio" name="is_free" value="0" checked>
                  <span><i class="bi bi-currency-rupee"></i>Paid</span>
                </label>
                <label>
                  <input type="radio" name="is_free" value="1">
                  <span><i class="bi bi-gift"></i>Free</span>
                </label>
              </div>
            </div>
          </div>
        </section>

        {{-- 2. Pricing --}}
        <section class="pf-section">
          <div class="pf-section-head">
            <span class="pf-ico"><i class="bi bi-wallet2"></i></span>
            <div><h3>Pricing</h3><div class="caption js-paid-only">Yearly price is worked out from the free months you give. You can still type your own.</div></div>
          </div>

          <div class="pf-free-note js-free-only d-none">
            <i class="bi bi-check-circle-fill"></i>
            <div><strong>This plan is free.</strong> Companies on it never get an invoice and never expire. Limits and modules below still apply.</div>
          </div>

          <div class="js-paid-only">
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label" for="pf_m">Monthly price <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input id="pf_m" name="monthly_price" type="number" min="0" class="form-control" placeholder="2499" required>
                  <span class="input-group-text">/ mo</span>
                  <div class="invalid-feedback"></div>
                </div>
              </div>

              <div class="col-md-4 mb-3">
                <label class="form-label" for="pf_free_months">Free months on yearly</label>
                <div class="input-group">
                  <button type="button" class="btn btn-outline-secondary js-step" data-step="-1" aria-label="Fewer free months">−</button>
                  <input id="pf_free_months" type="number" min="0" max="11" value="2" class="form-control text-center">
                  <button type="button" class="btn btn-outline-secondary js-step" data-step="1" aria-label="More free months">+</button>
                </div>
                <div class="form-text">Customer pays for <strong class="js-paid-months">10</strong> of 12 months.</div>
              </div>

              <div class="col-md-4 mb-3">
                <label class="form-label" for="pf_y">Yearly price <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input id="pf_y" name="yearly_price" type="number" min="0" class="form-control" placeholder="24990" required>
                  <span class="input-group-text">/ yr</span>
                  <div class="invalid-feedback"></div>
                </div>
                <div class="form-text"><button type="button" class="btn btn-link p-0 small d-none js-yearly-auto">Custom price. Reset to auto</button></div>
              </div>
            </div>

            <div class="pf-summary"><i class="bi bi-lightning-charge-fill"></i><span class="js-price-summary"></span></div>
          </div>
        </section>

        {{-- 3. Free trial (paid plans only) --}}
        <section class="pf-section js-paid-only">
          <div class="pf-section-head">
            <span class="pf-ico"><i class="bi bi-hourglass-split"></i></span>
            <div><h3>Free trial</h3><div class="caption">How long a new company can use this plan before paying.</div></div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <button type="button" class="pf-chip" data-days="0">No trial</button>
            <button type="button" class="pf-chip" data-days="7">7 days</button>
            <button type="button" class="pf-chip" data-days="14">14 days</button>
            <button type="button" class="pf-chip" data-days="30">30 days</button>
            <button type="button" class="pf-chip" data-days="60">60 days</button>
            <div class="input-group pf-trial-input">
              <input name="trial_days" type="number" min="0" max="365" value="0" class="form-control text-center" aria-label="Custom trial days">
              <span class="input-group-text">days</span>
              <div class="invalid-feedback"></div>
            </div>
          </div>
          <div class="caption js-trial-hint"></div>
        </section>

        {{-- 4. Limits --}}
        <section class="pf-section">
          <div class="pf-section-head">
            <span class="pf-ico"><i class="bi bi-sliders"></i></span>
            <div><h3>Limits</h3></div>
          </div>
          <div class="row">
            @foreach (['max_users' => ['Max users', 5], 'max_branches' => ['Max branches', 1]] as $name => [$label, $def])
              <div class="col-md-6 mb-3">
                <label class="form-label" for="pf_{{ $name }}">{{ $label }} <span class="text-danger">*</span></label>
                <div class="input-group">
                  <button type="button" class="btn btn-outline-secondary js-step" data-step="-1" aria-label="Decrease">−</button>
                  <input id="pf_{{ $name }}" name="{{ $name }}" type="number" min="1" value="{{ $def }}" class="form-control text-center" required>
                  <button type="button" class="btn btn-outline-secondary js-step" data-step="1" aria-label="Increase">+</button>
                  <div class="invalid-feedback"></div>
                </div>
              </div>
            @endforeach
          </div>
        </section>

        {{-- 5. Modules --}}
        <section class="pf-section">
          <div class="pf-section-head">
            <span class="pf-ico"><i class="bi bi-grid-1x2"></i></span>
            <div class="flex-grow-1"><h3>Included modules</h3><div class="caption">Tick what this plan unlocks. Missing one? Create it here.</div></div>
            <span class="status tone-primary js-mod-count">0 selected</span>
          </div>

          <div class="d-flex flex-wrap gap-2 mb-2" id="pfModules">
            @foreach ($featureMap as $key => $f)
              <label class="feat {{ ! empty($f['custom']) ? 'is-custom' : '' }}">
                <input type="checkbox" name="features[]" value="{{ $key }}">
                <span><i class="bi {{ $f['icon'] }}"></i>{{ $f['label'] }}</span>
                @if (! empty($f['custom']))
                  <button type="button" class="pf-chip-del js-mod-del" title="Delete module" aria-label="Delete {{ $f['label'] }}"
                          data-label="{{ $f['label'] }}" data-url="{{ route('console.modules.destroy', $f['id']) }}"><i class="bi bi-x"></i></button>
                @endif
              </label>
            @endforeach
          </div>

          <div class="pf-mod-tools">
            <button type="button" class="btn btn-sm btn-link js-mod-all">Select all</button>
            <button type="button" class="btn btn-sm btn-link js-mod-none">Clear</button>
            <a href="{{ route('console.modules.index') }}" class="btn btn-sm btn-link" target="_blank" rel="noopener">Manage modules</a>
            <button type="button" class="btn btn-sm btn-light border ms-auto js-mod-new-toggle" aria-expanded="false">
              <i class="bi bi-plus-lg me-1"></i>New module
            </button>
          </div>

          <div class="pf-mod-new d-none js-mod-new">
            <div class="row g-2 align-items-start">
              <div class="col-md-8">
                <label class="form-label" for="pf_mod_label">Module name</label>
                <input id="pf_mod_label" class="form-control" maxlength="40" placeholder="e.g. GPS Tracking" autocomplete="off">
                <div class="invalid-feedback js-mod-error"></div>
              </div>
              <div class="col-md-4 d-grid align-self-end">
                <button type="button" class="btn btn-primary js-mod-add">Add module</button>
              </div>
            </div>
            <div class="form-label mt-3 mb-2">Icon</div>
            <div class="pf-icons">
              @foreach (\App\Models\PlanModule::ICONS as $icon)
                <button type="button" class="pf-icon-btn {{ $loop->first ? 'active' : '' }}" data-icon="{{ $icon }}" aria-label="{{ $icon }}"><i class="bi {{ $icon }}"></i></button>
              @endforeach
            </div>
          </div>
        </section>

        {{-- 6. Visibility --}}
        <section class="pf-section mb-0">
          <div class="row">
            <div class="col-md-6 mb-2"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="pf_pop" name="is_popular" value="1"><label class="form-check-label" for="pf_pop">Mark as "Popular"</label></div></div>
            <div class="col-md-6 mb-2"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="pf_act" name="is_active" value="1" checked><label class="form-check-label" for="pf_act">Active (available for new companies)</label></div></div>
          </div>
        </section>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"><span class="js-save-label">Create plan</span> <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span></button>
      </div>
    </form>
  </div>
</div>