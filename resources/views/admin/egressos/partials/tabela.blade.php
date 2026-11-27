{{-- resources/views/admin/egressos/partials/tabela.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 report-table">
        <thead class="bg-light">
            <tr>
                <th class="ps-4">Egresso</th>
                <th>Nº Processo</th>
                <th>Curso</th>
                <th>Unidade</th>
                <th class="text-center">Status</th>
                <th class="text-end pe-4">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($egressos as $egresso)
                @php
                    $temFoto = !empty($egresso->foto_url);
                    $iniciais = collect(explode(' ', trim($egresso->nome_completo ?? 'E')))
                        ->filter()
                        ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                        ->take(2)
                        ->implode('');

                    $statusMap = [
                        'active'       => ['color' => 'success',   'label' => 'Activo',       'icon' => 'check-circle'],
                        'inactive'     => ['color' => 'danger',    'label' => 'Inactivo',     'icon' => 'times-circle'],
                        'lost_contact' => ['color' => 'secondary', 'label' => 'Sem Contacto', 'icon' => 'question-circle'],
                    ];
                    $statusInfo = $statusMap[$egresso->status] ?? ['color' => 'secondary', 'label' => $egresso->status, 'icon' => 'circle'];

                    $verificado = $egresso->verificado ?? false;
                @endphp

                <tr>
                    {{-- Egresso (avatar + nome + email) --}}
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            @if($temFoto)
                                <img src="{{ asset($egresso->foto_url) }}"
                                     alt="{{ $egresso->nome_completo }}"
                                     class="rounded-circle border flex-shrink-0"
                                     style="width: 42px; height: 42px; object-fit: cover;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="rounded-circle align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                     style="width: 42px; height: 42px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 0.85rem; display: none;">
                                    {{ $iniciais }}
                                </div>
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                     style="width: 42px; height: 42px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 0.85rem;">
                                    {{ $iniciais }}
                                </div>
                            @endif

                            <div class="min-width-0">
                                <div class="d-flex align-items-center gap-1">
                                    <strong class="d-block text-truncate" style="max-width: 220px;">
                                        {{ $egresso->nome_completo }}
                                    </strong>
                                    @if($verificado)
                                        <i class="fas fa-check-circle text-primary" style="font-size: 0.75rem;" title="Verificado"></i>
                                    @endif
                                </div>
                                @if($egresso->email)
                                    <small class="text-muted d-block text-truncate" style="max-width: 220px;">
                                        <i class="fas fa-envelope me-1" style="font-size: 0.7rem;"></i>
                                        {{ $egresso->email }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Nº Processo --}}
                    <td>
                        <code class="text-dark fw-bold">{{ $egresso->numero_processo }}</code>
                    </td>

                    {{-- Curso --}}
                    <td>
                        @if($egresso->curso)
                            <div class="d-flex align-items-center gap-1">
                                <i class="fas fa-graduation-cap text-primary" style="font-size: 0.75rem;"></i>
                                <span class="small">{{ $egresso->curso->nome }}</span>
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- Unidade --}}
                    <td>
                        @if($egresso->curso && $egresso->curso->unidade)
                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                <i class="fas fa-building me-1"></i>
                                {{ $egresso->curso->unidade->sigla }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="text-center">
                        <span class="badge bg-{{ $statusInfo['color'] }}-subtle text-{{ $statusInfo['color'] }}-emphasis border border-{{ $statusInfo['color'] }}-subtle">
                            <i class="fas fa-{{ $statusInfo['icon'] }} me-1"></i>
                            {{ $statusInfo['label'] }}
                        </span>
                    </td>

                    {{-- Ações --}}
                    <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.egressos.show', $egresso->id) }}"
                               class="btn btn-outline-primary"
                               title="Ver perfil">
                                <i class="fas fa-eye"></i>
                            </a>

                            <a href="{{ route('admin.egressos.edit', $egresso->id) }}"
                               class="btn btn-outline-primary"
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('admin.egressos.destroy', $egresso->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Tem certeza que deseja eliminar este egresso? Esta ação não pode ser desfeita.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-outline-danger"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-users-slash"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Nenhum egresso encontrado</h6>
                        @if(request()->hasAny(['search', 'unidade_id', 'curso_id', 'status']))
                            <p class="text-muted small mb-3">
                                Tente ajustar os filtros aplicados.
                            </p>
                            <a href="{{ route('admin.egressos.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Limpar filtros
                            </a>
                        @else
                            <p class="text-muted small mb-3">
                                Comece por registar o primeiro egresso.
                            </p>
                            <a href="{{ route('admin.egressos.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Novo Egresso
                            </a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>