<?php
use App\Http\Controllers\Console\AuthController as Console;
use App\Http\Controllers\Console\CompanyController;
use App\Http\Controllers\Console\PlanModuleController;



use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadManageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryManageController;
use App\Http\Controllers\BillingManageController;
use App\Http\Controllers\MetaLeadController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatbotSettingController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\LeadDirectoryController;
use App\Http\Controllers\CompanyOpenController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\DealRecordController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\SignInMethodsController;
use App\Http\Controllers\SupportTicketController;

use App\Http\Controllers\Console\{
    DirectoryController,
    CashfreeSettingsController,
    InvoiceController,
    PlanController,
    SubscriptionController,
    TrialController,
    PaymentSettingsController
};
/* ==========================================================================
   Guest routes (no auth required)
   ========================================================================== */
 // Guest-only (already logged-in admin yahan nahi aa sakta)
// replace your guest group
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::prefix('admin/auth/google')->middleware('throttle:20,1')->group(function () {
        Route::get('redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
        Route::get('callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
    });
});

Route::view('/workspace-inactive', 'pages.auth.workspace-inactive')->name('workspace.inactive');


// Login (Laravel Breeze / Auth::routes() handles these — add yours if custom)
    Route::prefix('admin')->middleware(['auth:admin'])->group(function () {

Route::get('/onboarding', [WorkspaceController::class, 'show'])->middleware('super_admin')->name('workspace.onboarding');
Route::post('/onboarding', [WorkspaceController::class, 'update'])->middleware('super_admin')->name('workspace.onboarding.save');
// inside your first auth:admin group
Route::post('/profile/google/unlink', [SignInMethodsController::class, 'unlinkGoogle'])->name('admin.profile.google.unlink');
    // Route::get('/admin-manage', [PagesController::class, 'users'])->name('admin.register');
    Route::get('/', [DashboardController::class, 'index'])->name('auth.home.main');
    Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::get('users/list', [AuthController::class, 'index']);
    Route::post('users', [AuthController::class, 'store']);
    Route::get('users/{id}', [AuthController::class, 'show']);
    Route::put('users/{id}', [AuthController::class, 'update']);
    Route::delete('users/{id}', [AuthController::class, 'destroy']);
    Route::post('users/bulk-delete', [AuthController::class, 'bulkDelete']);
    Route::patch('users/{id}/toggle-status', [AuthController::class, 'toggleStatus']);

  Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');

    // Password change with email OTP
    Route::post('/profile/password/send-otp', [ProfileController::class, 'sendPasswordOtp'])
        ->middleware('throttle:6,1')
        ->name('admin.profile.password.send-otp');

    Route::post('/profile/password/verify-otp', [ProfileController::class, 'verifyPasswordOtp'])
        ->middleware('throttle:12,1')
        ->name('admin.profile.password.verify-otp');
});

Route::prefix('admin')->middleware(['auth:admin'])->group(function () {




Route::prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/',        [SupportTicketController::class, 'page'])->name('index');
    Route::get('/data',    [SupportTicketController::class, 'data'])->name('data');
    Route::get('/lookup',  [SupportTicketController::class, 'lookup'])->name('lookup');      // record picker of the create modal
    Route::get('/for/{type}/{id}', [SupportTicketController::class, 'forRecord'])->name('for'); // tickets of a deal/lead/contact/company
    Route::post('/',       [SupportTicketController::class, 'store'])->name('store');

    Route::get('/{id}',            [SupportTicketController::class, 'show'])->whereNumber('id')->name('show');
    Route::patch('/{id}',          [SupportTicketController::class, 'patch'])->whereNumber('id')->name('patch');
    Route::post('/{id}/messages',  [SupportTicketController::class, 'storeMessage'])->whereNumber('id')->name('messages.store');
    Route::delete('/{id}',         [SupportTicketController::class, 'destroy'])->whereNumber('id')->name('destroy');
});








