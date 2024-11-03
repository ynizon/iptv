<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 bg-slate-900 fixed-start " id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <div class="navbar-brand d-flex align-items-center m-0">
            <a href="/">
                <span class="font-weight-bold text-lg">{{config("app.name")}}</span>
            </a>
            <a href="/settings"><i class="fa fa-cog" style="padding-left:50px;color:#ffffff"></i></a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="login" onclick="event.preventDefault();
                            this.closest('form').submit();">
                    <i class="fa fa-sign-out-alt" style="padding-left:10px;color:#ffffff"></i>
                </a>
            </form>
        </div>
    </div>
    <div class="collapse navbar-collapse px-4  w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <x-categories></x-categories>
        </ul>
    </div>
</aside>
