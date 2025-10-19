<?php

namespace App\Services\Actions;

use App\Models\Protocolo;
use App\Models\User;

interface ProtocoloActionInterface
{
    /**
     * Execute the action for the protocol step
     */
    public function execute(Protocolo $protocolo, User $user, array $data = []): void;
}