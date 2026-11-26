@php($fmap = \App\Models\Plan::featureMap(true))
<div class="row g-3">
    @forelse ($plans as $p)
        <div class="col-md-6 col-xl-4">
            <div class="mp-card h-100 {{ $p->is_active ? '' : 'opacity-75' }}">
                <div class="mp-card-body">

                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h2 class="section-title">{{ $p->name }}</h2>
                            <div class="caption">
                                {{ $p->description ?: 'No description' }}
                            </div>
                        </div>

                        <div class="d-flex gap-1 flex-wrap justify-content-end">
                            @if ($p->is_free)
                                <span class="status tone-success">Free</span>
                            @endif

                            @if ($p->is_popular)
                                <span class="status tone-primary">Popular</span>
                            @endif

                            @unless ($p->is_active)
                                <span class="status tone-warning">Suspended</span>
                            @endunless
                        </div>
                    </div>

                    @if ($p->is_free)
                        <div class="kpi-value">
                            Free
                            <small class="caption fw-normal">forever</small>
                        </div>
                        <div class="caption mb-3">No invoices, never expires</div>
                    @else
                        <div class="kpi-value">
                            ₹{{ number_format($p->monthly_price) }}
                            <small class="caption fw-normal">/month</small>
                        </div>

                        <div class="caption mb-3">
                            or ₹{{ number_format($p->yearly_price) }} /year
                            @if ($p->yearly_savings_months > 0)
                                <span class="text-success fw-medium">· {{ $p->yearly_savings_months + 0 }} months free</span>
                            @endif
                        </div>
                    @endif

                    <ul class="plan-feats">
                        @if ($p->hasTrial())
                            <li><i class="bi bi-gift me-1"></i>{{ $p->trial_days }}-day free trial</li>
                        @endif

                        <li>{{ $p->max_users }} users</li>
                        <li>
                            {{ $p->max_branches }}
                            {{ Str::plural('branch', $p->max_branches) }}
                        </li>

                        @foreach ($p->features ?? [] as $f)
                            <li>{{ $fmap[$f]['label'] ?? $f }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="mp-card-head border-top border-bottom-0 d-flex justify-content-between align-items-center">
                    <span class="caption">
                        <i class="bi bi-buildings me-1"></i>
                        {{ $p->tenants_count ?? 0 }}
                        {{ Str::plural('company', $p->tenants_count ?? 0) }}
                    </span>

                    <div class="dropdown">
                        <button
                            type="button"
                            class="btn btn-sm btn-light border"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Plan options">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item js-plan-open"
                                    data-update-url="{{ route('console.plans.update', $p) }}"
                                    data-plan="{{ json_encode($p->only([
                                        'name',
                                        'description',
                                        'monthly_price',
                                        'yearly_price',
                                        'trial_days',
                                        'max_users',
                                        'max_branches',
                                        'features',
                                        'is_free',
                                        'is_popular',
                                        'is_active',
                                    ])) }}">
                                    <i class="bi bi-pencil me-2"></i>
                                    Edit plan
                                </button>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            @if ($p->is_active)
                                <li>
                                    <button
                                        type="button"
                                        class="dropdown-item text-warning js-plan-status"
                                        data-url="{{ route('console.plans.status', $p) }}"
                                        data-status="suspend"
                                        data-name="{{ $p->name }}">
                                        <i class="bi bi-pause-circle me-2"></i>
                                        Suspend plan
                                    </button>
                                </li>
                            @else
                                <li>
                                    <button
                                        type="button"
                                        class="dropdown-item text-success js-plan-status"
                                        data-url="{{ route('console.plans.status', $p) }}"
                                        data-status="unsuspend"
                                        data-name="{{ $p->name }}">
                                        <i class="bi bi-play-circle me-2"></i>
                                        Unsuspend plan
                                    </button>
                                </li>
                            @endif

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item text-danger js-plan-delete"
                                    data-url="{{ route('console.plans.destroy', $p) }}"
                                    data-name="{{ $p->name }}"
                                    data-used="{{ $p->tenants_count ?? 0 }}">
                                    <i class="bi bi-trash me-2"></i>
                                    Delete plan
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center border rounded-3 p-5">
                <i class="bi bi-tags fs-1 text-muted"></i>
                <h5 class="mt-3">No plans created yet</h5>
                <p class="caption mb-0">Create your first subscription plan.</p>
            </div>
        </div>
    @endforelse

    <div class="col-md-6 col-xl-4 d-flex">
        <button type="button" class="choice-card add-plan js-plan-open">
            <span class="box">
                <i class="bi bi-plus-circle fs-3"></i>
                <strong>Add more plan</strong>
                <span class="caption">Create a custom plan</span>
            </span>
        </button>
    </div>
</div>