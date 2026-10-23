<x-settings.page :title="__('Filters')" :subtitle="__('Hide every category whose name contains one of these words, for example the categories of other countries.')" wide>
    <x-slot name="actions">
        <a href="/filters/create" class="set-btn set-btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            {{__('New filter')}}
        </a>
    </x-slot>

    <div class="set-list">
        @forelse ($filters as $filter)
            <div class="set-list-row">
                <a href="/filters/{{$filter->id}}/edit" class="set-item">
                    <span class="set-item-main"><strong>{{$filter->name}}</strong></span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </a>
            </div>
        @empty
            <div class="set-empty">{{__('No filter yet.')}}</div>
        @endforelse
    </div>
</x-settings.page>
