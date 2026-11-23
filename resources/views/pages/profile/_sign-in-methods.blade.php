{{-- Include inside the profile page:  @include('pages.profile._sign-in-methods')  --}}
@php($me = auth('admin')->user())

<div class="card border mb-4" id="signInMethods">
  <div class="card-body">
    <h2 class="h6 fw-bold mb-1">Sign-in methods</h2>
    <p class="text-muted-2 fs-13 mb-3">Choose how you sign in to your workspace.</p>

    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
      <div>
        <div class="fw-semibold"><i class="bi bi-envelope me-2"></i>Email and password</div>
        <div class="fs-13 text-muted-2">{{ $me->email }}</div>
      </div>
      @if ($me->password)
        <span class="badge text-bg-success">Active</span>
      @else
        <span class="badge text-bg-secondary">Not set</span>
      @endif
    </div>

    @if (config('services.google.enabled'))
      <div class="d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center gap-3">
          @if ($me->avatar)
            <img src="{{ $me->avatar }}" alt="" width="36" height="36" class="rounded-circle" referrerpolicy="no-referrer">
          @else
            <i class="bi bi-google fs-4"></i>
          @endif
          <div>
            <div class="fw-semibold">Google</div>
            @if ($me->google_id)
              <div class="fs-13 text-muted-2">
                Linked {{ optional($me->google_linked_at)->diffForHumans() }}
                @if ($me->last_login_at) · last sign-in {{ $me->last_login_at->diffForHumans() }} @endif
              </div>
            @else
              <div class="fs-13 text-muted-2">
                Not linked yet. Sign in once with Google using {{ $me->email }} and it will be linked automatically.
              </div>
            @endif
          </div>
        </div>

        @if ($me->google_id)
          <button type="button" class="btn btn-sm btn-outline-danger" id="unlinkGoogleBtn"
                  data-url="{{ route('admin.profile.google.unlink') }}"
                  @disabled(! $me->password)
                  @if (! $me->password) title="Set a password before unlinking Google" @endif>
            Unlink
          </button>
        @else
          <span class="badge text-bg-secondary">Not linked</span>
        @endif
      </div>

      @if ($me->google_id && ! $me->password)
        <div class="alert alert-warning fs-13 mb-0">
          Google is your only sign-in method. Set a password before you can unlink it.
        </div>
      @endif
    @endif

    @if ($me->last_login_at)
      <div class="fs-12 text-muted-2 mt-3">
        Last sign-in: {{ $me->last_login_at->format('d M Y, h:i A') }}
        @if ($me->last_login_ip) from {{ $me->last_login_ip }} @endif
      </div>
    @endif
  </div>
</div>

@push('scripts')
<script>
$(function () {
  $('#unlinkGoogleBtn').on('click', function () {
    if (! confirm('Unlink your Google account? You will need your password to sign in.')) return;

    const $btn = $(this).prop('disabled', true);
    $.post($btn.data('url'))
      .done(function (res) {
        showToast('success', res.message || 'Google account unlinked.');
        setTimeout(function () { window.location.reload(); }, 700);
      })
      .fail(function (xhr) {
        showToast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Could not unlink Google.');
        $btn.prop('disabled', false);
      });
  });
});
</script>
@endpush