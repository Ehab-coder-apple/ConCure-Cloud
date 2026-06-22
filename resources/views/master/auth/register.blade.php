<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConCure Master - Create Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #dc3545 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-master {
            background: rgba(255,255,255,0.96);
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(0,0,0,0.25);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
        }
        .card-master-header {
            background: linear-gradient(135deg, #dc3545 0%, #6f42c1 100%);
            color: #fff;
            padding: 1.75rem 2rem;
            text-align: center;
        }
        .card-master-header .icon {
            font-size: 2.4rem;
            margin-bottom: .5rem;
        }
        .card-master-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
<div class="card-master">
    <div class="card-master-header">
        <div class="icon"><i class="fas fa-crown"></i></div>
        <h3 class="mb-0">ConCure Master</h3>
        <small class="opacity-75">Initial Super Admin Setup</small>
    </div>
    <div class="card-master-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('master.register') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Phone (optional)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-12 d-grid mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-shield me-1"></i>
                        Create Super Admin
                    </button>
                </div>
                <div class="col-12 text-center mt-2">
                    <small class="text-muted">This screen is only available until the first super admin is created.</small>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
