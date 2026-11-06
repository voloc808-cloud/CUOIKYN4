@if(($paginator['last_page'] ?? 1) > 1)
<nav><ul class="pagination mb-0">@for($page = 1; $page <= $paginator['last_page']; $page++)<li class="page-item {{ $page === $paginator['current_page'] ? 'active' : '' }}"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page]) }}">{{ $page }}</a></li>@endfor</ul></nav>
@endif
