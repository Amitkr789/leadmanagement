<div class="row g-3">
  @foreach ($plans as $p)
    <div class="col-md-6 col-xl-4"><div class="mp-card h-100 {{ $p->is_active ? '' : 'opacity-75' }}">
      <div class="mp-card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div><h2 class="section-title">{{ $p->name }}</h2><div class="caption">{{ $p->description ?: 'No description' }}</div></div>
          <div class="d-flex gap-1">@if ($p->is_popular)<span class="status tone-primary">Popular</span>@endif @unless ($p->is_active)<span class="status tone-warning">Inactive</span>@endunless</div>
        </div>
        <div class="kpi-value">₹{{ number_format($p->monthly_price) }}<small class="caption fw-normal"> /month</small></div>
        <div class="caption mb-3">or ₹{{ number_format($p->yearly_price) }} /year</div>
        <ul class="plan-feats"><li>{{ $p->max_users }} users</li><li>{{ $p->max_branches }} {{ Str::plural('branch', $p->max_branches) }}</li>
          @foreach ($p->features ?? [] as $f)<li>{{ \App\Models\Plan::FEATURES[$f]['label'] ?? $f }}</li>@endforeach</ul>
      </div>
      <div class="mp-card-head border-top border-bottom-0">
        <span class="caption"><i class="bi bi-buildings me-1"></i>{{ $p->companies_count }} {{ Str::plural('company', $p->companies_count) }}</span>
        <button type="button" class="btn btn-sm btn-light border js-plan-open" data-update-url="{{ route('console.plans.update', $p) }}"
          data-plan="{{ json_encode($p->only(['name', 'description', 'monthly_price', 'yearly_price', 'max_users', 'max_branches', 'features', 'is_popular', 'is_active'])) }}"><i class="bi bi-pencil me-1"></i>Edit</button>
      </div>
    </div></div>
  @endforeach
  <div class="col-md-6 col-xl-4 d-flex"><button type="button" class="choice-card add-plan js-plan-open"><span class="box"><i class="bi bi-plus-circle fs-3"></i><strong>Add more plan</strong><span class="caption">Create a custom plan</span></span></button></div>
</div>