Route::prefix('deals')->name('deals.')->group(function () {
    Route::get('/',                 [DealController::class, 'page'])->name('index');
    Route::get('/data',             [DealController::class, 'data'])->name('data');
    Route::post('/',                [DealController::class, 'store'])->name('store');
    Route::post('/bulk-delete',     [DealController::class, 'bulkDestroy'])->name('bulkDestroy');
    Route::patch('/{id}',           [DealController::class, 'patch'])->name('patch');
    Route::patch('/{id}/stage',     [DealController::class, 'updateStage'])->name('stage');
    Route::delete('/{id}',          [DealController::class, 'destroy'])->name('destroy');
    
    // activities + files (static prefixes first)
    Route::patch('/activities/{id}',   [DealRecordController::class, 'updateActivity'])->whereNumber('id')->name('activities.update');
    Route::delete('/activities/{id}',  [DealRecordController::class, 'destroyActivity'])->whereNumber('id')->name('activities.destroy');
    Route::delete('/attachments/{id}', [DealRecordController::class, 'destroyAttachment'])->whereNumber('id')->name('attachments.destroy');

    // single deal
    Route::get('/{id}',                [DealRecordController::class, 'show'])->whereNumber('id')->name('show');        // full record page
    Route::get('/{id}/record',         [DealRecordController::class, 'record'])->whereNumber('id')->name('record');    // JSON refresh
    Route::post('/{id}/activities',    [DealRecordController::class, 'storeActivity'])->whereNumber('id')->name('activities.store');
    Route::post('/{id}/attachments',   [DealRecordController::class, 'storeAttachment'])->whereNumber('id')->name('attachments.store');
    Route::patch('/{id}/stage',        [DealController::class, 'updateStage'])->whereNumber('id')->name('stage');
    Route::patch('/{id}',              [DealController::class, 'patch'])->whereNumber('id')->name('patch');
    Route::delete('/{id}',             [DealController::class, 'destroy'])->whereNumber('id')->name('destroy');
});


// contacts + companies (the lead is created from these)
Route::get('/leads/contacts/search', [LeadDirectoryController::class, 'contactsSearch'])->name('leads.contacts.search');
Route::post('/leads/contacts', [LeadDirectoryController::class, 'contactStore'])->name('leads.contacts.store');
Route::get('/leads/companies/search', [LeadDirectoryController::class, 'companiesSearch'])->name('leads.companies.search');
Route::post('/leads/companies', [LeadDirectoryController::class, 'companyStore'])->name('leads.companies.store');
Route::patch('/leads/companies/{id}', [LeadDirectoryController::class, 'companyUpdate'])->name('leads.companies.update');
// deal picker in the create-lead flow (keep above any  /leads/{id}  GET route)
Route::get('/leads/deals/search', [LeadDirectoryController::class, 'dealsSearch'])->name('leads.deals.search');

// bulk status change from the table's selection bar
Route::post('/leads/bulk-type', [LeadManageController::class, 'bulkType'])->name('leads.bulkType');

// click-to-edit in the drawer (partial update of one or more fields)
Route::patch('/leads/{id}', [LeadManageController::class, 'patch'])->name('leads.patch');

// full lead page — opens when a lead's name is clicked anywhere (contents come later)
Route::get('/leads/{id}/view', [LeadManageController::class, 'view'])->whereNumber('id')->name('leads.profile');

// snooze / reschedule a follow-up
Route::patch('/leads/reminders/{id}', [LeadManageController::class, 'updateReminder'])->whereNumber('id')->name('leads.reminders.update');
// Contacts directory
Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::get('/contacts/data', [ContactController::class, 'data'])->name('contacts.data');
Route::post('/contacts/bulk-delete', [ContactController::class, 'bulkDestroy'])->name('contacts.bulkDestroy');
Route::get('/contacts/{id}', [ContactController::class, 'show'])->whereNumber('id')->name('contacts.show');
Route::patch('/contacts/{id}', [ContactController::class, 'update'])->whereNumber('id')->name('contacts.update');
Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->whereNumber('id')->name('contacts.destroy');

// Companies directory
Route::get('/companies', [CompanyOpenController::class, 'index'])->name('companies.index');
Route::get('/companies/data', [CompanyOpenController::class, 'data'])->name('companies.data');
Route::post('/companies/bulk-delete', [CompanyOpenController::class, 'bulkDestroy'])->name('companies.bulkDestroy');
Route::get('/companies/{id}', [CompanyOpenController::class, 'show'])->whereNumber('id')->name('companies.show');
Route::delete('/companies/{id}', [CompanyOpenController::class, 'destroy'])->whereNumber('id')->name('companies.destroy');

