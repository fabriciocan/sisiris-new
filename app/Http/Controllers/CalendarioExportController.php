<?php

namespace App\Http\Controllers;

use App\Models\EventoCalendario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CalendarioExportController extends Controller
{
    /**
     * Exportar eventos em formato iCal
     */
    public function exportIcal(Request $request)
    {
        $assembleia_id = $request->get('assembleia_id');
        $inicio = $request->get('inicio', Carbon::now()->startOfMonth());
        $fim = $request->get('fim', Carbon::now()->endOfMonth());

        $query = EventoCalendario::with(['assembleia'])
            ->whereBetween('data_inicio', [$inicio, $fim]);

        if ($assembleia_id) {
            $query->where('assembleia_id', $assembleia_id);
        }

        // Se não for admin, só mostrar eventos públicos
        $user = Auth::user();
        $query->where(function ($q) use ($user) {
            $q->where('publico', true)
              ->orWhere('assembleia_id', $user->assembleia_id);
        });

        $eventos = $query->get();

        $ical = $this->generateIcal($eventos);

        return response($ical, 200, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="calendario-eventos.ics"',
        ]);
    }

    /**
     * Gerar conteúdo iCal
     */
    private function generateIcal($eventos)
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//SISIRIS//Calendario de Eventos//PT\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";

        foreach ($eventos as $evento) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:" . $evento->id . "@sisiris.local\r\n";
            $ical .= "DTSTAMP:" . Carbon::now()->format('Ymd\THis\Z') . "\r\n";
            $ical .= "DTSTART:" . Carbon::parse($evento->data_inicio)->format('Ymd\THis\Z') . "\r\n";
            
            if ($evento->data_fim) {
                $ical .= "DTEND:" . Carbon::parse($evento->data_fim)->format('Ymd\THis\Z') . "\r\n";
            }
            
            $ical .= "SUMMARY:" . $this->escapeString($evento->titulo) . "\r\n";
            
            if ($evento->descricao) {
                $ical .= "DESCRIPTION:" . $this->escapeString($evento->descricao) . "\r\n";
            }
            
            if ($evento->local) {
                $ical .= "LOCATION:" . $this->escapeString($evento->local . ($evento->endereco ? ', ' . $evento->endereco : '')) . "\r\n";
            }
            
            $ical .= "STATUS:CONFIRMED\r\n";
            $ical .= "CATEGORIES:" . $this->escapeString(str_replace('_', ' ', $evento->tipo)) . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Escapar strings para iCal
     */
    private function escapeString($string)
    {
        $string = str_replace(["\n", "\r"], "\\n", $string);
        $string = str_replace([",", ";"], "\\$0", $string);
        return $string;
    }

    /**
     * Gerar link para Google Calendar
     */
    public function googleCalendarLink(Request $request, $eventoId)
    {
        $evento = EventoCalendario::findOrFail($eventoId);

        // Verificar permissões
        $user = Auth::user();
        if (!$evento->publico && $evento->assembleia_id !== $user->assembleia_id) {
            abort(403);
        }

        $params = [
            'action' => 'TEMPLATE',
            'text' => $evento->titulo,
            'dates' => Carbon::parse($evento->data_inicio)->format('Ymd\THis\Z') . '/' . 
                      Carbon::parse($evento->data_fim ?: $evento->data_inicio)->format('Ymd\THis\Z'),
            'details' => $evento->descricao,
            'location' => $evento->local . ($evento->endereco ? ', ' . $evento->endereco : ''),
        ];

        $url = 'https://calendar.google.com/calendar/render?' . http_build_query($params);

        return redirect($url);
    }
}
