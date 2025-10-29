<div class="form-group mb-2">
    <label>Business Name</label>
    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $user->business_name ?? '') }}">
</div>

<div class="form-group mb-2">
    <label>Owner Name</label>
    <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $user->owner_name ?? '') }}">
</div>

<div class="form-group mb-2">
    <label>User Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}">
</div>

<div class="form-group mb-2">
    <label>Contact</label>
    <input type="text" name="contact" class="form-control" value="{{ old('contact', $user->contact ?? '') }}">
</div>

<div class="form-group mb-2">
    <label>Address</label>
    <input type="text" name="address" class="form-control" value="{{ old('address', $user->address ?? '') }}">
</div>

<div class="form-group mb-2">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}">
</div>

<div class="form-group mb-2">
    <label>Role</label>
    <select name="role" class="form-control">
        <option value="agent" {{ isset($user) && $user->is_agent ? 'selected' : '' }}>Agent</option>
        <option value="admin" {{ isset($user) && $user->is_admin ? 'selected' : '' }}>Admin</option>
    </select>
</div>

<div class="form-group mb-2">
    <label>Status</label>
    <select name="status" class="form-control">
        <option value="1" {{ isset($user) && $user->active ? 'selected' : '' }}>Active</option>
        <option value="0" {{ isset($user) && !$user->active ? 'selected' : '' }}>Inactive</option>
    </select>
</div>

<div class="form-group mb-2">
    <label>Business Logo</label>
    <input type="file" name="business_logo" class="form-control">
    @if(isset($user->business_logo))
    <img src="{{ Storage::url($user->business_logo) }}" width="60">
    @endif
</div>


<div class="form-group mb-3">
    <label>@if($edit)
        Change @endif Password</label>
    <input type="password" name="password" class="form-control">
    <label>Confirm Password</label>
    <input type="password" name="password_confirmation" class="form-control mt-1" placeholder="Confirm Password">
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');

        const matchIcon = document.createElement('span');
        matchIcon.style.marginLeft = '10px';

        function validatePassword() {
            if (password.value && confirmPassword.value) {
                if (password.value === confirmPassword.value) {
                    matchIcon.textContent = '✔️';
                    matchIcon.style.color = 'green';
                } else {
                    matchIcon.textContent = '❌';
                    matchIcon.style.color = 'red';
                }
            } else {
                matchIcon.textContent = '';
            }
        }

        if (password && confirmPassword) {
            confirmPassword.parentNode.appendChild(matchIcon);
            password.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);
        }
    });

</script>
