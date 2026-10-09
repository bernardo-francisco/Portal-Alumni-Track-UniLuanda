<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\MuralNoticia;
use Illuminate\Http\Request;

class MuralController extends Controller
{
    public function index()
    {
        $publicacoes = MuralNoticia::where('publicado', true)
            ->orderBy('destaque', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('egresso.mural.index', compact('publicacoes'));
    }
}