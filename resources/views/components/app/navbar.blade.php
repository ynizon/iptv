<nav class="navbar navbar-main navbar-expand-lg mx-lg-5 px-0 shadow-none rounded" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-1 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">{{config("app.name")}}</a></li>
                <li class="breadcrumb-item text-sm text-dark active" aria-current="page">{{get_title()}}</li>
            </ol>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                <div class="input-group">

                </div>
            </div>
            <div class="mb-0 font-weight-bold breadcrumb-text text-white">
                <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-dark mb-0 me-1" title="{{__("Add Task")}}">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <ul class="navbar-nav  justify-content-end">
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </li>

                @if (count(Auth::user()->getReminderTasks()) > 0)
                <li class="px-3 nav-item dropdown pe-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <svg height="22" width="22" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="color:#774dd3"
                            fill="currentColor" class="cursor-pointers">
                            <path fill-rule="evenodd"
                                d="M5.25 9a6.75 6.75 0 0113.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 01-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 11-7.48 0 24.585 24.585 0 01-4.831-1.244.75.75 0 01-.298-1.205A8.217 8.217 0 005.25 9.75V9zm4.502 8.9a2.25 2.25 0 104.496 0 25.057 25.057 0 01-4.496 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4"
                        aria-labelledby="dropdownMenuButton">
                        @foreach (Auth::user()->getReminderTasks() as $notificationId => $reminder)
                            <li class="mb-2">
                                <a class="dropdown-item border-radius-md"
                                    @if (Auth::user()->id == $reminder->user_id)
                                        href="/tasks/{{$reminder->id}}/edit?notif={{$notificationId}}"
                                    @else
                                       href="/tasks/{{$reminder->id}}?notif={{$notificationId}}"
                                    @endif
                                >
                                    <div class="d-flex py-1">
                                        <div class="my-auto">
                                            <i class="fa @if (Auth::user()->id == $reminder->user_id)
                                            {{$reminder->category->icon}}
                                             @else fa-group @endif avatar avatar-sm border-radius-sm  me-3 "
                                            style="background:{{$reminder->category->color}}"></i>
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="text-sm font-weight-normal mb-1">
                                                <span class="font-weight-bold">{{$reminder->name}}</span>
                                            </h6>
                                            <p class="text-xs text-secondary mb-0 d-flex align-items-center ">
                                                <i class="pad fa fa-clock opacity-6 me-1"></i>
                                                {{formatDate($reminder->reminder_date)}}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                @endif
                <li class="nav-item ps-2 d-flex align-items-center">
                    <a href="/profile" class="nav-link text-body p-0">
                        <img src="{{gravatar(Auth::user()->email)}}" class="avatar avatar-sm" alt="avatar" />
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
