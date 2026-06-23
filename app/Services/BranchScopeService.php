<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BranchScopeService
{
    public function user(): ?User
    {
        return auth()->user();
    }

    public function canAccessAllBranches(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        return $user->role === 'admin' || (bool) $user->can_access_all_branches;
    }

    public function branchId(): ?int
    {
        $user = $this->user();

        return $user?->branch_id;
    }

    public function apply(Builder $query, string $column = 'branch_id'): Builder
    {
        if ($this->canAccessAllBranches()) {
            return $query;
        }

        if ($branchId = $this->branchId()) {
            return $query->where($column, $branchId);
        }

        return $query;
    }

    public function assertBranchAccess(int $branchId): void
    {
        if ($this->canAccessAllBranches()) {
            return;
        }

        if ($this->branchId() && $this->branchId() !== $branchId) {
            throw new \RuntimeException('You do not have access to this branch.');
        }
    }

    public function defaultBranchId(): ?int
    {
        return $this->branchId();
    }
}
