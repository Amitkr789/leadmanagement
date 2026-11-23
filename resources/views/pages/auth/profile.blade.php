@extends('layouts.app')
@section('title', 'Dalal Adda | My Profile')
@section('content')
@php
  $initials = collect(preg_split('/\s+/', trim($admin->name)))->filter()->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
  $isSuper  = $admin->role === 'super_admin';
  [$mailName, $mailDomain] = array_pad(explode('@', (string) $admin->email, 2), 2, '');
  $maskedEmail = mb_substr($mailName, 0, 2) . str_repeat('•', max(3, mb_strlen($mailName) - 2)) . '@' . $mailDomain;
@endphp

<style>
  /* ==========================================================================
     My Profile — scoped styles (.pf-*)
     Uses the design tokens from assets/css/style.css
     ========================================================================== */
  .pf-wrap { max-width: 1120px; }

  /* ---- Hero ---- */
  .pf-hero { background: var(--card-bg); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; margin-bottom: 24px; box-shadow: var(--shadow-sm); }
  .pf-hero-cover {
    height: 112px; position: relative; overflow: hidden;
    background:
      radial-gradient(circle at 12% 20%, rgba(255,255,255,.18), transparent 42%),
      radial-gradient(circle at 88% 90%, rgba(255,255,255,.12), transparent 45%),
      linear-gradient(135deg, #2C2580 0%, #4338CA 55%, #6D28D9 100%);
  }
  .pf-hero-cover::after { content: ''; position: absolute; width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,.07); right: -60px; top: -90px; }
  .pf-hero-body { display: flex; align-items: flex-start; gap: 22px; padding: 0 28px 24px; margin-top: -40px; flex-wrap: wrap; }
  .pf-avatar {
    width: 96px; height: 96px; border-radius: 26px; border: 4px solid #fff; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary-light), #818CF8); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; letter-spacing: .02em;
    box-shadow: 0 10px 24px -6px rgba(var(--primary-rgb), .5);
  }
  .pf-hero-info { flex: 1; min-width: 220px; padding-top: 48px; }
  .pf-hero-info h1 { font-size: 22px; margin: 0 0 10px; }
  .pf-chips { display: flex; flex-wrap: wrap; gap: 8px; }
  .pf-chip {
    display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; color: var(--text-muted);
    background: var(--bg); border: 1px solid var(--border); border-radius: 999px; padding: 5px 12px;
  }
  .pf-chip i { font-size: 13px; }
  .pf-chip-role { background: var(--primary-50); color: var(--primary); border-color: var(--primary-100); }

  /* ---- Cards ---- */
  .pf-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 20px; box-shadow: var(--shadow-sm); height: 100%; overflow: hidden; }
  .pf-card-head { display: flex; align-items: center; gap: 14px; padding: 20px 24px; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, #FAFAFF 0%, #fff 70%); }
  .pf-card-icon { width: 42px; height: 42px; border-radius: 12px; background: var(--primary-50); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }
  .pf-card-head h2 { font-size: 16px; margin: 0 0 2px; }
  .pf-card-head p { margin: 0; font-size: 12.5px; color: var(--text-muted); }
  .pf-card-body { padding: 24px; }

  /* ---- Fields ---- */
  .pf-group { margin-bottom: 18px; }
  .pf-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 7px; }
  .pf-field { position: relative; }
  .pf-field .pf-ico { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9AA0B8; font-size: 16px; pointer-events: none; z-index: 2; transition: color .15s; }
  .pf-field:focus-within .pf-ico { color: var(--primary); }
  .pf-field .form-control { height: 46px; padding-left: 42px !important; padding-right: 44px !important; }
  .pf-field .form-control:disabled { background: #F1F2F8 !important; color: var(--text-muted); cursor: not-allowed; }
  .pf-field .pf-lock { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #A7ABC2; font-size: 14px; }
  .pf-eye { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; border: 0; background: transparent; color: var(--text-muted); border-radius: 9px; z-index: 3; display: flex; align-items: center; justify-content: center; }
  .pf-eye:hover { background: var(--primary-50); color: var(--primary); }
  .pf-hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; }
  .pf-error { display: none; font-size: 12px; color: var(--danger); font-weight: 600; margin-top: 6px; }
  .pf-error:not(:empty) { display: block; }
  .pf-group.has-error .pf-field .form-control { border-color: var(--danger) !important; box-shadow: 0 0 0 .2rem rgba(220, 38, 38, .1) !important; }

  /* ---- Actions ---- */
  .pf-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 22px; padding-top: 18px; border-top: 1px dashed var(--border); }
  .pf-dirty { display: none; font-size: 12.5px; font-weight: 600; color: var(--warning); align-items: center; gap: 6px; }
  .pf-dirty.show { display: inline-flex; }
  .pf-btns { display: flex; gap: 10px; margin-left: auto; }
  .pf-btn { height: 46px; border-radius: 12px !important; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 22px; }
  .pf-btn-block { width: 100%; }
  .pf-btn.btn-primary { box-shadow: 0 8px 18px -6px rgba(var(--primary-rgb), .55); }
  .pf-btn.btn-primary:disabled { box-shadow: none; opacity: .5; }
  .pf-link { background: none; border: 0; padding: 0; color: var(--primary); font-weight: 700; font-size: 13px; }
  .pf-link:hover { text-decoration: underline; }
  .pf-link:disabled { color: var(--text-muted); cursor: not-allowed; text-decoration: none; }

  /* ---- Stepper ---- */
  .pf-steps { display: flex; align-items: center; margin-bottom: 24px; }
  .pf-step { display: flex; align-items: center; gap: 8px; color: var(--text-muted); font-size: 12.5px; font-weight: 700; }
  .pf-step-dot { width: 26px; height: 26px; border-radius: 50%; border: 2px solid var(--border); background: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; transition: all .25s; }
  .pf-step-dot i { display: none; font-size: 13px; }
  .pf-step.active { color: var(--primary); }
  .pf-step.active .pf-step-dot { border-color: var(--primary); color: var(--primary); box-shadow: 0 0 0 4px rgba(var(--primary-rgb), .12); }
  .pf-step.done { color: var(--text); }
  .pf-step.done .pf-step-dot { background: var(--primary); border-color: var(--primary); color: #fff; }
  .pf-step.done .pf-step-dot b { display: none; }
  .pf-step.done .pf-step-dot i { display: block; }
  .pf-step-line { flex: 1; height: 2px; background: var(--border); margin: 0 10px; border-radius: 2px; position: relative; overflow: hidden; }
  .pf-step-line::after { content: ''; position: absolute; inset: 0; background: var(--primary); transform: scaleX(0); transform-origin: left; transition: transform .35s ease; }
  .pf-step-line.fill::after { transform: scaleX(1); }

  .pf-panel { display: none; }
  .pf-panel.active { display: block; animation: pfIn .3s ease; }
  @keyframes pfIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

  .pf-note { display: flex; gap: 12px; background: var(--primary-50); border: 1px solid var(--primary-100); border-radius: 12px; padding: 12px 14px; font-size: 12.5px; line-height: 1.55; color: #3730A3; margin-bottom: 20px; }
  .pf-note i { font-size: 16px; margin-top: 1px; }

  /* ---- Password strength ---- */
  .pf-meter { display: flex; gap: 6px; margin-top: 12px; }
  .pf-meter span { flex: 1; height: 5px; border-radius: 4px; background: var(--border); transition: background .25s; }
  .pf-meter[data-level="1"] span:nth-child(-n+1) { background: var(--danger); }
  .pf-meter[data-level="2"] span:nth-child(-n+2) { background: var(--warning); }
  .pf-meter[data-level="3"] span:nth-child(-n+3) { background: var(--info); }
  .pf-meter[data-level="4"] span:nth-child(-n+4) { background: var(--success); }
  .pf-strength { display: flex; justify-content: space-between; font-size: 12px; margin-top: 6px; color: var(--text-muted); }
  .pf-strength b { font-weight: 700; }
  .pf-rules { display: grid; grid-template-columns: 1fr 1fr; gap: 7px 12px; margin: 12px 0 0; padding: 0; list-style: none; }
  .pf-rule { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 7px; transition: color .2s; }
  .pf-rule i { font-size: 14px; color: #C5C8DA; transition: color .2s; }
  .pf-rule.ok { color: var(--success); }
  .pf-rule.ok i { color: var(--success); }
  .pf-match { font-size: 12px; font-weight: 600; margin-top: 6px; display: none; align-items: center; gap: 6px; }
  .pf-match.ok { display: flex; color: var(--success); }
  .pf-match.bad { display: flex; color: var(--danger); }

  /* ---- OTP ---- */
  .pf-mail-ico { width: 64px; height: 64px; border-radius: 20px; background: var(--primary-50); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 14px; }
  .pf-center { text-align: center; }
  .pf-center h3 { font-size: 18px; margin-bottom: 6px; }
  .pf-center p { font-size: 13.5px; color: var(--text-muted); margin-bottom: 0; line-height: 1.6; }
  .pf-center p b { color: var(--text); }
  .otp-row { display: flex; gap: 10px; justify-content: center; margin: 24px 0 8px; }
  .otp-box {
    width: 48px; height: 58px; text-align: center; font-size: 24px; font-weight: 800; color: var(--text);
    border: 1.5px solid var(--border); border-radius: 12px; background: #FBFBFE; outline: none; padding: 0;
    transition: border-color .15s, box-shadow .15s, background .15s, transform .15s; caret-color: var(--primary);
  }
  .otp-box:focus { border-color: var(--primary-light); box-shadow: 0 0 0 .22rem rgba(var(--primary-rgb), .16); background: #fff; transform: translateY(-1px); }
  .otp-box.filled { border-color: var(--primary); background: #fff; }
  .otp-row.has-error .otp-box { border-color: var(--danger); background: var(--danger-bg); }
  .otp-row.shake { animation: pfShake .45s; }
  @keyframes pfShake { 10%, 90% { transform: translateX(-1px); } 20%, 80% { transform: translateX(3px); } 30%, 50%, 70% { transform: translateX(-5px); } 40%, 60% { transform: translateX(5px); } }
  .pf-otp-error { text-align: center; min-height: 20px; font-size: 12.5px; font-weight: 600; color: var(--danger); }
  .pf-otp-meta { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; font-size: 12.5px; color: var(--text-muted); margin: 14px 0 20px; }
  .pf-otp-meta .expired { color: var(--danger); font-weight: 700; }
  .pf-back { margin-top: 16px; text-align: center; }

  /* ---- Success ---- */
  .pf-success { text-align: center; padding: 14px 4px 4px; }
  .pf-check { width: 88px; height: 88px; margin: 0 auto 18px; display: block; }
  .pf-check .c { fill: var(--success-bg); stroke: var(--success); stroke-width: 2; stroke-dasharray: 151; stroke-dashoffset: 151; animation: pfDraw .6s ease forwards; }
  .pf-check .t { fill: none; stroke: var(--success); stroke-width: 3.5; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 40; stroke-dashoffset: 40; animation: pfDraw .4s .5s ease forwards; }
  @keyframes pfDraw { to { stroke-dashoffset: 0; } }
  .pf-success h3 { font-size: 19px; margin-bottom: 6px; }
  .pf-success p { color: var(--text-muted); font-size: 13.5px; margin-bottom: 22px; line-height: 1.6; }

  /* ---- Toasts ---- */
  .pf-toast { background: #fff; border: 1px solid var(--border); border-left: 4px solid var(--primary); border-radius: 12px; box-shadow: var(--shadow-lg); font-size: 13.5px; font-weight: 600; color: var(--text); min-width: 280px; }
  .pf-toast-success { border-left-color: var(--success); }
  .pf-toast-success .pf-toast-ico { color: var(--success); }
  .pf-toast-error { border-left-color: var(--danger); }
  .pf-toast-error .pf-toast-ico { color: var(--danger); }
  .pf-toast-info .pf-toast-ico { color: var(--primary); }
  .pf-toast-ico { font-size: 18px; margin-top: 1px; }
  .pf-btn .spinner-border { width: 1rem; height: 1rem; border-width: .15em; }

  @media (max-width: 575.98px) {
    .pf-hero-body { padding: 0 18px 20px; }
    .pf-card-body { padding: 18px; }
    .pf-card-head { padding: 16px 18px; }
    .pf-rules { grid-template-columns: 1fr; }
    .pf-hero-info { padding-top: 4px; }
    .pf-btns { width: 100%; }
    .pf-btns .pf-btn { flex: 1; }
    .otp-row { gap: 6px; }
    .otp-box { width: 42px; height: 52px; font-size: 21px; }
    .pf-step-label { display: none; }
  }
  @media (prefers-reduced-motion: reduce) {
    .pf-wrap *, .pf-wrap *::before, .pf-wrap *::after { animation: none !important; transition: none !important; }
  }
</style>

<div class="page-content">
  <section class="page-section active" id="page-profile">
    <div class="pf-wrap">

      <!-- ===== Hero ===== -->
      <div class="pf-hero">
        <div class="pf-hero-cover"></div>
        <div class="pf-hero-body">
          <div class="pf-avatar" id="pfAvatar">{{ $initials }}</div>
          <div class="pf-hero-info">
            <h1 id="pfHeroName">{{ $admin->name }}</h1>
            <div class="pf-chips">
              <span class="pf-chip pf-chip-role"><i class="bi bi-shield-check"></i>{{ $isSuper ? 'Super Admin' : 'Admin' }}</span>
              <span class="pf-chip"><i class="bi bi-envelope"></i><span id="pfHeroEmail">{{ $admin->email }}</span></span>
              <span class="pf-chip {{ $admin->phone ? '' : 'd-none' }}" id="pfHeroPhoneChip"><i class="bi bi-telephone"></i><span id="pfHeroPhone">{{ $admin->phone }}</span></span>
              @if($admin->created_at)
                <span class="pf-chip"><i class="bi bi-calendar3"></i>Member since {{ $admin->created_at->format('M Y') }}</span>
              @endif
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">

        <!-- ===== Profile details ===== -->
        <div class="col-lg-6">
          <div class="pf-card">
            <div class="pf-card-head">
              <div class="pf-card-icon"><i class="bi bi-person-vcard"></i></div>
              <div>
                <h2>Profile Details</h2>
                <p>Your name and contact info</p>
              </div>
            </div>
            <form id="profileForm" class="pf-card-body" autocomplete="off">
              <div class="pf-group" data-field="name">
                <label class="pf-label" for="pfName">Full name</label>
                <div class="pf-field">
                  <i class="bi bi-person pf-ico"></i>
                  <input type="text" class="form-control" id="pfName" value="{{ $admin->name }}" placeholder="Your full name" required maxlength="255">
                </div>
                <div class="pf-error" id="err_name"></div>
              </div>

              <div class="pf-group" data-field="email">
                <label class="pf-label" for="pfEmail">Email</label>
                <div class="pf-field">
                  <i class="bi bi-envelope pf-ico"></i>
                  <input type="email" class="form-control" id="pfEmail" value="{{ $admin->email }}" placeholder="you@example.com" required maxlength="255">
                </div>
                <div class="pf-hint">Verification codes are sent to this address.</div>
                <div class="pf-error" id="err_email"></div>
              </div>

              <div class="pf-group" data-field="phone">
                <label class="pf-label" for="pfPhone">Phone</label>
                <div class="pf-field">
                  <i class="bi bi-telephone pf-ico"></i>
                  <input type="text" class="form-control" id="pfPhone" value="{{ $admin->phone }}" placeholder="+91 98765 43210" inputmode="tel" maxlength="20">
                </div>
                <div class="pf-error" id="err_phone"></div>
              </div>

              <div class="pf-group mb-0">
                <label class="pf-label">Role</label>
                <div class="pf-field">
                  <i class="bi bi-shield-check pf-ico"></i>
                  <input type="text" class="form-control" value="{{ $isSuper ? 'Super Admin' : 'Admin' }}" disabled>
                  <i class="bi bi-lock-fill pf-lock"></i>
                </div>
              </div>

              <div class="pf-actions">
                <span class="pf-dirty" id="pfDirty"><i class="bi bi-dot fs-4 m-0"></i>Unsaved changes</span>
                <div class="pf-btns">
                  <button type="button" class="btn btn-outline-secondary pf-btn" id="discardBtn" disabled>Discard</button>
                  <button type="submit" class="btn btn-primary pf-btn" id="saveProfileBtn" disabled><i class="bi bi-check2"></i><span>Save changes</span></button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- ===== Security / password ===== -->
        <div class="col-lg-6">
          <div class="pf-card">
            <div class="pf-card-head">
              <div class="pf-card-icon"><i class="bi bi-shield-lock"></i></div>
              <div>
                <h2>Change Password</h2>
                <p>Protected with email verification</p>
              </div>
            </div>

            <div class="pf-card-body">
              <div class="pf-steps" id="pfSteps">
                <div class="pf-step active" data-step="1"><span class="pf-step-dot"><b>1</b><i class="bi bi-check-lg"></i></span><span class="pf-step-label">Details</span></div>
                <div class="pf-step-line"></div>
                <div class="pf-step" data-step="2"><span class="pf-step-dot"><b>2</b><i class="bi bi-check-lg"></i></span><span class="pf-step-label">Verify</span></div>
                <div class="pf-step-line"></div>
                <div class="pf-step" data-step="3"><span class="pf-step-dot"><b>3</b><i class="bi bi-check-lg"></i></span><span class="pf-step-label">Done</span></div>
              </div>

              <!-- STEP 1 -->
              <div class="pf-panel active" data-panel="1">
                <form id="pwStep1" autocomplete="off" novalidate>
                  <div class="pf-note">
                    <i class="bi bi-envelope-check"></i>
                    <div>For your safety, we'll email a 6-digit code to <b>{{ $maskedEmail }}</b> before your password is changed.</div>
                  </div>

                  <div class="pf-group" data-field="current_password">
                    <label class="pf-label" for="pfCurrentPassword">Current password</label>
                    <div class="pf-field">
                      <i class="bi bi-key pf-ico"></i>
                      <input type="password" class="form-control" id="pfCurrentPassword" placeholder="Enter current password" autocomplete="current-password">
                      <button type="button" class="pf-eye" tabindex="-1" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="pf-error" id="err_current_password"></div>
                  </div>

                  <div class="pf-group" data-field="password">
                    <label class="pf-label" for="pfPassword">New password</label>
                    <div class="pf-field">
                      <i class="bi bi-lock pf-ico"></i>
                      <input type="password" class="form-control" id="pfPassword" placeholder="Create a strong password" autocomplete="new-password">
                      <button type="button" class="pf-eye" tabindex="-1" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="pf-meter" id="pfMeter" data-level="0"><span></span><span></span><span></span><span></span></div>
                    <div class="pf-strength"><span>Password strength</span><b id="pfStrengthLabel">&nbsp;</b></div>
                    <ul class="pf-rules">
                      <li class="pf-rule" data-rule="len"><i class="bi bi-circle"></i>At least 8 characters</li>
                      <li class="pf-rule" data-rule="letter"><i class="bi bi-circle"></i>Contains a letter</li>
                      <li class="pf-rule" data-rule="number"><i class="bi bi-circle"></i>Contains a number</li>
                      <li class="pf-rule" data-rule="diff"><i class="bi bi-circle"></i>Different from current</li>
                    </ul>
                    <div class="pf-error" id="err_password"></div>
                  </div>

                  <div class="pf-group" data-field="password_confirmation">
                    <label class="pf-label" for="pfConfirmPassword">Confirm new password</label>
                    <div class="pf-field">
                      <i class="bi bi-lock-fill pf-ico"></i>
                      <input type="password" class="form-control" id="pfConfirmPassword" placeholder="Re-enter new password" autocomplete="new-password">
                      <button type="button" class="pf-eye" tabindex="-1" aria-label="Show password"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="pf-match" id="pfMatch"></div>
                  </div>

                  <button type="submit" class="btn btn-primary pf-btn pf-btn-block" id="sendOtpBtn" disabled><i class="bi bi-send"></i><span>Send verification code</span></button>
                  <div class="pf-back d-none" id="haveCodeLink"><button type="button" class="pf-link" id="haveCodeBtn">Already have a code? Enter it</button></div>
                </form>
              </div>

              <!-- STEP 2 -->
              <div class="pf-panel" data-panel="2">
                <div class="pf-center">
                  <div class="pf-mail-ico"><i class="bi bi-envelope-open"></i></div>
                  <h3>Check your email</h3>
                  <p>We sent a 6-digit code to<br><b id="maskedEmail">{{ $maskedEmail }}</b></p>
                </div>

                <div class="otp-row" id="otpRow">
                  <input type="text" class="otp-box" inputmode="numeric" autocomplete="one-time-code" aria-label="Digit 1">
                  <input type="text" class="otp-box" inputmode="numeric" autocomplete="off" aria-label="Digit 2">
                  <input type="text" class="otp-box" inputmode="numeric" autocomplete="off" aria-label="Digit 3">
                  <input type="text" class="otp-box" inputmode="numeric" autocomplete="off" aria-label="Digit 4">
                  <input type="text" class="otp-box" inputmode="numeric" autocomplete="off" aria-label="Digit 5">
                  <input type="text" class="otp-box" inputmode="numeric" autocomplete="off" aria-label="Digit 6">
                </div>
                <div class="pf-otp-error" id="err_otp" role="alert"></div>

                <div class="pf-otp-meta">
                  <span id="otpExpiry"><i class="bi bi-clock me-1"></i>Expires in 10:00</span>
                  <button type="button" class="pf-link" id="resendBtn" disabled>Resend code</button>
                </div>

                <button type="button" class="btn btn-primary pf-btn pf-btn-block" id="verifyBtn" disabled><i class="bi bi-shield-check"></i><span>Verify &amp; change password</span></button>
                <div class="pf-back"><button type="button" class="pf-link" id="backBtn"><i class="bi bi-arrow-left me-1"></i>Back to details</button></div>
                <p class="pf-hint text-center mt-3 mb-0">Didn't get it? Check your spam folder. Never share this code with anyone.</p>
              </div>

              <!-- STEP 3 -->
              <div class="pf-panel" data-panel="3">
                <div class="pf-success">
                  <svg class="pf-check" viewBox="0 0 52 52" aria-hidden="true"><circle class="c" cx="26" cy="26" r="24"/><path class="t" d="M14 27l8 8 16-17"/></svg>
                  <h3>Password updated</h3>
                  <p>Your account is now secured with your new password.<br>Use it the next time you sign in.</p>
                  <button type="button" class="btn btn-outline-secondary pf-btn pf-btn-block" id="doneBtn">Done</button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
</div>

<script>
  $(function () {
    'use strict';

    var URL_PROFILE    = '/admin/profile';
    var URL_SEND_OTP   = '/admin/profile/password/send-otp';
    var URL_VERIFY_OTP = '/admin/profile/password/verify-otp';

    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' }
    });

    /* ======================= helpers ======================= */

    function toast(type, message) {
      var icons = { success: 'bi-check-circle-fill', error: 'bi-exclamation-octagon-fill', info: 'bi-info-circle-fill' };
      var $t = $(
        '<div class="toast pf-toast pf-toast-' + type + '" role="alert" aria-live="assertive" aria-atomic="true">' +
          '<div class="d-flex align-items-start gap-2 p-3">' +
            '<i class="bi ' + (icons[type] || icons.info) + ' pf-toast-ico"></i>' +
            '<div class="pf-toast-msg flex-grow-1"></div>' +
            '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>' +
          '</div>' +
        '</div>'
      );
      $t.find('.pf-toast-msg').text(message);
      $('#toastStack').append($t);
      var inst = new bootstrap.Toast($t[0], { delay: type === 'error' ? 6000 : 3800 });
      $t.on('hidden.bs.toast', function () { $t.remove(); });
      inst.show();
    }

    function setBusy($btn, busy, label) {
      if (busy) {
        if (!$btn.data('origHtml')) $btn.data('origHtml', $btn.html());
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>' + label + '</span>');
      } else {
        if ($btn.data('origHtml')) $btn.html($btn.data('origHtml'));
        $btn.removeData('origHtml').prop('disabled', false);
      }
    }

    function showErr(field, msg) {
      $('#err_' + field).text(msg);
      $('.pf-group[data-field="' + field + '"]').addClass('has-error');
    }

    function clearErr($scope) {
      $scope.find('.pf-error').text('');
      $scope.find('.pf-group').removeClass('has-error');
    }

    function handleError(xhr) {
      var j = xhr.responseJSON || {};
      if (xhr.status === 422 && j.errors) {
        Object.keys(j.errors).forEach(function (f) { showErr(f, j.errors[f][0]); });
        return;
      }
      if (xhr.status === 419 || xhr.status === 401) {
        toast('error', 'Your session has expired. Please refresh the page and sign in again.');
        return;
      }
      if (xhr.status === 429) {
        toast('error', j.message || 'Too many requests. Please wait a moment and try again.');
        return;
      }
      toast('error', j.message || 'Something went wrong. Please try again.');
    }

    function fmt(s) {
      return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
    }

    /* ======================= PROFILE FORM ======================= */

    var $name = $('#pfName'), $email = $('#pfEmail'), $phone = $('#pfPhone');

    function currentProfile() {
      return { name: $.trim($name.val()), email: $.trim($email.val()), phone: $.trim($phone.val()) };
    }

    var initial = currentProfile();

    function isDirty() {
      var c = currentProfile();
      return c.name !== initial.name || c.email !== initial.email || c.phone !== initial.phone;
    }

    function refreshDirty() {
      var d = isDirty();
      $('#saveProfileBtn').prop('disabled', !d);
      $('#discardBtn').prop('disabled', !d);
      $('#pfDirty').toggleClass('show', d);
    }

    function refreshHero() {
      var c = currentProfile();
      $('#pfHeroName').text(c.name);
      $('#pfHeroEmail').text(c.email);
      $('#pfHeroPhone').text(c.phone);
      $('#pfHeroPhoneChip').toggleClass('d-none', !c.phone);
      var initials = c.name.split(/\s+/).filter(Boolean).slice(0, 2).map(function (p) { return p.charAt(0).toUpperCase(); }).join('');
      $('#pfAvatar').text(initials);
      $('.sidebar-user .uname').text(c.name);
    }

    $('#profileForm').on('input', 'input', function () {
      var f = $(this).closest('.pf-group').data('field');
      if (f) { $('#err_' + f).text(''); $(this).closest('.pf-group').removeClass('has-error'); }
      refreshDirty();
    });

    $('#discardBtn').on('click', function () {
      $name.val(initial.name); $email.val(initial.email); $phone.val(initial.phone);
      clearErr($('#profileForm'));
      refreshDirty();
    });

    $('#profileForm').on('submit', function (e) {
      e.preventDefault();
      if (!isDirty()) return;
      clearErr($(this));

      var $btn = $('#saveProfileBtn');
      setBusy($btn, true, 'Saving…');

      $.ajax({ url: URL_PROFILE, method: 'PUT', data: currentProfile() })
        .done(function (res) {
          initial = currentProfile();
          refreshHero();
          toast('success', res.message || 'Profile updated.');
        })
        .fail(handleError)
        .always(function () { setBusy($btn, false); refreshDirty(); });
    });

    window.addEventListener('beforeunload', function (e) {
      if (isDirty()) { e.preventDefault(); e.returnValue = ''; }
    });

    refreshDirty();

    /* ======================= PASSWORD: STEP 1 ======================= */

    var rules = {
      len:    function (p) { return p.length >= 8; },
      letter: function (p) { return /[A-Za-z]/.test(p); },
      number: function (p) { return /\d/.test(p); },
      diff:   function (p, c) { return p.length > 0 && p !== c; }
    };

    function updatePasswordUi() {
      var p = $('#pfPassword').val(), c = $('#pfCurrentPassword').val(), r = $('#pfConfirmPassword').val();
      var allRules = true;

      $('.pf-rule').each(function () {
        var ok = rules[$(this).data('rule')](p, c);
        $(this).toggleClass('ok', ok);
        $(this).find('i').toggleClass('bi-check-circle-fill', ok).toggleClass('bi-circle', !ok);
        if (!ok) allRules = false;
      });

      // strength
      var s = 0;
      if (p.length >= 8) s++;
      if (p.length >= 12) s++;
      if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
      if (/\d/.test(p)) s++;
      if (/[^A-Za-z0-9]/.test(p)) s++;
      var level = !p.length ? 0 : (s <= 1 ? 1 : (s === 2 ? 2 : (s === 3 ? 3 : 4)));
      var labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
      var colors = ['', 'var(--danger)', 'var(--warning)', 'var(--info)', 'var(--success)'];
      $('#pfMeter').attr('data-level', level);
      $('#pfStrengthLabel').text(labels[level] || '\u00a0').css('color', colors[level] || '');

      // match
      var $m = $('#pfMatch');
      if (!r) {
        $m.attr('class', 'pf-match').empty();
      } else if (r === p) {
        $m.attr('class', 'pf-match ok').html('<i class="bi bi-check-circle-fill"></i>Passwords match');
      } else {
        $m.attr('class', 'pf-match bad').html('<i class="bi bi-x-circle-fill"></i>Passwords do not match');
      }

      var valid = allRules && c.length > 0 && r === p;
      $('#sendOtpBtn').prop('disabled', !valid);
      return valid;
    }

    $('#pfCurrentPassword, #pfPassword, #pfConfirmPassword').on('input', function () {
      var f = $(this).closest('.pf-group').data('field');
      if (f) { $('#err_' + f).text(''); $(this).closest('.pf-group').removeClass('has-error'); }
      updatePasswordUi();
    });

    $('.pf-eye').on('click', function () {
      var $input = $(this).siblings('input');
      var show = $input.attr('type') === 'password';
      $input.attr('type', show ? 'text' : 'password');
      $(this).find('i').toggleClass('bi-eye', !show).toggleClass('bi-eye-slash', show);
      $(this).attr('aria-label', show ? 'Hide password' : 'Show password');
    });

    function resetPasswordForm() {
      $('#pwStep1')[0].reset();
      $('#pwStep1 input').attr('type', 'password');
      $('.pf-eye i').removeClass('bi-eye-slash').addClass('bi-eye');
      clearErr($('#pwStep1'));
      updatePasswordUi();
    }

    /* ======================= STEPPER ======================= */

    var $boxes = $('.otp-box');

    function showStep(n) {
      $('.pf-panel').removeClass('active').filter('[data-panel="' + n + '"]').addClass('active');
      $('.pf-step').each(function () {
        var s = +$(this).data('step');
        $(this).toggleClass('active', s === n && n < 3).toggleClass('done', s < n || n === 3);
      });
      $('.pf-step-line').each(function (i) { $(this).toggleClass('fill', n > i + 1); });
      if (n === 2) setTimeout(function () { $boxes.eq(0).trigger('focus'); }, 60);
    }

    /* ======================= OTP STATE / TIMERS ======================= */

    var otpExpiresAt = 0, resendAt = 0, timer = null, resending = false, verifying = false;

    function tick() {
      var now = Date.now();
      var left = Math.max(0, Math.ceil((otpExpiresAt - now) / 1000));
      var rl = Math.max(0, Math.ceil((resendAt - now) / 1000));
      var expired = left === 0;

      $('#otpExpiry')
        .toggleClass('expired', expired)
        .html('<i class="bi bi-clock me-1"></i>' + (expired ? 'Code expired' : 'Expires in ' + fmt(left)));

      if (!resending) {
        $('#resendBtn').prop('disabled', rl > 0).text(rl > 0 ? 'Resend code in ' + rl + 's' : 'Resend code');
      }
      $('#haveCodeLink').toggleClass('d-none', expired);
      updateVerifyBtn();

      if (expired && rl === 0) stopTimer();
    }

    function startTimer() { stopTimer(); tick(); timer = setInterval(tick, 1000); }
    function stopTimer() { if (timer) clearInterval(timer); timer = null; }

    function getOtp() { return $boxes.map(function () { return this.value; }).get().join(''); }

    function updateVerifyBtn() {
      $('#verifyBtn').prop('disabled', verifying || getOtp().length !== 6 || Date.now() >= otpExpiresAt);
    }

    function clearOtpError() {
      $('#err_otp').text('');
      $('#otpRow').removeClass('has-error shake');
    }

    function clearOtp() {
      $boxes.val('').removeClass('filled');
      clearOtpError();
      updateVerifyBtn();
    }

    function onOtpSent(res) {
      otpExpiresAt = Date.now() + (res.expires_in || 600) * 1000;
      resendAt = Date.now() + (res.resend_in || 60) * 1000;
      if (res.masked_email) $('#maskedEmail').text(res.masked_email);
      clearOtp();
      startTimer();
    }

    function requestOtp() {
      return $.ajax({
        url: URL_SEND_OTP, method: 'POST',
        data: {
          current_password: $('#pfCurrentPassword').val(),
          password: $('#pfPassword').val(),
          password_confirmation: $('#pfConfirmPassword').val()
        }
      });
    }

    /* Step 1 submit -> send code */
    $('#pwStep1').on('submit', function (e) {
      e.preventDefault();
      clearErr($(this));
      if (!updatePasswordUi()) return;

      var $btn = $('#sendOtpBtn');
      setBusy($btn, true, 'Sending code…');

      requestOtp()
        .done(function (res) { onOtpSent(res); showStep(2); })
        .fail(function (xhr) {
          var j = xhr.responseJSON || {};
          if (xhr.status === 429 && j.retry_after) { resendAt = Date.now() + j.retry_after * 1000; if (otpExpiresAt > Date.now()) startTimer(); }
          handleError(xhr);
        })
        .always(function () { setBusy($btn, false); updatePasswordUi(); });
    });

    $('#haveCodeBtn').on('click', function () { showStep(2); });
    $('#backBtn').on('click', function () { showStep(1); });

    /* Resend */
    $('#resendBtn').on('click', function () {
      if (resending) return;
      resending = true;
      $(this).prop('disabled', true).text('Sending…');

      requestOtp()
        .done(function (res) { onOtpSent(res); $boxes.eq(0).trigger('focus'); toast('success', 'A new code is on its way.'); })
        .fail(function (xhr) {
          var j = xhr.responseJSON || {};
          if (xhr.status === 429 && j.retry_after) resendAt = Date.now() + j.retry_after * 1000;
          var msg = (j.errors && Object.keys(j.errors).length && j.errors[Object.keys(j.errors)[0]][0]) || j.message || 'Could not resend the code. Please try again.';
          toast('error', msg);
        })
        .always(function () { resending = false; startTimer(); });
    });

    /* ======================= OTP INPUTS ======================= */

    function fillOtp(raw) {
      var digits = String(raw).replace(/\D/g, '').slice(0, 6);
      clearOtpError();
      $boxes.each(function (i) {
        this.value = digits.charAt(i) || '';
        $(this).toggleClass('filled', !!this.value);
      });
      $boxes.eq(Math.min(digits.length, 5)).trigger('focus');
      afterOtpChange();
    }

    function afterOtpChange() {
      updateVerifyBtn();
      if (getOtp().length === 6) setTimeout(verifyOtp, 200);
    }

    $boxes.on('input', function () {
      clearOtpError();
      var raw = this.value.replace(/\D/g, '');
      if (raw.length > 2) { fillOtp(raw); return; }   // autofill / SMS-style full code
      this.value = raw.slice(-1);
      $(this).toggleClass('filled', !!this.value);
      if (this.value) $boxes.eq($boxes.index(this) + 1).trigger('focus');
      afterOtpChange();
    });

    $boxes.on('keydown', function (e) {
      var i = $boxes.index(this);
      if (e.key === 'Backspace' && !this.value && i > 0) {
        e.preventDefault();
        $boxes.eq(i - 1).val('').removeClass('filled').trigger('focus');
        updateVerifyBtn();
      } else if (e.key === 'ArrowLeft' && i > 0) {
        e.preventDefault(); $boxes.eq(i - 1).trigger('focus');
      } else if (e.key === 'ArrowRight' && i < 5) {
        e.preventDefault(); $boxes.eq(i + 1).trigger('focus');
      } else if (e.key === 'Enter') {
        e.preventDefault(); verifyOtp();
      }
    });

    $boxes.on('paste', function (e) {
      e.preventDefault();
      var cd = e.originalEvent.clipboardData || window.clipboardData;
      fillOtp(cd.getData('text'));
    });

    $boxes.on('focus', function () { this.select(); });

    /* ======================= VERIFY ======================= */

    function verifyOtp() {
      if (verifying) return;
      var code = getOtp();
      if (code.length !== 6 || Date.now() >= otpExpiresAt) return;

      verifying = true;
      clearOtpError();
      var $btn = $('#verifyBtn');
      setBusy($btn, true, 'Verifying…');

      $.ajax({ url: URL_VERIFY_OTP, method: 'POST', data: { otp: code } })
        .done(function (res) {
          stopTimer();
          otpExpiresAt = 0; resendAt = 0;
          $('#haveCodeLink').addClass('d-none');
          clearOtp();
          resetPasswordForm();
          showStep(3);
          toast('success', res.message || 'Password changed successfully.');
        })
        .fail(function (xhr) {
          var j = xhr.responseJSON || {};
          if (xhr.status === 422 && j.errors && j.errors.otp) {
            $('#err_otp').text(j.errors.otp[0]);
            $('#otpRow').addClass('has-error');
            // restart the shake animation
            var row = document.getElementById('otpRow');
            row.classList.remove('shake'); void row.offsetWidth; row.classList.add('shake');
            $boxes.val('').removeClass('filled');
            $boxes.eq(0).trigger('focus');
            if (j.expired) { otpExpiresAt = 0; tick(); }
          } else {
            handleError(xhr);
          }
        })
        .always(function () { verifying = false; setBusy($btn, false); updateVerifyBtn(); });
    }

    $('#verifyBtn').on('click', verifyOtp);
    $('#doneBtn').on('click', function () { showStep(1); });

    /* ======================= boot ======================= */
    updatePasswordUi();
    setTimeout(updatePasswordUi, 400); // browser autofill
  });
</script>
@endsection