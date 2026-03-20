<div class="admin-toolbar-meta">Page {{ $currentPage }} of {{ $totalPages }}</div>
<div class="admin-pagination__controls">
    <button class="admin-button" data-page-nav="prev" @disabled($currentPage <= 1)>Previous</button>
    <button class="admin-button admin-button--primary" data-page-nav="next" @disabled($currentPage >= $totalPages)>Next</button>
</div>
