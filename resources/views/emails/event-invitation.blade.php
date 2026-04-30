<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>You're Invited</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333333;
            padding: 40px 16px;
        }

        .wrapper {
            max-width: 600px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
            border-radius: 12px 12px 0 0;
            padding: 40px 40px 32px;
            text-align: center;
        }

        .header .brand {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #e94560;
            margin-bottom: 16px;
        }

        .header h1 {
            font-size: 26px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
        }

        /* Body card */
        .card {
            background: #ffffff;
            padding: 40px;
            border-left: 1px solid #e8eaf0;
            border-right: 1px solid #e8eaf0;
        }

        .greeting {
            font-size: 16px;
            color: #555;
            margin-bottom: 24px;
        }

        .greeting strong {
            color: #1a1a2e;
        }

        /* Event detail block */
        .event-block {
            background: #f8f9fc;
            border-left: 4px solid #e94560;
            border-radius: 0 8px 8px 0;
            padding: 20px 24px;
            margin-bottom: 32px;
        }

        .event-block .event-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .event-block .event-meta {
            font-size: 14px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
        }

        .event-block .event-meta span.label {
            font-weight: 600;
            color: #444;
            min-width: 70px;
            display: inline-block;
        }

        .body-text {
            font-size: 15px;
            line-height: 1.7;
            color: #555;
            margin-bottom: 32px;
        }

        /* CTA Button */
        .cta-wrapper {
            text-align: center;
            margin-bottom: 32px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #e94560, #c0392b);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 16px;
            font-weight: 700;
            padding: 16px 40px;
            border-radius: 50px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(233, 69, 96, 0.35);
        }

        .fallback-link {
            font-size: 12px;
            color: #999;
            text-align: center;
            margin-bottom: 24px;
            word-break: break-all;
        }

        .fallback-link a {
            color: #e94560;
        }

        /* Footer */
        .footer {
            background: #f8f9fc;
            border: 1px solid #e8eaf0;
            border-top: none;
            border-radius: 0 0 12px 12px;
            padding: 24px 40px;
            text-align: center;
        }

        .footer p {
            font-size: 12px;
            color: #aaa;
            line-height: 1.6;
        }

        .footer .org-name {
            font-weight: 600;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- Header -->
        <div class="header">
            <div class="brand">{{ $organizer }}</div>
            <h1>You're Invited! 🎉</h1>
        </div>

        <!-- Body -->
        <div class="card">
            <p class="greeting">
                Dear <strong>{{ $staffName }}</strong>,
            </p>

            <div class="event-block">
                <div class="event-title">{{ $eventTitle }}</div>
                <div class="event-meta">
                    <span class="label">📅 Date</span>
                    <span>{{ $eventDate }}</span>
                </div>
                <div class="event-meta">
                    <span class="label">🏢 Host</span>
                    <span>{{ $organizer }}</span>
                </div>
            </div>

            <p class="body-text">
                We are delighted to invite you to attend <strong>{{ $eventTitle }}</strong>.
                Please confirm your attendance by clicking the button below so we can
                better prepare for your arrival.
            </p>

            <div class="cta-wrapper">
                <a href="{{ $rsvpUrl }}" class="cta-button">Confirm Attendance</a>
            </div>

            <p class="fallback-link">
                If the button above does not work, copy and paste this link into your browser:<br />
                <a href="{{ $rsvpUrl }}">{{ $rsvpUrl }}</a>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                This invitation was sent by <span class="org-name">{{ $organizer }}</span>.<br />
                If you believe this was sent in error, please disregard this email.
            </p>
        </div>

    </div>
</body>
</html>
