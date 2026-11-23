<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verification code</title>
</head>
<body style="margin:0;padding:0;background:#F5F6FB;font-family:Inter,-apple-system,'Segoe UI',Arial,sans-serif;color:#1E2233;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F6FB;">
  <tr>
    <td align="center" style="padding:32px 16px;">
      <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="max-width:480px;width:100%;background:#FFFFFF;border:1px solid #E7E8F0;border-radius:18px;overflow:hidden;">

        <!-- Header -->
        <tr>
          <td style="background:#4338CA;background-image:linear-gradient(135deg,#2C2580 0%,#4338CA 55%,#6D28D9 100%);padding:26px 32px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.18);text-align:center;color:#fff;font-weight:800;font-size:16px;line-height:36px;">
                  {{ mb_strtoupper(mb_substr(config('app.name', 'L'), 0, 1)) }}
                </td>
                <td style="padding-left:12px;color:#FFFFFF;font-weight:700;font-size:17px;">
                  {{ config('app.name') }}
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:32px 32px 8px;">
            <h1 style="margin:0 0 10px;font-size:21px;line-height:1.3;color:#1E2233;">Verify your password change</h1>
            <p style="margin:0;font-size:14.5px;line-height:1.65;color:#5B6078;">
              Hi {{ $name }}, use the code below to confirm the password change on your account.
            </p>
          </td>
        </tr>

        <tr>
          <td align="center" style="padding:22px 32px 8px;">
            <div style="background:#EEF2FF;border:1px dashed #C7D2FE;border-radius:14px;padding:20px 12px;">
              <div style="font-size:36px;font-weight:800;letter-spacing:12px;color:#4338CA;padding-left:12px;font-family:'SFMono-Regular',Consolas,'Courier New',monospace;">{{ $otp }}</div>
            </div>
            <p style="margin:12px 0 0;font-size:12.5px;color:#767B91;">
              This code expires in <strong style="color:#1E2233;">{{ $minutes }} minutes</strong>.
            </p>
          </td>
        </tr>

        <tr>
          <td style="padding:18px 32px 30px;">
            <div style="background:#FEF3E2;border:1px solid #FBE3BC;border-radius:12px;padding:13px 15px;font-size:12.5px;line-height:1.6;color:#92400E;">
              <strong>Didn't request this?</strong> Someone may know your password. Ignore this email and change your password right away. Never share this code with anyone &mdash; our team will never ask for it.
            </div>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:18px 32px;background:#FAFAFD;border-top:1px solid #E7E8F0;font-size:12px;color:#9AA0B8;text-align:center;">
            Sent by {{ config('app.name') }} &middot; This is an automated message, please do not reply.
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>