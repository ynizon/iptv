<x-settings.page :title="$category->id > 0 ? __('Edit the rule') : __('New rule')" back="/categories" :backLabel="__('Categories')">
    <form class="set-card" method="post"
          action="{{ $category->id > 0 ? route('categories.update', $category->id) : route('categories.store') }}">
        @if ($category->id > 0)
            @method('PUT')
        @endif
        @csrf
        <div class="set-field">
            <label for="from">{{__('Text to replace')}}</label>
            <input type="text" class="form-control" id="from" name="from" value="{{ old('from', $category->from) }}" required autofocus aria-describedby="from-help">
            <p class="set-help" id="from-help">{{__('Searched in every category name, exactly as written. Example: BRAQUAGE & ARNAQUE')}}</p>
        </div>
        <div class="set-field">
            <label for="to">{{__('Replace with')}}</label>
            <input type="text" class="form-control" id="to" name="to" value="{{ old('to', $category->to) }}" aria-describedby="to-help">
            <p class="set-help" id="to-help">{{__('Leave empty to remove the text. Example: BRAQUAGE, to group it with the BRAQUAGE category.')}}</p>
        </div>
        <div class="set-actions">
            <button type="submit" class="set-btn set-btn-primary">{{__('Save')}}</button>
            <a href="/categories" class="set-btn set-btn-ghost">{{__('Cancel')}}</a>
        </div>
    </form>

    @if ($category->id > 0)
        <section class="set-card set-danger" aria-label="{{__('Delete')}}">
            <div class="set-danger-row">
                <p>{{__('The categories get their original name back.')}}</p>
                <form action="{{ route('categories.destroy', $category->id) }}" method="post" onsubmit="return confirm(@js(__('Delete this rule?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="set-btn set-btn-danger">{{__('Delete the rule')}}</button>
                </form>
            </div>
        </section>
    @endif
</x-settings.page>
