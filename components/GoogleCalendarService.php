<?php
namespace app\components;

use app\models\EventosSincronizados;
use app\models\Usuarios;
use Yii;
use Google_Client;
use Google_Service_Calendar;
class GoogleCalendarService implements InterfaceGoogle
{


    public function verificarUsuarioLogueado()
    {
        $usuario = Yii::$app->user->identity;
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'Debes iniciar sesión para acceder a esta sección.');
            Yii::$app->controller->redirect(['site/login']);
            Yii::$app->end();
        }
        return $usuario;
    }



    private function getClient($usuario = null): Google_Client
    {
        $client = new \Google_Client();
        $client->setApplicationName('MiSistemaZebra'); // ← esto también ayuda
        $client->setClientId(Yii::$app->params['google_client_id']);
        $client->setClientSecret(Yii::$app->params['google_client_secret']);
        $client->setRedirectUri(Yii::$app->params['google_redirect_uri']);
        $client->addScope(\Google_Service_Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        // ⬇️ Esta es clave para permitir crear reuniones por Meet
        $client->setConfig('conferenceDataVersion', 1);

        if ($usuario && $usuario->google_token) {
            $token = json_decode($usuario->google_token, true);
            $client->setAccessToken($token);

            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $nuevoToken = $client->getAccessToken();
                $usuario->google_token = json_encode($nuevoToken);
                $usuario->save(false);
            }
        }

        return $client;
    }


    public function getAuthUrl(): string
    {
        return $this->getClient()->createAuthUrl();
    }

    public function fetchAccessToken(string $code): array
    {
        $client = $this->getClient();
        return $client->fetchAccessTokenWithAuthCode($code);
    }

    public function crearEventoConMeet($usuario, string $titulo, string $descripcion, string $horaInicio, string $horaFin): ?string
    {
        $client = $this->getClient($usuario);
        $service = new \Google_Service_Calendar($client);

        $event = new \Google_Service_Calendar_Event([
            'summary' => $titulo,
            'description' => $descripcion,
            'start' => [
                'dateTime' => $horaInicio,
                'timeZone' => $usuario->zona_horaria ?? 'America/La_Paz',
            ],
            'end' => [
                'dateTime' => $horaFin,
                'timeZone' => $usuario->zona_horaria ?? 'America/La_Paz',
            ],
            // ← sin conferenceData
        ]);


        $createdEvent = $service->events->insert('primary', $event, ['conferenceDataVersion' => 1]);

        return $createdEvent->getHangoutLink(); // Devuelve el enlace Meet
    }

    public function crearEvento(string $token, array $evento): ?string
    {
        $client = new \Google_Client();
        $client->setAccessToken($token);

        $service = new \Google_Service_Calendar($client);
        $calendarId = 'primary';

        $googleEvent = new \Google_Service_Calendar_Event($evento);
        $createdEvent = $service->events->insert($calendarId, $googleEvent);

        return $createdEvent->getId(); // ID de Google Calendar
    }




    public function obtenerEventosSincronizados($usuario): array
    {
        $registros = EventosSincronizados::find()
            ->select(['titulo', 'fecha_inicio', 'url']) // incluir url
            ->where(['idusuario' => $usuario->idusuario])
            ->asArray()
            ->all();

        $resultado = [];
        foreach ($registros as $r) {
            $clave = $r['titulo'] . '|' . $r['fecha_inicio'] . '|' . ($r['url'] ?? '');
            $resultado[$clave] = true;
        }
        return $resultado;
    }



    public function registrarEventosSincronizados($usuario, array $eventos): void
    {
        foreach ($eventos as $e) {
            $modelo = new EventosSincronizados();
            $modelo->idusuario = $usuario->idusuario;
            $modelo->titulo = $e['summary'];
            $modelo->fecha_inicio = $e['start']['date'] ?? null;
            $modelo->url = $e['url'] ?? null; // <-- NUEVO

            $modelo->google_event_id = $e['google_event_id'] ?? null;
            $modelo->save(false);
        }
    }



    public function sincronizarEventos($usuario): array
    {
        $eventos = Yii::$app->calendarService->obtenerEventosPorUsuario($usuario);
        $existentes = $this->obtenerEventosSincronizados($usuario);

        $nuevos = [];
        foreach ($eventos as $e) {
            $clave = $e['title'] . '|' . date('Y-m-d', strtotime($e['start'])) . '|' . ($e['url'] ?? '');
            if (!isset($existentes[$clave])) {
                $nuevos[] = $e;
            }
        }

        if (empty($nuevos)) {
            return [
                'flash_tipo' => 'info',
                'mensaje' => 'Todos los eventos ya están sincronizados.',
            ];
        }

        $contador = 0;
        $aRegistrar = [];

        foreach ($nuevos as $e) {
            $origen = $e['extendedProps']['origen'] ?? ucfirst($e['tipo'] ?? 'Evento');

            $eventoGoogle = [
                'summary'     => $e['title'],
                'description' => $origen,
                'start'       => ['date' => $e['start']],
                'end'         => ['date' => $e['end'] ?? date('Y-m-d', strtotime($e['start'] . ' +1 day'))],
                'url'         => $e['url'] ?? null,
            ];

            try {
                $googleId = $this->crearEvento($usuario->google_token, $eventoGoogle);
                $eventoGoogle['google_event_id'] = $googleId;
                $aRegistrar[] = $eventoGoogle;
                $contador++;
            } catch (\Throwable $ex) {
                Yii::error("Error al sincronizar evento: " . $ex->getMessage(), 'google');
            }
        }

        $this->registrarEventosSincronizados($usuario, $aRegistrar);

        return [
            'flash_tipo' => 'success',
            'mensaje' => "Se sincronizaron $contador nuevos eventos.",
            'cantidad' => $contador,
        ];
    }



}