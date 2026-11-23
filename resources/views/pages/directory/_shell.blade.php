{{-- Shared page shell for Contacts / Companies.
     Vars: $dirTitle, $dirSub, $dirCreate, $dirSearch, $dirNoun, $isSuperAdmin --}}
<style>
  .page-content:has(.lh-wrap) { padding-bottom: 28px; }
  #dirChips { display: contents; }
  .lh-wrap { --lh-top: 300px; }
</style>

<div class="page-content">
<div class="lh-wrap" id="lhWrap">

  <!-- Header -->
  <div class="lh-head">
    <div class="grow">
      <h1>{{ $dirTitle }} <span class="cnt" id="lhCount">0</span></h1>
      <p>{{ $dirSub }}</p>
    </div>
    <button type="button" class="lh-create" id="dirCreateBtn"><i class="bi bi-plus-lg"></i><span class="t">{{ $dirCreate }}</span></button>
  </div>

  <!-- Tabs -->
  <div class="lh-tabs" id="lhTabs"></div>

  <!-- Toolbar -->
  <div class="lh-toolbar">
    <div class="lh-search">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" id="lhSearch" placeholder="{{ $dirSearch }}" autocomplete="off">
      <kbd>/</kbd>
    </div>
    <div id="dirChips"></div>
    <button type="button" class="lh-chip" id="lhChipDate"><i class="bi bi-calendar3"></i><span>Create date</span><i class="bi bi-chevron-down"></i></button>
    @if($isSuperAdmin)
    <button type="button" class="lh-chip" id="lhChipOwner"><i class="bi bi-people"></i><span>All users</span><i class="bi bi-chevron-down"></i></button>
    @endif
    <button type="button" class="lh-link d-none" id="lhClear">Clear all</button>
    <div class="lh-spacer"></div>
    <button type="button" class="lh-ghost" id="lhColsBtn"><i class="bi bi-layout-three-columns"></i><span>Edit columns</span></button>
  </div>

  <!-- Bulk bar -->
  <div class="lh-bulk" id="lhBulk">
    <b><span id="lhBulkCount">0</span> selected</b>
    <button type="button" class="lh-ghost" id="lhBulkDelete" style="color:var(--danger)"><i class="bi bi-trash"></i>Delete</button>
    <button type="button" class="lh-link ms-auto" id="lhBulkClear">Clear selection</button>
  </div>

  <!-- Table -->
  <div class="lh-card" id="lhTableWrap">
    <div class="lh-scroll" id="lhScroll">
      <table class="lh-table">
        <thead id="lhThead"></thead>
        <tbody id="lhTbody"></tbody>
      </table>
    </div>
    <div class="lh-foot" id="lhFoot">
      <span id="lhPageInfo"></span>
      <span>Rows: <select class="form-select form-select-sm" id="lhPageSize"><option>10</option><option>25</option><option>50</option><option>100</option></select></span>
      <div class="lh-pages" id="lhPages"></div>
    </div>
  </div>

</div>
</div>

<!-- Record drawer (content is rendered by DirUI) -->
<div class="offcanvas offcanvas-end lh-drawer" tabindex="-1" id="lhDrawer" aria-label="{{ $dirNoun }} details">
  <div class="offcanvas-header"><h5 class="offcanvas-title">{{ $dirNoun }}</h5></div>
  <div class="offcanvas-body">
    <div class="lh-dr-top">
      <div id="lhDrTop"></div>
      <div class="lh-dr-tabs" id="lhDrTabs"></div>
    </div>
    <div class="lh-dr-body" id="lhDrBody"></div>
  </div>
</div>

<!-- Edit columns -->
<div class="modal fade" id="lhColsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit columns</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="lh-cols">
          <div>
            <h6 style="margin-top:0">Available properties</h6>
            <input type="text" class="form-control mb-2" id="lhColsSearch" placeholder="Search properties...">
            <div class="lst" id="lhColsAvail"></div>
          </div>
          <div>
            <h6 style="margin-top:0">Selected columns (<span id="lhColsN">0</span>)</h6>
            <div class="lst" id="lhColsSel"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary me-auto" id="lhColsReset">Reset to default</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete confirm -->
<div class="modal fade" id="lhDeleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-content-compact">
    <div class="modal-content modal-content-danger">
      <div class="modal-header">
        <h5 class="modal-title" id="lhDeleteTitle">Delete?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body"><p class="mb-0" id="lhDeleteText"></p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>