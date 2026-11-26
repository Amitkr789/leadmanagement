{{-- Plan picker used inside Add Company. Re-rendered by PlanController after a plan is created. --}}
@foreach ($plans as $p)
  <div class="col-md-4">
    <label class="choice-card w-100 h-100">
      <input type="radio" name="plan" value="{{ $p->slug }}"
             data-price="{{ $p->monthly_price }}" data-yearly="{{ $p->yearly_price }}"
             data-label="{{ $p->name }}" data-free="{{ $p->is_free ? 1 : 0 }}" data-trial="{{ $p->trial_days }}"
             @checked($p->slug === ($selected ?? null))>
      <span class="box">
        <span class="d-flex justify-content-between align-items-center">
          <strong>{{ $p->name }}</strong>
          <span class="d-flex gap-1">
            @if ($p->is_free)<span class="status tone-success">Free</span>@endif
            @if ($p->is_popular)<span class="status tone-primary">Popular</span>@endif
          </span>
        </span>
        @if ($p->is_free)
          <span class="price">Free<small> forever</small></span>
        @else
          <span class="price">₹{{ number_format($p->monthly_price) }}<small>/month</small></span>
        @endif
        <ul class="plan-feats">
          @if ($p->hasTrial())<li>{{ $p->trial_days }}-day free trial</li>@endif
          <li>{{ $p->max_users }} users</li>
          <li>{{ $p->max_branches }} {{ Str::plural('branch', $p->max_branches) }}</li>
          <li>{{ count($p->features ?? []) }} modules</li>
        </ul>
      </span>
    </label>
  </div>
@endforeach
<div class="col-md-4">
  <button type="button" class="choice-card add-plan js-plan-open h-100">
    <span class="box"><i class="bi bi-plus-circle fs-3"></i><strong>Add more plan</strong><span class="caption">Create a custom plan</span></span>
  </button>
</div>