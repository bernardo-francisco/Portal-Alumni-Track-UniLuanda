<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\UnidadeOrganica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PerfilController extends Controller
{
    /**
     * Mostrar o perfil do administrador
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para aceder ao sistema.');
        }

        /** @var \App\Models\User $user */
        $admin = Admin::where('user_id', $user->id)->first();
        
        if (!$admin) {
            return redirect()->route('admin.perfil.edit')
                ->with('warning', 'Complete seu perfil para aceder ao sistema.');
        }

        $admin->load(['unidade']);
        
        return view('admin.perfil.index', compact('admin', 'user'));
    }

    /**
     * Mostrar o formulário de edição do perfil
     */
    public function edit()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para aceder ao sistema.');
        }

        /** @var \App\Models\User $user */
        $admin = Admin::where('user_id', $user->id)->first();
        
        if (!$admin) {
            $admin = Admin::create([
                'user_id' => $user->id,
                'nome_completo' => $user->name,
                'email' => $user->email,
                'telefone' => $user->phone,
                'foto_url' => $user->photo_url,
                'ativo' => true,
            ]);
        }

        $unidades = UnidadeOrganica::orderBy('nome')->get();
        
        return view('admin.perfil.edit', compact('admin', 'user', 'unidades'));
    }

    /**
     * Atualizar o perfil do administrador
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Sessão expirada. Faça login novamente.');
        }

        /** @var \App\Models\User $user */
        $admin = Admin::where('user_id', $user->id)->first();

        if (!$admin) {
            return redirect()->route('admin.perfil.edit')
                ->with('error', 'Perfil não encontrado.');
        }

        $validated = $request->validate([
            'nome_completo' => 'required|string|max:200',
            'telefone' => 'nullable|string|max:25',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'unidade_id' => 'nullable|exists:unidades_organicas,id',
            'cargo' => 'nullable|string|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // ============================================================
            // PROCESSAR FOTO - APENAS SE FOR ENVIADA UMA NOVA
            // ============================================================
            $fotoUrl = null;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $foto = $request->file('foto');
                $nomeFoto = 'admin_' . uniqid() . '_' . time() . '.' . $foto->getClientOriginalExtension();
                $foto->move(public_path('uploads/admins'), $nomeFoto);
                $fotoUrl = 'uploads/admins/' . $nomeFoto;

                // Remover foto antiga se existir
                if ($admin->foto_url && file_exists(public_path($admin->foto_url))) {
                    @unlink(public_path($admin->foto_url));
                }
            }

            // ============================================================
            // ATUALIZAR ADMIN
            // ============================================================
            $adminData = [
                'nome_completo' => $validated['nome_completo'],
                'email' => $validated['email'],
                'telefone' => $validated['telefone'] ?? null,
                'unidade_id' => $validated['unidade_id'] ?? null,
                'cargo' => $validated['cargo'] ?? null,
            ];

            // Só atualizar a foto se uma nova for enviada
            if ($fotoUrl) {
                $adminData['foto_url'] = $fotoUrl;
            }

            $admin->update($adminData);

            // ============================================================
            // ATUALIZAR USUÁRIO
            // ============================================================
            $userData = [
                'name' => $validated['nome_completo'],
                'phone' => $validated['telefone'] ?? null,
                'email' => $validated['email'],
            ];

            // Só atualizar a foto do usuário se uma nova for enviada
            if ($fotoUrl) {
                $userData['photo_url'] = $fotoUrl;
            }

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            DB::commit();

            // Atualizar sessão
            session([
                'user_name' => $validated['nome_completo'],
                'user_photo' => $fotoUrl ?? $user->photo_url,
            ]);

            return redirect()->route('admin.perfil.index')
                ->with('success', 'Perfil actualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao actualizar perfil do admin', [
                'user_id' => $user->id ?? 'null',
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao actualizar perfil: ' . $e->getMessage());
        }
    }
}