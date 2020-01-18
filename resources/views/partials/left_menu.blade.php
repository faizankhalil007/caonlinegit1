<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">CA Online Test</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Interface
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    {{--<li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-cog"></i>
            <span>Class</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Custom Components:</h6>
                <a class="collapse-item" href="buttons.html">Buttons</a>
                <a class="collapse-item" href="cards.html">Cards</a>
            </div>
        </div>
    </li>--}}

    <!-- Nav Item - Utilities Collapse Menu -->
    {{--<li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Utilities</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Custom Utilities:</h6>
                <a class="collapse-item" href="utilities-color.html">Colors</a>
                <a class="collapse-item" href="utilities-border.html">Borders</a>
                <a class="collapse-item" href="utilities-animation.html">Animations</a>
                <a class="collapse-item" href="utilities-other.html">Other</a>
            </div>
        </div>
    </li>--}}

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    {{--<div class="sidebar-heading">
        Addons
    </div>--}}

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('students_list') }}">
            <i class="fas fa-fw fa-table"></i>
            <span>Students List</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fw fa-folder"></i>
            <span>Modules</span>
        </a>
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Module Menu:</h6>
                <a class="collapse-item" href="{{ route('classes') }}">Module List</a>
                <a class="collapse-item" href="{{ route('add_class') }}">Add Module</a>
            </div>
        </div>
    </li>
    {{-- courses --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-folder"></i>
            <span>Courses</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Course Menu:</h6>
                <a class="collapse-item" href="{{ route('courses-list') }}">Courses</a>
                <a class="collapse-item" href="{{ route('add_courses') }}">Add Course</a>
            </div>
            {{-- start course schedule menu --}}
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Schedule Menu:</h6>
                <a class="collapse-item" href="{{ route('schedule_list') }}">Schedule</a>
                <a class="collapse-item" href="{{ route('add_schedule') }}">Add Schedule</a>
            </div>
        </div>
    </li>
    {{-- Quizes --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseQuizes" aria-expanded="true" aria-controls="collapseQuizes">
            <i class="fas fa-fw fa-folder"></i>
            <span>Quizes</span>
        </a>
        <div id="collapseQuizes" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Quiz Menu:</h6>
                <a class="collapse-item" href="{{ route('quiz_list') }}">Quiz</a>
                <a class="collapse-item" href="{{ route('add_quiz') }}">Add Quiz</a>
                <a class="collapse-item" href="{{ route('quiz-answer') }}">Quiz Answer</a>
                <a class="collapse-item" href="{{ route('quiz-request') }}">Quiz Request</a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->