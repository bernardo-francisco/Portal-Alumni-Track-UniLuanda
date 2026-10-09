<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Egresso;
use App\Models\Localizacao;
use App\Models\Profissional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PerfilController extends Controller
{
    // ================================================================
    // 👁️ MOSTRAR PERFIL
    // ================================================================
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para aceder ao sistema.');
        }

        $egresso = Egresso::where('user_id', $user->id)->first();

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil para acessar o sistema.');
        }

        $egresso->load(['curso.unidade', 'localizacoes', 'profissionais']);

        $localizacaoAtual = $egresso->localizacoes
            ->sortByDesc('data_desde')
            ->first();

        $profissionalAtual = $egresso->profissionalAtual;

        return view('egresso.perfil.index', compact('egresso', 'localizacaoAtual', 'profissionalAtual'));
    }

    // ================================================================
    // ✏️ FORMULÁRIO DE EDIÇÃO
    // ================================================================
    public function edit()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para aceder ao sistema.');
        }

        $egresso = Egresso::where('user_id', $user->id)->first();

        if (!$egresso) {
            $egresso = Egresso::create([
                'user_id'         => $user->id,
                'nome_completo'   => $user->name,
                'email'           => $user->email,
                'numero_processo' => 'ALUMNI_' . date('Ymd') . '_' . $user->id,
                'status'          => 'active',
                'verificado'      => false,
            ]);
        }

        $egresso->load(['localizacoes', 'profissionais']);

        $localizacaoAtual = $egresso->localizacoes
            ->sortByDesc('data_desde')
            ->first();

        $profissionalAtual = $egresso->profissionalAtual;

        $cursos = Curso::with('unidade')->orderBy('nome')->get();

        return view('egresso.perfil.editar', compact(
            'egresso',
            'cursos',
            'localizacaoAtual',
            'profissionalAtual'
        ));
    }

    // ================================================================
    // 💾 ACTUALIZAR PERFIL
    // ================================================================
    public function update(Request $request)
    {
        // ============================================================
        // VALIDAÇÃO
        // ============================================================
        $validated = $request->validate([
            // Dados pessoais
            'nome_completo'   => 'required|string|max:200',
            'genero'          => 'required|in:M,F,O',
            'data_nascimento' => 'nullable|date|before:today',
            'telefone'        => 'nullable|string|max:25',
            'foto'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'password'        => 'nullable|string|min:6|confirmed',

            // Dados académicos
            'curso_id'        => 'nullable|exists:cursos,id',
            'ano_formatura'   => 'nullable|integer|min:1990|max:' . (date('Y') + 5),
            'nota_final'      => 'nullable|numeric|min:0|max:20',
            'observacoes'     => 'nullable|string|max:500',

            // Localização
            'pais'            => 'nullable|string|max:100',
            'provincia'       => 'nullable|string|max:100',
            'cidade'          => 'nullable|string|max:100',
            'endereco'        => 'nullable|string|max:255',
            'latitude'        => 'nullable|string|max:50',
            'longitude'       => 'nullable|string|max:50',
            'data_desde'      => 'nullable|date',
            'is_current'      => 'nullable|boolean',

            // Profissional
            'emprego_tipo'    => 'nullable|in:full_time,part_time,freelance,self_employed,unemployed,student,unknown',
            'cargo'           => 'nullable|string|max:255',
            'empregador'      => 'nullable|string|max:255',
            'sector'          => 'nullable|string|max:255',
            'data_inicio'     => 'nullable|date',
            'linkedin_url'    => 'nullable|url|max:255',
            'is_current_emprego' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Sessão expirada. Faça login novamente.');
        }

        try {
            DB::beginTransaction();

            // ============================================================
            // FOTO
            // ============================================================
            $fotoUrl = null;

            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $foto = $request->file('foto');

                // Garantir que a pasta existe
                $pasta = public_path('uploads/egressos');
                if (!file_exists($pasta)) {
                    mkdir($pasta, 0755, true);
                }

                // Nome único
                $nomeFoto = 'egresso_' . uniqid() . '_' . time() . '.' . $foto->getClientOriginalExtension();

                // Mover para public/uploads/egressos
                $foto->move($pasta, $nomeFoto);

                // Caminho relativo
                $fotoUrl = 'uploads/egressos/' . $nomeFoto;
            }

            // ============================================================
            // EGRESSO
            // ============================================================
            $egresso = Egresso::where('user_id', $user->id)->first();

            if (!$egresso) {
                $egresso = new Egresso();
                $egresso->user_id = $user->id;
                $egresso->email = $user->email;
                $egresso->numero_processo = 'ALUMNI_' . date('Ymd') . '_' . $user->id;
                $egresso->status = 'active';
                $egresso->verificado = false;
            }

            $egressoData = [
                'nome_completo'   => $validated['nome_completo'],
                'genero'          => $validated['genero'],
                'data_nascimento' => $validated['data_nascimento'] ?? null,
                'telefone'        => $validated['telefone'] ?? null,
                'curso_id'        => $validated['curso_id'] ?? null,
                'ano_formatura'   => $validated['ano_formatura'] ?? null,
                'nota_final'      => $validated['nota_final'] ?? null,
                'observacoes'     => $validated['observacoes'] ?? null,
            ];

            // Se enviou foto nova
            if ($fotoUrl) {
                // Apagar foto antiga
                if ($egresso->foto_url && file_exists(public_path($egresso->foto_url))) {
                    @unlink(public_path($egresso->foto_url));
                }
                $egressoData['foto_url'] = $fotoUrl;
            }

            if ($egresso->exists) {
                $egresso->update($egressoData);
            } else {
                $egresso->fill($egressoData);
                $egresso->save();
            }

            // ============================================================
            // LOCALIZAÇÃO
            // ============================================================
            $temDadosLocalizacao =
                $request->filled('pais') ||
                $request->filled('provincia') ||
                $request->filled('cidade') ||
                $request->filled('endereco') ||
                $request->filled('latitude') ||
                $request->filled('longitude') ||
                $request->filled('data_desde');

            if ($temDadosLocalizacao) {

                $totalLocalizacoes = Localizacao::where('egresso_id', $egresso->id)->count();
                $marcarComoAtual = $request->has('is_current') || $totalLocalizacoes === 0;

                if ($marcarComoAtual) {
                    Localizacao::where('egresso_id', $egresso->id)
                        ->update(['is_current' => false]);
                }

                $localizacao = Localizacao::where('egresso_id', $egresso->id)
                    ->where('is_current', true)
                    ->first();

                if (!$localizacao) {
                    $localizacao = new Localizacao();
                    $localizacao->egresso_id = $egresso->id;
                }

                $localizacao->fill([
                    'pais'       => $request->filled('pais')       ? $request->pais       : ($localizacao->pais ?? null),
                    'provincia'  => $request->filled('provincia')  ? $request->provincia  : ($localizacao->provincia ?? null),
                    'cidade'     => $request->filled('cidade')     ? $request->cidade     : ($localizacao->cidade ?? null),
                    'endereco'   => $request->filled('endereco')   ? $request->endereco   : ($localizacao->endereco ?? null),
                    'latitude'   => $request->filled('latitude')   ? $request->latitude   : ($localizacao->latitude ?? null),
                    'longitude'  => $request->filled('longitude')  ? $request->longitude  : ($localizacao->longitude ?? null),
                    'data_desde' => $request->filled('data_desde') ? $request->data_desde : ($localizacao->data_desde ?? null),
                    'is_current' => 1,
                ]);

                $localizacao->save();
            }

            // ============================================================
            // PROFISSIONAL
            // ============================================================
            $temDadosProfissional =
                $request->filled('emprego_tipo') ||
                $request->filled('cargo') ||
                $request->filled('empregador') ||
                $request->filled('sector') ||
                $request->filled('data_inicio') ||
                $request->filled('linkedin_url');

            if ($temDadosProfissional) {

                $totalProfissionais = Profissional::where('egresso_id', $egresso->id)->count();
                $marcarComoAtual = $request->has('is_current_emprego') || $totalProfissionais === 0;

                if ($marcarComoAtual) {
                    Profissional::where('egresso_id', $egresso->id)
                        ->update(['is_current' => false]);
                }

                $profissional = Profissional::where('egresso_id', $egresso->id)
                    ->where('is_current', true)
                    ->first();

                if (!$profissional) {
                    $profissional = new Profissional();
                    $profissional->egresso_id = $egresso->id;
                }

                $tipoEmprego = $request->filled('emprego_tipo')
                    ? $request->emprego_tipo
                    : ($profissional->tipo_emprego ?? 'unknown');

                $profissional->fill([
                    'tipo_emprego' => $tipoEmprego,
                    'cargo'        => $request->filled('cargo')        ? $request->cargo        : ($profissional->cargo ?? null),
                    'empregador'   => $request->filled('empregador')   ? $request->empregador   : ($profissional->empregador ?? null),
                    'sector'       => $request->filled('sector')       ? $request->sector       : ($profissional->sector ?? null),
                    'data_inicio'  => $request->filled('data_inicio')  ? $request->data_inicio  : ($profissional->data_inicio ?? null),
                    'linkedin_url' => $request->filled('linkedin_url') ? $request->linkedin_url : ($profissional->linkedin_url ?? null),
                    'is_current'   => 1,
                ]);

                $profissional->save();
            }

            // ============================================================
            // USER (nome, telefone, foto, password)
            // ============================================================
            $userData = [
                'name'  => $validated['nome_completo'],
                'phone' => $validated['telefone'] ?? null,
            ];

            if ($fotoUrl) {
                // Apagar foto antiga do user
                if ($user->photo_url && file_exists(public_path($user->photo_url)) && $user->photo_url !== $egresso->foto_url) {
                    @unlink(public_path($user->photo_url));
                }
                $userData['photo_url'] = $fotoUrl;
            }

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            User::where('id', $user->id)->update($userData);

            DB::commit();

            // Actualizar sessão
            session([
                'user_name'  => $validated['nome_completo'],
                'user_photo' => $fotoUrl ?? $user->photo_url,
            ]);

            return redirect()->route('egresso.perfil')
                ->with('success', 'Perfil actualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao actualizar perfil', [
                'user_id' => $user->id ?? 'null',
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao actualizar perfil: ' . $e->getMessage());
        }
    }
}