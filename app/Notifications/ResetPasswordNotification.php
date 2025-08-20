<?php
// 1. app/Notifications/ResetPasswordNotification.php - VERSIÓN PRESENTABLE

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('🔑 Solicitud de restablecimiento de contraseña')
            ->greeting('¡Hola ' . $notifiable->first_name . '!')
            ->line('Has solicitado restablecer la contraseña de tu cuenta en **' . config('app.name') . '**.')
            ->line('')
            ->line('**Detalles de la solicitud:**')
            ->line('• Correo electrónico: ' . $notifiable->email)
            ->line('• Fecha: ' . now()->format('d/m/Y H:i'))
            ->line('• Válido por: ' . $expireMinutes . ' minutos')
            ->line('')
            ->action('🔐 Restablecer mi contraseña', $url)
            ->line('')
            ->line('**Información importante:**')
            ->line('• Este enlace solo funciona una vez')
            ->line('• Expirará automáticamente en ' . $expireMinutes . ' minutos')
            ->line('• No compartas este enlace con nadie')
            ->line('')
            ->line('Si no solicitaste este cambio, puedes ignorar este correo de forma segura. Tu contraseña actual permanecerá sin cambios.')
            ->line('')
            ->line('¿Tienes problemas? Contacta a nuestro equipo de soporte.')
            ->salutation('Saludos cordiales,  
**Equipo de ' . config('app.name') . '**');
    }
}
