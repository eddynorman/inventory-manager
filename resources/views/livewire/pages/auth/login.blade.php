<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();

        if (! $user->canAccess('dashboard.view')) {

            $this->redirect(
                route('restricted', absolute: false),
                navigate: true
            );

            return;
        }

        $this->redirectIntended(default: route('dashboard', absolute: false));
    }
};
?>
<div>
    <style>

        body{
            background: #f8fafc;
            overflow-x: hidden;
        }

        .erp-auth-page{
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(37,99,235,.08), transparent 35%),
                radial-gradient(circle at bottom right, rgba(15,23,42,.08), transparent 30%),
                #f8fafc;
        }

        .erp-hero-section{
            position: relative;
            overflow: hidden;
        }

        .erp-hero-content{
            padding: 5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .erp-brand-badge{
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(37,99,235,.1);
            color: #2563eb;
            border: 1px solid rgba(37,99,235,.15);
            padding: .7rem 1.1rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 700;
            width: fit-content;
        }

        .erp-hero-title{
            font-size: 4rem;
            font-weight: 900;
            line-height: 1.05;
            color: #0f172a;
            margin-bottom: 1.5rem;
            max-width: 700px;
        }

        .erp-hero-subtitle{
            font-size: 1.15rem;
            color: #64748b;
            max-width: 700px;
            line-height: 1.8;
        }

        .erp-mini-stat{
            background: white;
            border-radius: 24px;
            padding: 1.3rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(15,23,42,.04);
            transition: all .3s ease;
        }

        .erp-mini-stat:hover{
            transform: translateY(-4px);
            box-shadow: 0 18px 35px rgba(15,23,42,.08);
        }

        .erp-mini-icon{
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .erp-feature-card{
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            transition: all .3s ease;
            box-shadow: 0 10px 25px rgba(15,23,42,.04);
        }

        .erp-feature-card:hover{
            transform: translateY(-4px);
            box-shadow: 0 18px 35px rgba(15,23,42,.08);
        }

        .erp-feature-icon{
            width: 55px;
            height: 55px;
            border-radius: 16px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .erp-login-wrapper{
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .erp-login-card{
            width: 100%;
            max-width: 520px;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(14px);
            border-radius: 32px;
            padding: 3rem;
            border: 1px solid rgba(255,255,255,.4);
            box-shadow: 0 25px 60px rgba(15,23,42,.12);
            animation: fadeIn .5s ease;
        }

        .erp-top-header{
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: white;
        }

        .erp-side-card{
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            border-radius: 14px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            font-weight: 500;
            transition: .2s;
        }

        .erp-side-card:hover{
            transform: translateX(5px);
            background: #f8fafc;
        }

        .erp-module-card{
            background: white;
            border-radius: 18px;
            padding: 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: .3s;
        }

        .erp-module-card:hover{
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,.08);
        }

        .erp-module-card i{
            font-size: 28px;
            margin-bottom: 10px;
            color: #2563eb;
        }

        @keyframes fadeIn{
            from{
                opacity: 0;
                transform: translateY(10px);
            }

            to{
                opacity: 1;
                transform: translateY(0);
            }
        }

        .erp-logo{
            width: 90px;
            height: 90px;
            border-radius: 28px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            box-shadow: 0 20px 40px rgba(37,99,235,.25);
        }

        .erp-input-group{
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
            transition: all .2s ease;
        }

        .erp-input-group:focus-within{
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,.1);
            background: white;
        }

        .erp-input-group span{
            padding-left: 1rem;
            color: #64748b;
        }

        .erp-input{
            border: none !important;
            background: transparent !important;
            height: 58px;
            box-shadow: none !important;
        }

        .erp-login-btn{
            height: 58px;
            border-radius: 18px;
            border: none;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-weight: 700;
            transition: all .3s ease;
        }

        .erp-login-btn:hover{
            transform: translateY(-2px);
            box-shadow: 0 18px 30px rgba(37,99,235,.25);
            color: white;
        }

        @media(max-width: 991px){

            .erp-hero-title{
                font-size: 2.5rem;
            }

            .erp-login-wrapper{
                padding: 1rem;
            }

            .erp-login-card{
                padding: 2rem;
                border-radius: 24px;
            }
        }

    </style>
    <div class="container pb-5">

    <div class="row align-items-center justify-content-center">

            {{-- LEFT (SIMPLE INFO ONLY) --}}
            <div class="col-lg-6 d-none d-lg-block">

                <div class="pe-5">

                    <h2 class="fw-bold mb-3">
                        Welcome Back 👋
                    </h2>

                    <p class="text-muted mb-4">
                        Access your ERP dashboard to manage inventory, sales, purchases and business operations in real time.
                    </p>

                    <div class="erp-side-card mb-3">
                        <i class="bi bi-shield-check text-primary me-2"></i>
                        Secure role-based access control
                    </div>

                    <div class="erp-side-card mb-3">
                        <i class="bi bi-speedometer2 text-success me-2"></i>
                        Real-time inventory tracking
                    </div>

                    <div class="erp-side-card">
                        <i class="bi bi-diagram-3 text-warning me-2"></i>
                        Multi-module ERP architecture
                    </div>

                </div>

            </div>

            {{-- RIGHT (LOGIN BIG & FOCUSED) --}}
            <div class="col-lg-6">

                <div class="erp-login-wrapper">

                    <div class="erp-login-card">

                        {{-- LOGO --}}
                        <div class="text-center mb-4">
                            <div class="erp-logo mx-auto mb-3">
                                <i class="bi bi-boxes"></i>
                            </div>

                            <h3 class="fw-bold">
                                Sign In
                            </h3>

                            <p class="text-muted">
                                Enter your credentials to continue
                            </p>
                        </div>

                        {{-- LOGIN FORM (UNCHANGED) --}}
                        {{-- AUTH ERRORS --}}
                        @if ($errors->any())

                            <div class="alert alert-danger border-0 rounded-4 mb-4">

                                <div class="fw-semibold mb-1">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Login Failed
                                </div>

                                @foreach ($errors->all() as $error)

                                    <div class="small">
                                        {{ $error }}
                                    </div>

                                @endforeach

                            </div>

                        @endif
                        <form wire:submit="login" autocomplete="on">

                            {{-- EMAIL --}}
                            <div class="mb-3">

                                <label class="form-label fw-semibold" for="email">
                                    Email
                                </label>

                                <div class="erp-input-group">
                                    <span><i class="bi bi-envelope"></i></span>

                                    <input
                                        wire:model="form.email"
                                        type="email"
                                        id="email"
                                        name="email"
                                        autocomplete="username"
                                        class="form-control erp-input"
                                        placeholder="you@example.com"
                                        autofocus>
                                </div>

                            </div>

                            {{-- PASSWORD --}}
                            <div class="mb-3">

                                <label class="form-label fw-semibold" for="password">
                                    Password
                                </label>

                                <div class="erp-input-group">
                                    <span><i class="bi bi-lock"></i></span>

                                    <input
                                        wire:model="form.password"
                                        type="password"
                                        id="password"
                                        name="password"
                                        autocomplete="current-password"
                                        class="form-control erp-input"
                                        placeholder="••••••••">
                                </div>

                            </div>

                            {{-- REMEMBER --}}
                            <div class="d-flex justify-content-between align-items-center mb-4">

                                <div class="form-check">
                                    <input wire:model="form.remember"
                                        class="form-check-input"
                                        type="checkbox"
                                        id="remember">

                                    <label class="form-check-label small">
                                        Remember me
                                    </label>
                                </div>

                            </div>

                            <button type="submit"
                                class="btn erp-login-btn w-100">

                                <i class="bi bi-box-arrow-in-right me-2"></i>

                                Login To ERP

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>
    {{-- <div class="container py-5">

        <h4 class="fw-bold text-center mb-5">
            System Modules
        </h4>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="erp-module-card">
                    <i class="bi bi-box-seam"></i>
                    <h6>Inventory</h6>
                </div>
            </div>

            <div class="col-md-4">
                <div class="erp-module-card">
                    <i class="bi bi-cart-check"></i>
                    <h6>Sales</h6>
                </div>
            </div>

            <div class="col-md-4">
                <div class="erp-module-card">
                    <i class="bi bi-bag"></i>
                    <h6>Purchases</h6>
                </div>
            </div>

        </div>

    </div>
     --}}

</div>

