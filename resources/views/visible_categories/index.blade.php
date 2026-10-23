<x-settings.page :title="__('Visible categories')" :subtitle="__('Only the checked categories are shown. New categories from the playlist stay hidden until you check them. If nothing is checked, every category is shown.')" wide>
    <form action="{{ route('visible-categories.store') }}" method="post" class="set-card" style="padding: 0; gap: 0;">
        @csrf
        <div class="set-toolbar" style="padding: 16px 20px; border-bottom: 1px solid #232831;">
            <label for="category-search" class="visually-hidden">{{__('Search a category')}}</label>
            <input type="search" id="category-search" class="form-control" placeholder="{{__('Search a category')}}">
            <button type="button" class="set-btn set-btn-ghost" data-check="1">{{__('Check all')}}</button>
            <button type="button" class="set-btn set-btn-ghost" data-check="0">{{__('Uncheck all')}}</button>
        </div>
        <div class="set-checklist">
            @foreach ($categories as $category)
                <label data-name="{{ mb_strtolower($category->category) }}">
                    <input type="checkbox" name="categories[]" value="{{$category->category}}" @checked(isset($visibles[$category->category]))>
                    <span>{{ $category->category != '' ? $category->category : __('(no category)') }}</span>
                    <span class="set-count">{{ number_format($category->nb, 0, ',', ' ') }}</span>
                </label>
            @endforeach
        </div>
        <div class="set-sticky" style="padding: 16px 20px; border-top: 1px solid #232831; border-radius: 0 0 16px 16px; background: #151920;">
            <div class="set-actions">
                <button type="submit" class="set-btn set-btn-primary">{{__('Save')}}</button>
                <span class="set-help" id="checked-count"></span>
            </div>
        </div>
    </form>

    <script>
        (function () {
            const items = Array.from(document.querySelectorAll(".set-checklist label"));
            const count = document.getElementById("checked-count");
            const updateCount = () => {
                count.textContent = @js(__(':count checked categories')).replace(":count", items.filter((item) => item.querySelector("input").checked).length);
            };
            document.getElementById("category-search").addEventListener("input", (event) => {
                const query = event.target.value.trim().toLowerCase();
                items.forEach((item) => { item.hidden = query !== "" && !item.dataset.name.includes(query); });
            });
            // Only the categories shown by the search
            document.querySelectorAll("[data-check]").forEach((button) => button.addEventListener("click", () => {
                items.filter((item) => !item.hidden).forEach((item) => { item.querySelector("input").checked = button.dataset.check === "1"; });
                updateCount();
            }));
            items.forEach((item) => item.querySelector("input").addEventListener("change", updateCount));
            updateCount();
        })();
    </script>
</x-settings.page>
