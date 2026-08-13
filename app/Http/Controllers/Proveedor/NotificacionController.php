<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function index(): View
    {
        $proveedorId = Auth::guard('proveedor')->id();

        $notificaciones = DB::table('notificaciones')
            ->where('destinatario_tipo', 'proveedor')
            ->where('destinatario_id', $proveedorId)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $pendientes = $notificaciones->where('leida', false)->count();

        return view('proveedor.notificaciones.index', compact('notificaciones', 'pendientes'));
    }

    public function marcarLeida(int $notificacion): RedirectResponse
    {
        $proveedorId = Auth::guard('proveedor')->id();

        $row = DB::table('notificaciones')
            ->where('id', $notificacion)
            ->where('destinatario_tipo', 'proveedor')
            ->where('destinatario_id', $proveedorId)
            ->first();

        abort_unless($row, 404);

        DB::table('notificaciones')->where('id', $notificacion)->update(['leida' => true]);

        if ($row->referencia_tipo === 'recepcion' && $row->referencia_id) {
            return redirect()
                ->route('proveedor.entregas.show', $row->referencia_id)
                ->with('status', 'Notificación marcada como leída.');
        }

        return back()->with('status', 'Notificación marcada como leída.');
    }
}
