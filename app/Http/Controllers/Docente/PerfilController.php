<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Docente;

class PerfilController
{
    public function index()
    {
$docente = Docente::where('id_usuario', Auth::id())->firstOrFail();

        
        return view('docente.perfil', compact('docente'));
    }
}