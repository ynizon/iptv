<x-app-layout>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <div class="container-fluid py-4">
            <div class="mt-4 row">
                <div class="col-12">
                    <a href="/settings" style="text-align: left">< {{__("Back")}}</a>
                    <div class="card">
                        <div class="pb-0 card-header">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="">{{__('Visible categories')}}</h5>
                                    <p class="mb-0 text-sm">
                                        {{__('Only the checked categories are shown. New categories from the playlist stay hidden until you check them. If nothing is checked, every category is shown.')}}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-12 px-4">
                                @if (session('success'))
                                    <div class="alert alert-success" role="alert" id="alert">
                                        {{ session('success') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <form action="{{ route('visible-categories.store') }}" method="post" class="px-4 pb-4">
                            @csrf
                            <div class="table-responsive">
                                <table class="table text-secondary">
                                    <tbody>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>
                                                <label class="d-flex px-2 mb-0 text-sm" style="cursor: pointer">
                                                    <input type="checkbox" name="categories[]" value="{{$category->category}}"
                                                           @if (isset($visibles[$category->category])) checked @endif>
                                                    &nbsp;{{$category->category != '' ? $category->category : __('(no category)')}}
                                                    &nbsp;<span class="text-muted">({{$category->nb}})</span>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-dark">{{__('Save')}}</button>
                        </form>
                    </div>
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>
</x-app-layout>
