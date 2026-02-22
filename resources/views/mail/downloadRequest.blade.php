{{-- resources/views/emails/pdf-download-request.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your PDF Download is Ready</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .header-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .header-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }
        .content {
            padding: 40px 30px;
        }
        .download-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 25px 0;
            text-align: center;
            border: 1px solid #bae6fd;
        }
        .file-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 12px;
        }
        .file-icon {
            background: #2563eb;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .file-details {
            text-align: left;
        }
        .file-name {
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        .file-size {
            color: #64748b;
            font-size: 14px;
            margin: 5px 0 0;
        }
        .download-button {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            color: white;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 18px;
            margin: 20px 0;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
            transition: transform 0.2s;
        }
        .download-button:hover {
            transform: translateY(-2px);
        }
        .expiry-notice {
            background: #fff7ed;
            border-left: 4px solid #f97316;
            padding: 15px;
            border-radius: 8px;
            margin: 25px 0;
            font-size: 14px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 30px 0;
        }
        .feature-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .feature-icon {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }
        .feature-text {
            font-size: 13px;
            color: #475569;
            margin: 0;
        }
        .footer {
            background: #f1f5f9;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .help-text {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
        hr {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 25px 0;
        }
        .text-small {
            font-size: 13px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <div class="header-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                </svg>
            </div>
            <h1 style="color: white; margin: 0; font-size: 28px;">Your Download is Ready!</h1>
            <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 16px;">Your requested PDF is now available</p>
        </div>
        
        <div class="content">
            <h2 style="margin-top: 0; color: #1e293b;">Hello {{ $userName ?? 'there' }}! 👋</h2>
            
            <p style="font-size: 16px; color: #475569;">
                Thank you for your interest in <strong>{{ $pdfTitle ?? 'our resource' }}</strong>. 
                We've prepared your download link below.
            </p>
            
            <div class="download-card">
                <div class="file-info">
                    <div class="file-icon">📄</div>
                    <div class="file-details">
                        <p class="file-name">{{ $pdfTitle ?? 'Guide-to-Success.pdf' }}</p>
                        <p class="file-size">{{ $fileSize ?? '2.5 MB' }} • PDF</p>
                    </div>
                </div>
                
                <a href="{{ $downloadUrl ?? '#' }}" class="download-button" target="_blank">
                    ⬇️ Download PDF Now
                </a>
                
                <p style="color: #475569; margin: 15px 0 0; font-size: 14px;">
                    Link expires in {{ $expiryHours ?? '24' }} hours
                </p>
            </div>
            
            @if(isset($bonusContent))
            <div class="expiry-notice">
                <strong>✨ Bonus Content Included:</strong> {{ $bonusContent }}
            </div>
            @endif
            
            <h3 style="color: #1e293b; margin: 30px 0 15px;">What's inside this PDF?</h3>
            
            <div class="features-grid">
                <div class="feature-item">
                    <span class="feature-icon">📊</span>
                    <p class="feature-text">Proven Strategies</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">⚡</span>
                    <p class="feature-text">Actionable Tips</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📋</span>
                    <p class="feature-text">Checklists</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🎯</span>
                    <p class="feature-text">Case Studies</p>
                </div>
            </div>
            
            <hr>
            
            <div class="help-text">
                <p style="margin: 0 0 10px; font-weight: 600;">⚠️ Having trouble downloading?</p>
                <p style="margin: 0; font-size: 14px; color: #475569;">
                    1. Try right-clicking and selecting "Save link as"<br>
                    2. Check your spam folder if you haven't received the email<br>
                    3. Contact us at <a href="mailto:support@example.com" style="color: #2563eb;">support@example.com</a>
                </p>
            </div>
            
            <p style="margin-top: 25px; font-style: italic; color: #64748b; text-align: center;">
                "{{ $quote ?? 'Knowledge is power. Information is liberating.' }}"<br>
                <span style="font-size: 12px;">- {{ $quoteAuthor ?? 'Kofi Annan' }}</span>
            </p>
        </div>
        
        <div class="footer">
            <p style="margin: 0 0 10px; font-weight: 600; color: #1e293b;">Want more resources?</p>
            <p style="margin: 0 0 15px; font-size: 14px; color: #475569;">
                Subscribe to our newsletter for exclusive content and updates.
            </p>
            <a href="{{ $newsletterUrl ?? '#' }}" style="color: #2563eb; text-decoration: none; font-weight: 500;">
                Join Our Community →
            </a>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #cbd5e1;">
                <p class="text-small">
                    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                    You received this email because you requested a PDF download from our website.
                </p>
                <p class="text-small" style="margin-top: 10px;">
                    <a href="{{ $privacyUrl ?? '#' }}" style="color: #64748b;">Privacy Policy</a> • 
                    <a href="{{ $unsubscribeUrl ?? '#' }}" style="color: #64748b;">Unsubscribe</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>