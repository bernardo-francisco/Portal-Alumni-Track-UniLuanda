<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use App\Traits\UserRolesTrait;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, UserRolesTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'photo_url',
        'role',
        'tipo',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // =============================================
    // RELACIONAMENTOS
    // =============================================

    public function egresso()
    {
        return $this->hasOne(Egresso::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Relação com Empresa
     * Um User pode ter um perfil de Empresa (role = 'empresa')
     */
    public function empresa()
    {
        return $this->hasOne(Empresa::class);
    }

    // =============================================
    // MÉTODOS DE VERIFICAÇÃO DE TIPO
    // =============================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->tipo === 'admin';
    }

    public function isEgresso(): bool
    {
        return $this->role === 'egresso' || $this->tipo === 'egresso';
    }

    /**
     * Verifica se o utilizador é uma Empresa
     */
    public function isEmpresa(): bool
    {
        return $this->role === 'empresa' || $this->tipo === 'empresa';
    }

    /**
     * Verifica se o utilizador tem perfil completo
     */
    public function hasPerfilCompleto(): bool
    {
        if ($this->isAdmin()) {
            return $this->admin !== null;
        }

        if ($this->isEmpresa()) {
            return $this->empresa !== null;
        }

        return $this->egresso !== null;
    }

    /**
     * Obtém o perfil do utilizador consoante o tipo
     */
    public function getPerfil()
    {
        if ($this->isAdmin()) {
            return $this->admin;
        }

        if ($this->isEmpresa()) {
            return $this->empresa;
        }

        return $this->egresso;
    }

    // =============================================
    // MÉTODOS PARA NOTIFICAÇÕES (ADMIN)
    // =============================================

    public function getNotificacoesNaoLidas()
    {
        $admin = $this->admin;

        if (!$admin) {
            return collect();
        }

        try {
            return Notificacao::where('admin_id', $admin->id)
                              ->where('lida', false)
                              ->latest()
                              ->take(10)
                              ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function getTotalNotificacoesNaoLidas()
    {
        $admin = $this->admin;

        if (!$admin) {
            return 0;
        }

        try {
            return Notificacao::where('admin_id', $admin->id)
                              ->where('lida', false)
                              ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getNotificacoes()
    {
        $admin = $this->admin;

        if (!$admin) {
            return collect();
        }

        try {
            return Notificacao::where('admin_id', $admin->id)
                              ->latest()
                              ->paginate(20);
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function notificarAdmin($titulo, $mensagem, $tipo = 'sistema', $link = null)
    {
        $admin = $this->admin;

        if (!$admin) {
            return null;
        }

        return Notificacao::create([
            'admin_id' => $admin->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'link' => $link,
            'lida' => false,
        ]);
    }

    // =============================================
    // MÉTODOS PARA NOTIFICAÇÕES (EGRESSO)
    // =============================================

    public function getNotificacoesEgressoNaoLidas()
    {
        $egresso = $this->egresso;

        if (!$egresso) {
            return collect();
        }

        try {
            return Notificacao::where('egresso_id', $egresso->id)
                              ->where('lida', false)
                              ->latest()
                              ->take(10)
                              ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function getTotalNotificacoesEgressoNaoLidas()
    {
        $egresso = $this->egresso;

        if (!$egresso) {
            return 0;
        }

        try {
            return Notificacao::where('egresso_id', $egresso->id)
                              ->where('lida', false)
                              ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function notificarEgresso($titulo, $mensagem, $tipo = 'sistema', $link = null)
    {
        $egresso = $this->egresso;

        if (!$egresso) {
            return null;
        }

        return Notificacao::create([
            'egresso_id' => $egresso->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'link' => $link,
            'lida' => false,
        ]);
    }

    // =============================================
    // MÉTODOS PARA NOTIFICAÇÕES (EMPRESA)
    // =============================================

    /**
     * Obtém as notificações não lidas da empresa
     */
    public function getNotificacoesEmpresaNaoLidas()
    {
        $empresa = $this->empresa;

        if (!$empresa) {
            return collect();
        }

        try {
            return Notificacao::where('empresa_id', $empresa->id)
                              ->where('lida', false)
                              ->latest()
                              ->take(10)
                              ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Obtém o total de notificações não lidas da empresa
     */
    public function getTotalNotificacoesEmpresaNaoLidas()
    {
        $empresa = $this->empresa;

        if (!$empresa) {
            return 0;
        }

        try {
            return Notificacao::where('empresa_id', $empresa->id)
                              ->where('lida', false)
                              ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Cria uma notificação para a empresa
     */
    public function notificarEmpresa($titulo, $mensagem, $tipo = 'sistema', $link = null)
    {
        $empresa = $this->empresa;

        if (!$empresa) {
            return null;
        }

        return Notificacao::create([
            'empresa_id' => $empresa->id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'link' => $link,
            'lida' => false,
        ]);
    }

    // =============================================
    // MÉTODOS PARA MENSAGENS (ADMIN)
    // =============================================

    public function getMensagensNaoLidas()
    {
        $admin = $this->admin;

        if (!$admin) {
            return collect();
        }

        try {
            $egresso = $this->egresso;

            if (!$egresso) {
                return collect();
            }

            return Mensagem::where('destinatario_id', $egresso->id)
                          ->where('lida', false)
                          ->with(['remetente' => function($q) {
                              $q->select('id', 'nome_completo', 'foto_url');
                          }])
                          ->latest()
                          ->take(5)
                          ->get()
                          ->map(function($msg) {
                              $remetente = $msg->remetente;
                              return [
                                  'id' => $msg->id,
                                  'nome' => $remetente->nome_completo ?? 'Egresso',
                                  'iniciais' => $remetente ? strtoupper(substr($remetente->nome_completo, 0, 2)) : 'EG',
                                  'mensagem' => Str::limit($msg->mensagem, 50),
                                  'hora' => $msg->created_at->format('H:i'),
                                  'data' => $msg->created_at->diffForHumans(),
                                  'link' => route('admin.mensagens.conversa', $remetente->id ?? 0),
                              ];
                          });
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function getTotalMensagensNaoLidas()
    {
        $admin = $this->admin;

        if (!$admin) {
            return 0;
        }

        try {
            $egresso = $this->egresso;

            if (!$egresso) {
                return 0;
            }

            return Mensagem::where('destinatario_id', $egresso->id)
                          ->where('lida', false)
                          ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    // =============================================
    // MÉTODOS AUXILIARES
    // =============================================

    public function getPhotoUrlAttribute($value): string
    {
        if (empty($value)) {
            return asset('images/default-avatar.png');
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (file_exists(public_path($value))) {
            return asset($value);
        }

        $caminhoUploads = public_path('uploads/users/' . basename($value));
        if (file_exists($caminhoUploads)) {
            return asset('uploads/users/' . basename($value));
        }

        $caminhoStorage = storage_path('app/public/' . $value);
        if (file_exists($caminhoStorage)) {
            return asset('storage/' . $value);
        }

        $caminhoStorageUsers = storage_path('app/public/users/' . basename($value));
        if (file_exists($caminhoStorageUsers)) {
            return asset('storage/users/' . basename($value));
        }

        return asset('images/default-avatar.png');
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($names, 0, 2) as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        return $initials;
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin')->orWhere('tipo', 'admin');
    }

    public function scopeEgressos($query)
    {
        return $query->where('role', 'egresso')->orWhere('tipo', 'egresso');
    }

    public function scopeEmpresas($query)
    {
        return $query->where('role', 'empresa')->orWhere('tipo', 'empresa');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =============================================
    // MÉTODOS DE COMPATIBILIDADE
    // =============================================

    public function updateLastLogin(): void
    {
        if (Schema::hasColumn($this->getTable(), 'last_login_at')) {
            $this->timestamps = false;
            $this->update(['last_login_at' => now()]);
            $this->timestamps = true;
        }
    }
}