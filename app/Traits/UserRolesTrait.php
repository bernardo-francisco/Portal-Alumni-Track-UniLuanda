<?php

namespace App\Traits;

trait UserRolesTrait
{
    /**
     * Verifica se o usuário é administrador
     */
    public function isAdmin(): bool
    {
        $role = $this->role ?? $this->getAttribute('role');
        $tipo = $this->tipo ?? $this->getAttribute('tipo');
        
        return $role === 'admin' || $tipo === 'admin';
    }

    /**
     * Verifica se o usuário é egresso
     */
    public function isEgresso(): bool
    {
        $role = $this->role ?? $this->getAttribute('role');
        $tipo = $this->tipo ?? $this->getAttribute('tipo');
        
        return $role === 'egresso' || $tipo === 'egresso';
    }

    /**
     * Verifica se o usuário é coordenador
     */
    public function isCoordenador(): bool
    {
        $role = $this->role ?? $this->getAttribute('role');
        return $role === 'coordenador';
    }

    /**
     * Verifica se o usuário é empresa
     */
    public function isEmpresa(): bool
    {
        $role = $this->role ?? $this->getAttribute('role');
        $tipo = $this->tipo ?? $this->getAttribute('tipo');
        
        return $role === 'empresa' || $tipo === 'empresa';
    }

    /**
     * Verifica se o usuário está ativo
     */
    public function isActive(): bool
    {
        $isActive = $this->is_active ?? $this->getAttribute('is_active');
        return (bool) $isActive;
    }

    /**
     * Atualiza o último login
     */
    public function updateLastLogin(): bool
    {
        return $this->update(['last_login' => now()]);
    }

    /**
     * Verifica se o usuário tem permissão para acessar uma rota
     */
    public function hasRole(string $role): bool
    {
        $userRole = $this->role ?? $this->getAttribute('role');
        return $userRole === $role;
    }

    /**
     * Verifica se o usuário tem um dos papéis especificados
     */
    public function hasAnyRole(array $roles): bool
    {
        $userRole = $this->role ?? $this->getAttribute('role');
        return in_array($userRole, $roles);
    }

    /**
     * Obtém o papel do usuário em formato legível
     */
    public function getRoleLabelAttribute(): string
    {
        $labels = [
            'admin' => 'Administrador',
            'egresso' => 'Egresso',
            'coordenador' => 'Coordenador',
            'empresa' => 'Empresa',
        ];
        
        $role = $this->role ?? $this->getAttribute('role');
        return $labels[$role] ?? $role;
    }

    /**
     * Obtém o tipo do usuário em formato legível
     */
    public function getTipoLabelAttribute(): string
    {
        $labels = [
            'admin' => 'Administrador',
            'egresso' => 'Egresso',
            'empresa' => 'Empresa',
        ];
        
        $tipo = $this->tipo ?? $this->getAttribute('tipo');
        return $labels[$tipo] ?? $tipo;
    }
}