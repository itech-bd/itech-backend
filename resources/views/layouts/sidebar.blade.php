@php
    $user = Auth::user();
    $isAdmin = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;
    $isMentor = $user && method_exists($user, 'hasRole') ? $user->hasRole('mentor') : false;
    $isStudent = $user && method_exists($user, 'hasRole') ? $user->hasRole('student') : false;

    $canManageCourses = $user && ($user->can('addCourse') || $user->can('editCourse') || $user->can('deleteCourse'));
    $canSeeMentorBatches = $user && $user->can('readBatch') && $user->can('addClassSchedule') && ! $user->can('addBatch');
    $canSeeStudentPanel = $user && $user->can('readBatch') && $user->can('readCourse') && ! $user->can('addBatch') && ! $user->can('addClassSchedule');
    $canManageReviews = $user && ($user->can('addReview') || $user->can('editReview') || $user->can('deleteReview'));

    $navItem = 'group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold transition-all duration-200';
    $active = 'bg-white text-[#2E3192] shadow-sm';
    $inactive = 'text-white/72 hover:bg-white/10 hover:text-white';
    $iconBase = 'grid h-9 w-9 shrink-0 place-items-center rounded-xl transition';
    $iconActive = 'bg-[#2E3192]/10 text-[#2E3192]';
    $iconInactive = 'bg-white/10 text-white/80 group-hover:bg-white/15 group-hover:text-white';
@endphp

<nav class="space-y-6">
    <div>
        <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-[0.22em] text-white/35">Main</div>
        <div class="space-y-1.5">
            @php $isActive = request()->routeIs('dashboard'); @endphp
            <a href="/dashboard" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-house"></i></span>
                <span>Dashboard</span>
            </a>

            @php $isActive = request()->routeIs('profile.*'); @endphp
            <a href="/profile" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-user"></i></span>
                <span>Profile</span>
            </a>
        </div>
    </div>

    @if($isStudent || $canSeeStudentPanel)
        <div>
            <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-[0.22em] text-white/35">Learning</div>
            <div class="space-y-1.5">
                @php $isActive = request()->routeIs('dashboard.student.courses.*'); @endphp
                <a href="/dashboard/student/courses" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-graduation-cap"></i></span>
                    <span>My Courses</span>
                </a>

                @php $isActive = request()->routeIs('dashboard.student.batches.*'); @endphp
                <a href="/dashboard/student/batches" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-layer-group"></i></span>
                    <span>My Batches</span>
                </a>

                @php $isActive = request()->routeIs('dashboard.student.mentors.*'); @endphp
                <a href="/dashboard/student/mentors" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-chalkboard-user"></i></span>
                    <span>My Mentors</span>
                </a>

                @php $isActive = request()->routeIs('dashboard.student.invoices.*'); @endphp
                <a href="/dashboard/student/invoices" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                    <span>Invoices</span>
                </a>
            </div>
        </div>
    @endif

    @if($isMentor || $canSeeMentorBatches)
        <div>
            <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-[0.22em] text-white/35">Mentor</div>
            <div class="space-y-1.5">
                @php $isActive = request()->routeIs('dashboard.mentor.batches.*'); @endphp
                <a href="/dashboard/mentor/batches" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-users-rectangle"></i></span>
                    <span>My Batches</span>
                </a>
            </div>
        </div>
    @endif

    @if($isAdmin || $canManageCourses || $canManageReviews || ($user && $user->can('readReview')) || ($user && $user->can('readMentor')))
        <div>
            <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-[0.22em] text-white/35">Management</div>
            <div class="space-y-1.5">
                @if($isAdmin || $canManageCourses)
                    @php $isActive = request()->routeIs('dashboard.courses.*'); @endphp
                    <a href="/dashboard/courses" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                        <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-book-open"></i></span>
                        <span>Courses</span>
                    </a>
                @endif

                @if($isAdmin)
                    @php $isActive = request()->routeIs('dashboard.batches.*') || request()->routeIs('dashboard.courses.batches.*'); @endphp
                    <a href="/dashboard/batches" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                        <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-calendar-days"></i></span>
                        <span>Batches</span>
                    </a>

                    @php $isActive = request()->routeIs('dashboard.admin.invoices.*'); @endphp
                    <a href="/dashboard/admin/invoices" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                        <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-receipt"></i></span>
                        <span>Invoices</span>
                    </a>

                    @php $isActive = request()->routeIs('dashboard.contact-messages.*'); @endphp
                    <a href="{{ route('dashboard.contact-messages.index') }}" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                        <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-envelope"></i></span>
                        <span>Messages</span>
                    </a>
                @endif

                @if($isAdmin || $canManageReviews || ($user && $user->can('readReview')))
                    @php $isActive = request()->routeIs('dashboard.reviews.*'); @endphp
                    <a href="/dashboard/reviews" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                        <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-star"></i></span>
                        <span>Reviews</span>
                    </a>
                @endif

                @if($user && $user->can('readMentor') && ! ($isStudent || $canSeeStudentPanel))
                    @php $isActive = request()->routeIs('dashboard.mentors.*'); @endphp
                    <a href="/dashboard/mentors" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                        <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-user-tie"></i></span>
                        <span>Mentors</span>
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if($isAdmin)
        <div>
            <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-[0.22em] text-white/35">System</div>
            <div class="space-y-1.5">
                @php $isActive = request()->routeIs('users.*'); @endphp
                <a href="/users" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-users"></i></span>
                    <span>Users</span>
                </a>

                @php $isActive = request()->routeIs('admin.frontend-editor.*'); @endphp
                <a href="/admin/frontend-editor" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-pen-ruler"></i></span>
                    <span>Frontend Editor</span>
                </a>

                @php $isActive = request()->routeIs('dashboard.admin.news.*'); @endphp
                <a href="{{ route('dashboard.admin.news.index') }}" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-newspaper"></i></span>
                    <span>News &amp; Updates</span>
                </a>

                @php $isActive = request()->routeIs('roles.*'); @endphp
                <a href="/roles" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-user-shield"></i></span>
                    <span>Roles</span>
                </a>

                @php $isActive = request()->routeIs('permissions.*'); @endphp
                <a href="/permissions" @click="sidebarOpen = false" class="{{ $navItem }} {{ $isActive ? $active : $inactive }}">
                    <span class="{{ $iconBase }} {{ $isActive ? $iconActive : $iconInactive }}"><i class="fa-solid fa-key"></i></span>
                    <span>Permissions</span>
                </a>
            </div>
        </div>
    @endif
</nav>