// contacts + companies (the lead is created from these)
Route::get('/leads/contacts/search', [LeadDirectoryController::class, 'contactsSearch'])->name('leads.contacts.search');
Route::post('/leads/contacts', [LeadDirectoryController::class, 'contactStore'])->name('leads.contacts.store');
Route::get('/leads/companies/search', [LeadDirectoryController::class, 'companiesSearch'])->name('leads.companies.search');
Route::post('/leads/companies', [LeadDirectoryController::class, 'companyStore'])->name('leads.companies.store');
Route::patch('/leads/companies/{id}', [LeadDirectoryController::class, 'companyUpdate'])->name('leads.companies.update');

// bulk status change from the table's selection bar
Route::post('/leads/bulk-type', [LeadManageController::class, 'bulkType'])->name('leads.bulkType');

// click-to-edit in the drawer (partial update of one or more fields)
Route::patch('/leads/{id}', [LeadManageController::class, 'patch'])->name('leads.patch');





    Route::get('/leads', [LeadManageController::class, 'Leadmanage'])->name('leads.index');
    Route::get('/leads/data', [LeadManageController::class, 'data'])->name('leads.data');

    Route::post('/leads', [LeadManageController::class, 'store'])->name('leads.store');
    Route::put('/leads/{id}', [LeadManageController::class, 'update'])->name('leads.update');
    Route::get('/leads/{id}', [LeadManageController::class, 'show'])->name('leads.show');
    Route::delete('/leads/{id}', [LeadManageController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/bulk-delete', [LeadManageController::class, 'bulkDestroy'])->name('leads.bulkDestroy');
    Route::post('/leads/{id}/toggle-pin', [LeadManageController::class, 'togglePin'])->name('leads.togglePin');

    Route::post('/leads/reminders', [LeadManageController::class, 'storeReminder'])->name('leads.reminders.store');
    Route::post('/leads/reminders/{id}/status', [LeadManageController::class, 'updateReminderStatus'])->name('leads.reminders.status');
    Route::delete('/leads/reminders/{id}', [LeadManageController::class, 'destroyReminder'])->name('leads.reminders.destroy');
    Route::get('/leads/reminders/check', [LeadManageController::class, 'checkReminders'])->name('leads.reminders.check');

    // Inline status (lead type) update — called from the "view lead" panel.
Route::patch('/leads/{lead}/type', [LeadManageController::class, 'updateType'])
    ->name('leads.updateType');

// Reminders page — lists every reminder across every lead the admin can see.
Route::get('/reminders', [LeadManageController::class, 'remindersPage'])
    ->name('reminders.page');

Route::get('/reminders/data', [LeadManageController::class, 'remindersData'])
    ->name('reminders.data');

     /* ==================== BILLING ==================== */

    Route::get('/billing', [BillingManageController::class, 'billingPage'])->name('billing.index');
    Route::get('/billing/data', [BillingManageController::class, 'data'])->name('billing.data');
    Route::post('/billing', [BillingManageController::class, 'store'])->name('billing.store');
    Route::get('/billing/{id}', [BillingManageController::class, 'show'])->name('billing.show');
    Route::put('/billing/{id}', [BillingManageController::class, 'update'])->name('billing.update');
    Route::delete('/billing/{id}', [BillingManageController::class, 'destroy'])->name('billing.destroy');
    Route::post('/billing/bulk-delete', [BillingManageController::class, 'bulkDestroy'])->name('billing.bulkDestroy');
    Route::get('/billing/{id}/pdf', [BillingManageController::class, 'downloadPdf'])->name('billing.pdf');

    // Banks (added from the billing page's filter section)
    Route::post('/billing/banks', [BillingManageController::class, 'storeBank'])->name('billing.banks.store');
    Route::put('/billing/banks/{id}', [BillingManageController::class, 'updateBank'])->name('billing.banks.update');
    Route::delete('/billing/banks/{id}', [BillingManageController::class, 'destroyBank'])->name('billing.banks.destroy');

    // Billing headers / company profiles (added from the billing page's filter section)
    Route::post('/billing/headers', [BillingManageController::class, 'storeHeader'])->name('billing.headers.store');
    Route::put('/billing/headers/{id}', [BillingManageController::class, 'updateHeader'])->name('billing.headers.update');
    Route::delete('/billing/headers/{id}', [BillingManageController::class, 'destroyHeader'])->name('billing.headers.destroy');
});



