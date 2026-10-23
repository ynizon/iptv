<x-settings.page :title="$filter->id > 0 ? __('Edit the filter') : __('New filter')" back="/filters" :backLabel="__('Filters')">
    <form class="set-card" method="post"
          action="{{ $filter->id > 0 ? route('filters.update', $filter->id) : route('filters.store') }}">
        @if ($filter->id > 0)
            @method('PUT')
        @endif
        @csrf
        <div class="set-field">
            <label for="name">{{__('Word to hide')}}</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $filter->name) }}" required autofocus aria-describedby="name-help">
            <p class="set-help" id="name-help">{{__('Every category whose name contains this word is hidden, for example ITALIA. Upper and lower case do not matter.')}}</p>
        </div>
        <div class="set-actions">
            <button type="submit" class="set-btn set-btn-primary">{{__('Save')}}</button>
            <a href="/filters" class="set-btn set-btn-ghost">{{__('Cancel')}}</a>
        </div>
    </form>

    @if ($filter->id > 0)
        <section class="set-card set-danger" aria-label="{{__('Delete')}}">
            <div class="set-danger-row">
                <p>{{__('The categories hidden by this filter are shown again.')}}</p>
                <form action="{{ route('filters.destroy', $filter->id) }}" method="post" onsubmit="return confirm(@js(__('Delete this filter?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="set-btn set-btn-danger">{{__('Delete the filter')}}</button>
                </form>
            </div>
        </section>
    @endif
</x-settings.page>
