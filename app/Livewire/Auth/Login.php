<?php

namespace App\Livewire\Auth;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    public function authenticate(): void
    {
        // Required-field + format validation. If either field is empty this
        // throws immediately and re-renders with errors — authenticate() never
        // reaches Auth::attempt() at all. Same mechanism as StudentForm::save().
        $this->validate();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->addError('email', 'Those credentials do not match our records.');
            return;
        }

        request()->session()->regenerate();
        ActivityLog::record('login', null, 'User logged in');

        $this->redirect('/dashboard', navigate: true);
    }
}
