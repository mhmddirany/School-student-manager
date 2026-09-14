<div class="card shadow-sm" style="max-width: 400px; width: 100%;">
    <div class="card-body p-4">
        <h4 class="mb-3 text-center">Student Manager</h4>

        <form wire:submit="authenticate" novalidate>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" wire:model.blur="email" class="form-control @error('email') is-invalid @enderror">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" wire:model.blur="password" class="form-control @error('password') is-invalid @enderror">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button class="btn btn-primary w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="authenticate">Log In</span>
                <span wire:loading wire:target="authenticate">Logging in...</span>
            </button>
        </form>

        <hr>
        <p class="small text-muted mb-1">Demo accounts (password shown = password):</p>
        <ul class="small text-muted mb-0">
            <li>admin@example.com / admin123 (admin — full access)</li>
            <li>staff@example.com / staff123 (staff — manage students only)</li>
            <li>viewer@example.com / viewer123 (viewer — read-only)</li>
        </ul>
    </div>
</div>