Route::prefix('admin')->middleware(['auth:admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Meta Lead Management
    |--------------------------------------------------------------------------
    */

    // Page

Route::post('knowledge-base/regenerate-catalog', [App\Http\Controllers\KnowledgeBaseController::class, 'regenerateCatalogOverview'])
    ->name('kb.regenerateCatalog');

Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::get('tasks/data', [TaskController::class, 'data'])->name('tasks.data');
Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');

// NEW — bulk actions & checklist (literal paths, so they never clash with {task})
Route::post('tasks/bulk-status', [TaskController::class, 'bulkStatus'])->name('tasks.bulkStatus');
Route::post('tasks/bulk-assign', [TaskController::class, 'bulkAssign'])->name('tasks.bulkAssign');
Route::post('tasks/bulk-destroy', [TaskController::class, 'bulkDestroy'])->name('tasks.bulkDestroy');

Route::post('tasks/checklist', [TaskController::class, 'storeChecklistItem'])->name('tasks.checklist.store');
Route::patch('tasks/checklist/{item}', [TaskController::class, 'toggleChecklistItem'])->name('tasks.checklist.toggle');
Route::delete('tasks/checklist/{item}', [TaskController::class, 'destroyChecklistItem'])->name('tasks.checklist.destroy');

Route::post('tasks/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
Route::post('tasks/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
Route::delete('tasks/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');

Route::get('tasks-notifications/check', [TaskController::class, 'notificationsCheck'])->name('tasks.notifications.check');
Route::post('tasks-notifications/read', [TaskController::class, 'notificationsMarkRead'])->name('tasks.notifications.read');

// {task} routes last
Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
Route::post('tasks/{task}/duplicate', [TaskController::class, 'duplicate'])->name('tasks.duplicate');   // NEW
Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

});

Route::get('/admin/meta/webhook', [MetaLeadController::class, 'webhookVerify']);
Route::post('/admin/meta/webhook', [MetaLeadController::class, 'webhookReceive']);
Route::get('/admin/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/admin/whatsapp/webhook', [WhatsAppWebhookController::class, 'receive']);

Route::get('/admin/test-notify', function () {

    $admin = \App\Models\Admin::first();
    $task = \App\Models\Task::first();

    $admin->notify(new \App\Notifications\TaskAssignedNotification($task));

    return 'Done';
});


Route::prefix('admin')
    ->middleware(['auth:admin', 'super_admin'])
    ->group(function () {

        Route::get('/admin-manage', [PagesController::class, 'users'])
            ->name('admin.register');


  Route::get('/inventory', [InventoryManageController::class, 'inventoryPage'])->name('inventory.index');
    Route::get('/inventory/data', [InventoryManageController::class, 'data'])->name('inventory.data');
    Route::post('/inventory', [InventoryManageController::class, 'store'])->name('inventory.store');
    Route::put('/inventory/{id}', [InventoryManageController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{id}', [InventoryManageController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/bulk-delete', [InventoryManageController::class, 'bulkDestroy'])->name('inventory.bulkDestroy');

    // Quick-add endpoints used by the "+" buttons next to the product /
    // dealer search-selects on the stock-entry modal. storeProduct is also
    // reused as the plain "Add product" action from the Products tab.
    Route::post('/inventory/products', [InventoryManageController::class, 'storeProduct'])->name('inventory.products.store');
    Route::post('/inventory/dealers', [InventoryManageController::class, 'storeDealer'])->name('inventory.dealers.store');

    // Products tab: full product list, detail view, edit, delete.
    // /inventory/products/data MUST be registered before the {id} routes
    // below so "data" doesn't get swallowed as an :id segment.
    Route::get('/inventory/products/data', [InventoryManageController::class, 'productsData'])->name('inventory.products.data');
    Route::get('/inventory/products/{id}', [InventoryManageController::class, 'productShow'])->name('inventory.products.show');
    Route::put('/inventory/products/{id}', [InventoryManageController::class, 'updateProduct'])->name('inventory.products.update');
    Route::delete('/inventory/products/{id}', [InventoryManageController::class, 'destroyProduct'])->name('inventory.products.destroy');

    // Product image gallery — add (multiple files) / remove (one at a time).
    Route::post('/inventory/products/{id}/images', [InventoryManageController::class, 'storeProductImages'])->name('inventory.products.images.store');
    Route::delete('/inventory/products/images/{imageId}', [InventoryManageController::class, 'destroyProductImage'])->name('inventory.products.images.destroy');


     Route::get('/meta-leads', [MetaLeadController::class, 'index'])->name('metaLeads.index');

    // Ajax
    Route::get('/meta-leads/data', [MetaLeadController::class, 'data'])->name('metaLeads.data');

    // Single Lead
    Route::get('/meta-leads/{id}', [MetaLeadController::class, 'show'])->name('metaLeads.show');

    // Convert to CRM Lead
    Route::post('/meta-leads/{id}/convert', [MetaLeadController::class, 'convert'])->name('metaLeads.convert');

    // Discard
    Route::post('/meta-leads/{id}/discard', [MetaLeadController::class, 'discard'])->name('metaLeads.discard');

    // Delete
    Route::delete('/meta-leads/{id}', [MetaLeadController::class, 'destroy'])->name('metaLeads.destroy');

    // Bulk Delete
    Route::post('/meta-leads/bulk-delete', [MetaLeadController::class, 'bulkDestroy'])->name('metaLeads.bulkDestroy');


    Route::prefix('knowledge-base')->name('kb.')->group(function () {
    // Page
    Route::get('/', [KnowledgeBaseController::class, 'index'])->name('index');
    // Ajax list
    Route::get('/data', [KnowledgeBaseController::class, 'data'])->name('data');

    // CRUD
    Route::get('/{id}', [KnowledgeBaseController::class, 'show'])->name('show');
    Route::post('/', [KnowledgeBaseController::class, 'store'])->name('store');
    Route::put('/{id}', [KnowledgeBaseController::class, 'update'])->name('update');
    Route::delete('/{id}', [KnowledgeBaseController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-delete', [KnowledgeBaseController::class, 'bulkDestroy'])->name('bulkDestroy');

    // Approval workflow
    Route::post('/{id}/submit', [KnowledgeBaseController::class, 'submit'])->name('submit');
    Route::post('/{id}/approve', [KnowledgeBaseController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [KnowledgeBaseController::class, 'reject'])->name('reject');
    Route::post('/{id}/archive', [KnowledgeBaseController::class, 'archive'])->name('archive');

    // Version history
    Route::post('/{id}/restore-version', [KnowledgeBaseController::class, 'restoreVersion'])->name('restoreVersion');

    // Media (PDF / Image uploads)
    Route::post('/{id}/media', [KnowledgeBaseController::class, 'uploadMedia'])->name('media.upload');
    Route::delete('/media/{mediaId}', [KnowledgeBaseController::class, 'destroyMedia'])->name('media.destroy');

    // Categories
    Route::post('/categories', [KnowledgeBaseController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{id}', [KnowledgeBaseController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [KnowledgeBaseController::class, 'destroyCategory'])->name('categories.destroy');
});



});

// Ye wala group jaisa hai waisa rehne do (kb, chatbot-settings, inventory, meta-leads):
Route::prefix('admin')
    ->middleware(['auth:admin', 'super_admin'])
    ->group(function () {

        // ... inventory, meta-leads, knowledge-base ...

        Route::prefix('chatbot-settings')->name('chatbotSettings.')->middleware('super_admin')->group(function () {
            Route::get('/', [ChatbotSettingController::class, 'index'])->name('index');
            Route::get('/data', [ChatbotSettingController::class, 'show'])->name('show');
            Route::put('/', [ChatbotSettingController::class, 'update'])->name('update');
            Route::post('/regenerate-webhook-token', [ChatbotSettingController::class, 'regenerateWebhookToken'])->name('regenerateToken');
        });
});

// Naya, alag group — sirf auth:admin, koi bhi admin/agent access kar sake:
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/data', [ChatController::class, 'data'])->name('data');
        Route::get('/employees', [ChatController::class, 'employees'])->name('employees');
        Route::get('/{id}', [ChatController::class, 'show'])->name('show');
    Route::post('/{id}/send-media', [ChatController::class, 'sendMedia'])->name('chat.sendMedia');

        Route::post('/{id}/send', [ChatController::class, 'send'])->name('send');
        Route::post('/{id}/takeover', [ChatController::class, 'takeover'])->name('takeover');
        Route::post('/{id}/resume-ai', [ChatController::class, 'resumeAi'])->name('resumeAi');
        Route::post('/{id}/transfer', [ChatController::class, 'transfer'])->name('transfer');
        Route::post('/{id}/assign', [ChatController::class, 'assign'])->name('assign');
        Route::post('/{id}/lock', [ChatController::class, 'lock'])->name('lock');
        Route::post('/{id}/unlock', [ChatController::class, 'unlock'])->name('unlock');
        Route::patch('/{id}/ai-toggle', [ChatController::class, 'aiToggle'])->name('aiToggle');

        Route::post('/{id}/notes', [ChatController::class, 'storeNote'])->name('notes.store');
        Route::post('/{id}/convert-to-lead', [ChatController::class, 'convertToLead'])->name('convertToLead');
    });
    Route::post('/leads/attachments', [LeadManageController::class, 'storeAttachment'])->name('leads.attachments.store');
Route::delete('/leads/attachments/{id}', [LeadManageController::class, 'destroyAttachment'])->name('leads.attachments.destroy');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
});


// Software owner console
Route::prefix(config('console.path'))->name('console.')->middleware('console.gate')->group(function () {
    Route::get('login', [Console::class, 'showLogin'])->name('login');
    Route::post('login', [Console::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('two-factor', [Console::class, 'showTwoFactor'])->name('2fa');
    Route::post('two-factor', [Console::class, 'verifyTwoFactor'])->middleware('throttle:10,1')->name('2fa.verify');
Route::get('modules', [PlanModuleController::class, 'index'])->name('modules.index');
Route::post('modules', [PlanModuleController::class, 'store'])->name('modules.store');
Route::put('modules/{module}', [PlanModuleController::class, 'update'])->name('modules.update');
Route::patch('modules/{module}/status', [PlanModuleController::class, 'status'])->name('modules.status');
Route::delete('modules/{module}', [PlanModuleController::class, 'destroy'])->name('modules.destroy');
    Route::middleware('console.auth')->group(function () {
        Route::get('/', fn () => view('superadminconsole.pages.console.dashboard'))->name('dashboard');
        Route::patch('/plans/{plan}/status', [PlanController::class, 'changeStatus'])
    ->name('plans.status');
    Route::post('plan-modules', [PlanModuleController::class, 'store'])->name('modules.store');
Route::delete('plan-modules/{module}', [PlanModuleController::class, 'destroy'])->name('modules.destroy');

Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])
    ->name('plans.destroy');
        Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('trials', [TrialController::class, 'index'])->name('trials.index');
        Route::get('directory', [DirectoryController::class, 'index'])->name('directory.index');
          Route::post('companies/{company}/send-reset-link', [CompanyController::class, 'sendResetLink'])->name('companies.reset-link');
        Route::post('companies/{company}/reset-password', [CompanyController::class, 'resetPassword'])->name('companies.password');
        Route::get('companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::get('companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
        Route::put('companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::post('companies/{company}/upgrade', [CompanyController::class, 'upgrade'])->name('companies.upgrade');
        Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
        Route::post('companies/{company}/extend-trial', [CompanyController::class, 'extendTrial'])->name('companies.extend');
        Route::post('companies/{company}/status', [CompanyController::class, 'setStatus'])->name('companies.status');
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('invoices/{invoice}/paid', [InvoiceController::class, 'markPaid'])->name('invoices.paid');
        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
        Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
         Route::get('settings/payments', [PaymentSettingsController::class, 'edit'])->name('payments.edit');
        Route::post('settings/payments', [PaymentSettingsController::class, 'update'])->name('payments.update');
        Route::post('settings/payments/test', [PaymentSettingsController::class, 'test'])->name('payments.test');
        Route::post('logout', [Console::class, 'logout'])->name('logout');
        Route::get('settings/cashfree', [\App\Http\Controllers\Console\CashfreeSettingsController::class, 'edit'])->name('cashfree.edit');
Route::post('settings/cashfree', [\App\Http\Controllers\Console\CashfreeSettingsController::class, 'update'])->name('cashfree.update');
Route::post('settings/cashfree/test', [\App\Http\Controllers\Console\CashfreeSettingsController::class, 'test'])->name('cashfree.test');
    });
});
