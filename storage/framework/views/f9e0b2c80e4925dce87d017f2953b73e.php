<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification LAgentO</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            padding: 40px 30px;
            text-align: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
        }
        
        .header-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .message {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #555;
        }
        
        .otp-container {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #FF6B35;
        }
        
        .otp-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #FF6B35;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        
        .instructions {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
            color: #555;
        }
        
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #856404;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer-text {
            font-size: 14px;
            color: #6c757d;
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
            <div class="logo">LAgent<span style="color: #FFE4B5;">O</span></div>
            <div class="header-subtitle">Votre assistant IA entrepreneurial</div>
        </div>
        
        <div class="content">
            <div class="greeting">Bonjour <?php echo e($userName); ?> ! 👋</div>
            
            <div class="message">
                Nous avons reçu une demande de connexion à votre compte LAgentO. 
                Pour confirmer votre identité, veuillez utiliser le code de vérification ci-dessous :
            </div>
            
            <div class="otp-container">
                <div class="otp-label">Code de vérification</div>
                <div class="otp-code"><?php echo e($otpCode); ?></div>
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
                Cet email a été envoyé par <span class="footer-brand">LAgentO</span>
            </div>
            <div class="footer-text">
                Votre assistant IA dédié aux entrepreneurs ivoiriens
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH /Users/laminebarro/agent-O/resources/views/emails/otp.blade.php ENDPATH**/ ?>