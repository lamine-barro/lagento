<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification LagentO</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            background-color: #F9FAFB;
            color: #1F2937;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #E5E7EB;
        }
        
        .header {
            background-color: #FFFFFF;
            padding: 32px 30px;
            text-align: center;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .logo {
            height: 60px;
            width: auto;
            margin-bottom: 10px;
        }
        
        .header-subtitle {
            color: #6B7280;
            font-size: 16px;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1F2937;
        }
        
        .message {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #374151;
        }
        
        .otp-container {
            text-align: center;
            margin: 30px 0;
            padding: 24px;
            background-color: #F3F4F6;
            border-radius: 8px;
            border: 1px solid #E5E7EB;
        }
        
        .otp-label {
            font-size: 14px;
            color: #6B7280;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #FF6B35;
            letter-spacing: 6px;
            font-family: 'Courier New', monospace;
        }
        
        .instructions {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
            color: #374151;
        }
        
        .warning {
            background-color: #FFFBEB;
            border: 1px solid #FDE68A;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #92400E;
        }
        
        .footer {
            background-color: #F9FAFB;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer-text {
            font-size: 14px;
            color: #6B7280;
            margin-bottom: 10px;
        }
        
        .footer-brand {
            font-weight: bold;
            color: #FF6B35;
        }
        
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            
            .header, .content, .footer {
                padding: 25px 20px;
            }
            
            .otp-code {
                font-size: 28px;
                letter-spacing: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('logo-light.png') }}" alt="LagentO" style="height: 60px; width: auto; margin-bottom: 10px;">
            <div class="header-subtitle">Votre assistant IA entrepreneurial</div>
        </div>
        
        <div class="content">
            <div class="greeting">Bonjour {{ $userName }} ! 👋</div>
            
            <div class="message">
                Nous avons reçu une demande de connexion à votre compte LagentO. 
                Pour confirmer votre identité, veuillez utiliser le code de vérification ci-dessous :
            </div>
            
            <div class="otp-container">
                <div class="otp-label">Code de vérification</div>
                <div class="otp-code">{{ $otpCode }}</div>
            </div>
            
            <div class="instructions">
                <strong>Instructions :</strong>
                <ul>
                    <li>Saisissez ce code dans l'écran de vérification</li>
                    <li>Ce code est valide pendant <strong>10 minutes</strong></li>
                    <li>Ne partagez jamais ce code avec personne</li>
                </ul>
            </div>
            
            <div class="warning">
                <strong>⚠️ Sécurité :</strong> Si vous n'avez pas demandé ce code, ignorez cet email. 
                Votre compte reste sécurisé et aucune action n'est requise.
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-text">
                Cet email a été envoyé par <span class="footer-brand">LagentO</span>
            </div>
            <div class="footer-text">
                Votre assistant IA dédié aux entrepreneurs ivoiriens
            </div>
        </div>
    </div>
</body>
</html>