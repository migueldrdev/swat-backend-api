<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { width: 80%; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { color: #d32f2f; font-size: 24px; font-weight: bold; margin-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">SWAT Protection 51</div>
        <p>Hola, <strong>{{ $document->user->name }}</strong>.</p>
        <p>Se ha subido un nuevo documento laboral a tu perfil:</p>
        <ul>
            <li><strong>Título:</strong> {{ $document->title }}</li>
            <li><strong>Tipo:</strong> {{ $document->type }}</li>
            <li><strong>Fecha:</strong> {{ $document->created_at->format('d/m/Y H:i') }}</li>
        </ul>
        <p>Puedes acceder al ecosistema digital para visualizarlo y descargarlo.</p>
        <p>Este es un sistema automatizado, por favor no respondas a este correo.</p>
        <div class="footer">
            &copy; {{ date('Y') }} SWAT Protection 51. Sistema de Blindaje Legal.
        </div>
    </div>
</body>
</html>
