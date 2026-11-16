<?php

namespace App\Providers;

use App\Mail\BrevoTransport;
use App\Models\CampanaPreventiva;
use App\Models\Horario;
use App\Models\Posta;
use App\Models\TurnoMedico;
use App\Models\User;
use App\Models\VacunaAviso;
use App\Support\CachePortal;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Conexiones persistentes sin repetir SET NAMES/sql_mode en cada petición.
        $this->app->bind('db.connector.mysql', \App\Support\ConectorMySql::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Detrás del proxy HTTPS de Render todas las URL deben ser https.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Correo por la API HTTPS de Brevo (MAIL_MAILER=brevo).
        Mail::extend('brevo', fn (array $config) => new BrevoTransport((string) ($config['key'] ?? '')));

        // Correo de recuperación en español.
        ResetPassword::toMailUsing(function ($usuario, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $usuario->getEmailForPasswordReset()]);

            return (new MailMessage)
                ->subject('Hampi · Restablece tu contraseña')
                ->greeting('Hola, '.$usuario->name)
                ->line('Recibimos una solicitud para cambiar la contraseña de tu cuenta en Hampi.')
                ->action('Crear contraseña nueva', $url)
                ->line('El enlace vence en 60 minutos. Si no lo pediste, ignora este correo: tu contraseña no cambiará.')
                ->salutation('Microred Socabaya');
        });

        // Cualquier cambio en estos datos invalida la caché del portal.
        foreach ([Horario::class, TurnoMedico::class, User::class, VacunaAviso::class, Posta::class, CampanaPreventiva::class] as $modelo) {
            $modelo::saved(fn () => CachePortal::limpiar());
            $modelo::deleted(fn () => CachePortal::limpiar());
        }
    }
}
