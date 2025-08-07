<div class="form-group mb-2">
    <label>Business Name</label>
    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $user->business_name ?? '') }}">
</div>

<div class="form-group mb-2">
    <label>Owner Name</label>
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
        <option value="admin" {{ isset($user) && $user->is_admin ? 'selected' : '' }}>Admin</option>
        <option value="agent" {{ isset($user) && $user->is_agent ? 'selected' : '' }}>Agent</option>
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
    <img src="{{ asset('images/Agents_logo/' . $user->business_logo) }}" width="60">
    @endif
</div>

@if(!$edit)
<div class="form-group mb-3">
    <label>Password</label>
    <input type="password" name="password" class="form-control">
    <label>Confirm Password</label>
    <input type="password" name="password_confirmation" class="form-control mt-1" placeholder="Confirm Password">
</div>
@endif
@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');

        const matchIcon = document.createElement('span');
        matchIcon.style.marginLeft = '10px';

        confirmPassword.parentNode.insertBefore(matchIcon, confirmPassword.nextSibling);

        confirmPassword.addEventListener('input', () => {
            if (confirmPassword.value === "") {
                matchIcon.innerHTML = "";
            } else if (confirmPassword.value === password.value) {
                matchIcon.innerHTML = "✅";
                matchIcon.style.color = "green";
            } else {
                matchIcon.innerHTML = "❌";
                matchIcon.style.color = "red";
            }
        });
    });

</script>
