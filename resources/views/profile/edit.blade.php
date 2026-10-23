<x-settings.page :title="__('My account')">
    <form class="set-card" method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')
        <h2>{{__('Profile Information')}}</h2>
        <div class="set-row">
            <div class="set-field">
                <label for="name">{{__('Name')}}</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                @error('name')<p class="set-help set-error">{{ $message }}</p>@enderror
            </div>
            <div class="set-field">
                <label for="email">{{__('Email')}}</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                @error('email')<p class="set-help set-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="set-actions">
            <button type="submit" class="set-btn set-btn-primary">{{__('Save')}}</button>
            @if (session('status') === 'profile-updated')
                <span class="set-help" role="status">{{__('Saved.')}}</span>
            @endif
        </div>
    </form>

    <form class="set-card" method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')
        <h2>{{__('Update Password')}}</h2>
        <p>{{__('Ensure your account is using a long, random password to stay secure.')}}</p>
        <div class="set-field">
            <label for="current_password">{{__('Current Password')}}</label>
            <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password">
            @foreach ($errors->updatePassword->get('current_password') as $message)<p class="set-help set-error">{{ $message }}</p>@endforeach
        </div>
        <div class="set-row">
            <div class="set-field">
                <label for="password">{{__('New Password')}}</label>
                <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                @foreach ($errors->updatePassword->get('password') as $message)<p class="set-help set-error">{{ $message }}</p>@endforeach
            </div>
            <div class="set-field">
                <label for="password_confirmation">{{__('Confirm Password')}}</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                @foreach ($errors->updatePassword->get('password_confirmation') as $message)<p class="set-help set-error">{{ $message }}</p>@endforeach
            </div>
        </div>
        <div class="set-actions">
            <button type="submit" class="set-btn set-btn-primary">{{__('Save')}}</button>
            @if (session('status') === 'password-updated')
                <span class="set-help" role="status">{{__('Saved.')}}</span>
            @endif
        </div>
    </form>

    <form class="set-card set-danger" method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm(@js(__('Are you sure you want to delete your account?')))">
        @csrf
        @method('delete')
        <h2>{{__('Delete Account')}}</h2>
        <p>{{__('Once your account is deleted, all of its resources and data will be permanently deleted.')}}</p>
        <div class="set-danger-row">
            <div class="set-field" style="flex: 1 1 260px;">
                <label for="delete_password">{{__('Password')}}</label>
                <input type="password" class="form-control" id="delete_password" name="password" required autocomplete="current-password">
                @foreach ($errors->userDeletion->get('password') as $message)<p class="set-help set-error">{{ $message }}</p>@endforeach
            </div>
            <button type="submit" class="set-btn set-btn-danger" style="align-self: flex-end;">{{__('Delete Account')}}</button>
        </div>
    </form>
</x-settings.page>
