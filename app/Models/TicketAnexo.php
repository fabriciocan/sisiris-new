<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAnexo extends Model
{
    protected $table = 'ticket_anexos';

    protected $fillable = [
        'ticket_id',
        'nome_arquivo',
        'caminho_arquivo',
        'tipo_arquivo',
        'tamanho',
        'uploaded_by',
    ];

    /**
     * Relacionamento: Anexo pertence a um ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relacionamento: Anexo foi enviado por um usuário
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Accessor: Tamanho formatado
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
