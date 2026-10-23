<x-settings.page :title="$user->id > 0 ? __('Edit the user') : __('New user')" back="/users" :backLabel="__('Users')">
    <form class="set-card" method="post"
          action="{{ $user->id > 0 ? route('users.update', $user->id) : route('users.store') }}">
        @if ($user->id > 0)
            @method('PUT')
        @endif
        @csrf
        <div class="set-row">
            <div class="set-field">
                <label for="name">{{__('Name')}}</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus>
            </div>
            <div class="set-field">
                <label for="email">{{__('Email')}}</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
        </div>
        @if ($user->id == 0)
            <div class="set-field">
                <label for="password">{{__('Password')}}</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
            </div>
        @endif
        <div class="set-field">
            <label for="admin">{{__('Role')}}</label>
            <select id="admin" name="admin" class="form-control" aria-describedby="admin-help">
                <option value="0" @selected(!old('admin', $user->admin))>{{__("User")}}</option>
                <option value="1" @selected(old('admin', $user->admin))>{{__("Admin")}}</option>
            </select>
            <p class="set-help" id="admin-help">{{__('An administrator manages the users.')}}</p>
        </div>
        <div class="set-actions">
            <button type="submit" class="set-btn set-btn-primary">{{__('Save')}}</button>
            <a href="/users" class="set-btn set-btn-ghost">{{__('Cancel')}}</a>
        </div>
    </form>
</x-settings.page>
