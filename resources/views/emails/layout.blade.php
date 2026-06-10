<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'Notification' }}</title>
    <style>
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f4f7fb;margin:0;padding:0}
        .wrapper{padding:30px 15px}
        .container{max-width:580px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08)}
        .header{background:linear-gradient(135deg,#1a0262,#2d1270);padding:28px 32px;text-align:center}
        .header h1{color:#fff;margin:0;font-size:20px;font-weight:700}
        .body{padding:32px}
        .greeting{font-size:16px;color:#1a1a2e;margin:0 0 16px}
        .content{font-size:14px;line-height:1.7;color:#444}
        .content p{margin:0 0 12px}
        .action{margin:24px 0;text-align:center}
        .action a{display:inline-block;background:#1a0262;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600}
        .outro{margin-top:20px;font-size:13px;color:#888;border-top:1px solid #eee;padding-top:16px}
        .footer{text-align:center;padding:20px 32px;font-size:12px;color:#aaa;border-top:1px solid #f0f0f0}
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ $subject ?? 'Notification' }}</h1>
            </div>
            <div class="body">
                @if(!empty($customContent))
                    {!! $customContent !!}
                @else
                    @if(!empty($greeting))
                        <p class="greeting">{{ $greeting }}</p>
                    @endif
                    <div class="content">
                        @if(!empty($introLines))
                            @foreach($introLines as $line)
                                <p>{!! $line !!}</p>
                            @endforeach
                        @endif
                    </div>
                    @if(!empty($actionText) && !empty($actionUrl))
                        <div class="action">
                            <a href="{{ $actionUrl }}">{{ $actionText }}</a>
                        </div>
                    @endif
                    @if(!empty($outroLines))
                        <div class="outro">
                            @foreach($outroLines as $line)
                                <p>{!! $line !!}</p>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
