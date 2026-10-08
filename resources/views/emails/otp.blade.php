<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <title>Pulse Chat Verification Code</title>
</head>

<body style="margin:0; padding:0; background-color:#eef3f8; -webkit-text-size-adjust:100%;">

    {{-- Preview text shown next to the subject in the inbox --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; font-size:1px; line-height:1px; color:#eef3f8;">
        Your Pulse Chat code is {{ $otp }}. It expires in 5 minutes.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef3f8;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:480px; background-color:#ffffff; border-radius:24px; overflow:hidden; box-shadow:0 8px 30px rgba(14,116,160,0.12);">

                    {{-- Header --}}
                    <tr>
                        <td align="center" bgcolor="#0a8fc7"
                            style="background-color:#0a8fc7; background-image:linear-gradient(135deg,#1aa7e8 0%,#0678a8 100%); padding:36px 24px 32px;">

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                                <tr>
                                    <td align="center" width="56" height="56" bgcolor="#ffffff"
                                        style="width:56px; height:56px; background-color:#ffffff; border-radius:16px; font-family:Arial,Helvetica,sans-serif; font-size:28px; font-weight:800; color:#0a8fc7; line-height:56px;">
                                        P
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin:16px 0 0; font-family:Arial,Helvetica,sans-serif; font-size:24px; font-weight:800; color:#ffffff; letter-spacing:0.3px;">
                                Pulse Chat
                            </h1>
                            <p style="margin:6px 0 0; font-family:Arial,Helvetica,sans-serif; font-size:14px; color:#d6f0fb;">
                                Stay close to the people who matter
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td align="center" style="padding:36px 28px 8px;">
                            <h2 style="margin:0 0 10px; font-family:Arial,Helvetica,sans-serif; font-size:22px; font-weight:700; color:#0f2a3d;">
                                Verify your account
                            </h2>
                            <p style="margin:0; font-family:Arial,Helvetica,sans-serif; font-size:15px; line-height:24px; color:#5b6b7a;">
                                Use the code below to verify your Pulse Chat account.
                            </p>
                        </td>
                    </tr>

                    {{-- OTP digits --}}
                    <tr>
                        <td align="center" style="padding:24px 16px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                                <tr>
                                    @foreach (str_split((string) $otp) as $digit)
                                        <td align="center" width="42" height="52" bgcolor="#eaf6fd"
                                            style="width:42px; height:52px; background-color:#eaf6fd; border:1px solid #bfe3f6; border-radius:12px; font-family:'Courier New',Courier,monospace; font-size:28px; font-weight:800; color:#0678a8;">
                                            {{ $digit }}
                                        </td>
                                        @if (! $loop->last)
                                            <td width="6" style="width:6px; font-size:0; line-height:0;">&nbsp;</td>
                                        @endif
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Expiry pill --}}
                    <tr>
                        <td align="center" style="padding:20px 28px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                                <tr>
                                    <td bgcolor="#fff4e5"
                                        style="background-color:#fff4e5; border-radius:999px; padding:8px 16px; font-family:Arial,Helvetica,sans-serif; font-size:13px; font-weight:700; color:#b45f06;">
                                        &#9201; Expires in 5 minutes
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Security note --}}
                    <tr>
                        <td style="padding:24px 28px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td bgcolor="#f6f9fc"
                                        style="background-color:#f6f9fc; border-left:4px solid #1aa7e8; border-radius:10px; padding:14px 16px; font-family:Arial,Helvetica,sans-serif; font-size:13px; line-height:21px; color:#5b6b7a;">
                                        <strong style="color:#0f2a3d;">Keep it private.</strong>
                                        Never share this code with anyone. The Pulse Chat team will never ask for it.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:16px 28px 32px;">
                            <p style="margin:0; font-family:Arial,Helvetica,sans-serif; font-size:13px; line-height:21px; color:#8a97a5;">
                                Didn't request this code? You can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" bgcolor="#f6f9fc"
                            style="background-color:#f6f9fc; padding:20px 24px; border-top:1px solid #e6edf3;">
                            <p style="margin:0; font-family:Arial,Helvetica,sans-serif; font-size:12px; line-height:18px; color:#9aa7b4;">
                                &copy; {{ date('Y') }} Pulse Chat. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>