<?php

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserTermController;
use App\Http\Controllers\Users\UserAuthController;
use App\Http\Controllers\Users\UserContactController;
use App\Http\Controllers\Users\UserPolicyController;
use App\Http\Controllers\Users\UserAboutUsController;
use App\Http\Controllers\Users\UserFaqController;
use App\Http\Controllers\Users\UserHomeController;
use App\Http\Controllers\Users\UserProvFavouriteController;
use App\Http\Controllers\Users\UserVoucherFavouriteController;
use App\Http\Controllers\Users\UserCartController;
use App\Http\Controllers\Users\UserCheckoutController;
use App\Http\Controllers\Users\UserFeedbackController;
use App\Http\Controllers\Users\UserVoucherstatusController;




use App\Http\Controllers\Providers\ProvTermController;
use App\Http\Controllers\Providers\ProviderAuthController;
use App\Http\Controllers\Providers\ProvContactController;
use App\Http\Controllers\Providers\ProvPolicyController;
use App\Http\Controllers\Providers\ProvAboutUsController;
use App\Http\Controllers\Providers\ProvFaqController;
use App\Http\Controllers\Providers\ProvVoucherController;
use App\Http\Controllers\Providers\ProvSubscriptionController;
use App\Http\Controllers\Providers\ProvWalletController;
use App\Http\Controllers\Providers\ProvScanController;
use App\Http\Controllers\Providers\ProvHomeController;
use App\Http\Controllers\Providers\ProviderWithdrawalRequestController;
use App\Http\Controllers\Providers\ProvTransactionController;






use App\Http\Controllers\Admin\AdminAuthConroller;
use App\Http\Controllers\Admin\AdminCountryController;
use App\Http\Controllers\Admin\AdminContactController;
use App\Http\Controllers\Admin\AdminTermTranslationController;
use App\Http\Controllers\Admin\AdminPolicyTranslationController;
use App\Http\Controllers\Admin\AdminAboutUsTranslationController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminFaqController;
use App\Http\Controllers\Admin\AdminVoucherController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Admin\AdminSystemReportController;






/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('admin/login', [AdminAuthConroller::class, 'login'])->name('admin.login');


Route::group(['middleware' => ['custom.permission']], function () {

    // Admin Part
    Route::prefix('admin')->group(function () {

        // Show feedbacks
        Route::get('/feedbacks', [UserFeedbackController::class, 'show']);

        Route::middleware('auth:api')->group(function () {
            // Show Static pages
            Route::get('/policies/{id}', [AdminPolicyTranslationController::class, 'show']);
            Route::get('/about-us/{id}', [AdminAboutUsTranslationController::class, 'show']);
            Route::get('/terms/{id}', [AdminTermTranslationController::class, 'show'])->name('admin.terms.show');

            //Create Static pages
            Route::post('create/terms', [AdminTermTranslationController::class, 'create'])->name('admin.terms.store');

            Route::post('create/policies', [AdminPolicyTranslationController::class, 'create']);
            Route::post('create/about-us', [AdminAboutUsTranslationController::class, 'create']);

            Route::get('/vouchers', [AdminVoucherController::class, 'getVouchers']);


            // Show and read contacts
            Route::get('contacts', [AdminContactController::class, 'show']);
            Route::get('/read-contacts/{id}', [AdminContactController::class, 'markAsRead']);

            Route::post('/logout', [AdminAuthConroller::class, 'logout']);

            // Admin create provider cruds
            Route::post('/create-provider', [AdminAuthConroller::class, 'createProvider']);
            Route::post('/update-provider/{id}', [AdminAuthConroller::class, 'updateProvider']);
            Route::get('/show-provider/{id}', [AdminAuthConroller::class, 'showProvider']);
            Route::get('/showAll-providers', [AdminAuthConroller::class, 'showAllProviders']);
            Route::delete('/delete-provider/{id}', [AdminAuthConroller::class, 'deleteProvider']);



            // Admin create user cruds
            Route::post('/create-user', [AdminAuthConroller::class, 'createUser']);
            Route::post('/update-user/{id}', [AdminAuthConroller::class, 'updateUser']);
            Route::get('/show-user/{id}', [AdminAuthConroller::class, 'showUser']);
            Route::get('/showAll-users', [AdminAuthConroller::class, 'showAllUsers']);
            Route::delete('/delete-user/{id}', [AdminAuthConroller::class, 'deleteUser']);



            // Admin create admin cruds
            Route::post('/create-admin', [AdminAuthConroller::class, 'createAdmin']);
            Route::post('/update-admin/{id}', [AdminAuthConroller::class, 'updateAdmin']);
            Route::get('/show-admin/{id}', [AdminAuthConroller::class, 'showAdmin']);
            Route::get('/showAll-admins', [AdminAuthConroller::class, 'showAllAdmins']);
            Route::delete('/delete-admin/{id}', [AdminAuthConroller::class, 'deleteAdmin']);


            // System report
            Route::get('/allVouchers', [AdminSystemReportController::class, 'AdminGetAllVouchers']);
            Route::get('/allVouchers-status', [AdminSystemReportController::class, 'AdminGetAllStatusVouchers']);
            Route::get('/allRoles', [AdminSystemReportController::class, 'getAllUsersByRole']);
            Route::get('/counts', [AdminSystemReportController::class, 'getCounts']);



            // Approve and reject notifications of feedbacks
            Route::post('/approve-feedback/{feedbackId}', [AdminFeedbackController::class, 'approveFeedback'])->name('admin.approve-feedback');
            Route::post('/reject-feedback/{feedbackId}', [AdminFeedbackController::class, 'rejectFeedback']);

            // Approve and reject notifications of withdrawal requests
            Route::post('/approve-withdrawal/{withdrawal_id}', [AdminWithdrawalController::class, 'approveWithdrawal']);
            Route::post('/reject-withdrawal/{withdrawal_id}', [AdminWithdrawalController::class, 'rejectWithdrawal']);


            // Admin send bulk notifications & read messages
            Route::post('/send-bulk-notifications', [AdminNotificationController::class, 'sendBulkNotifications']);
            Route::get('/notifications', [AdminNotificationController::class, 'getNotificationsAndMarkAsRead']);



            // Subscription Crud
            Route::prefix('subscriptions')->group(function () {
                Route::get('/', [AdminSubscriptionController::class, 'index']); // إحضار كل الاشتراكات
                Route::get('/{subscription}', [AdminSubscriptionController::class, 'show']); // إحضار كل الاشتراكات
                Route::post('/', [AdminSubscriptionController::class, 'create']); // إنشاء اشتراك جديد
                Route::post('/{subscription}', [AdminSubscriptionController::class, 'update']); // تحديث اشتراك
                Route::delete('/{subscription}', [AdminSubscriptionController::class, 'destroy']); // حذف اشتراك
            });


            // Country Codes Crud
            Route::prefix('countries')->group(function () {
                Route::get('/', [AdminCountryController::class, 'index']); // Get all countries
                Route::post('/', [AdminCountryController::class, 'create']); // Add a new country
                Route::get('/{id}', [AdminCountryController::class, 'show']); // Get a specific country
                Route::post('/{id}', [AdminCountryController::class, 'update']); // Update a country
                Route::delete('/{id}', [AdminCountryController::class, 'destroy']); // Delete a country
            });

            // Group for Categories CRUD
            Route::prefix('categories')->group(function () {
                Route::get('/', [AdminCategoryController::class, 'index']);          // Get all categories
                Route::post('/', [AdminCategoryController::class, 'create']);       // Create new category
                Route::get('/{id}', [AdminCategoryController::class, 'show']);      // Show a single category
                Route::post('/{id}', [AdminCategoryController::class, 'update']);    // Update a category
                Route::delete('/{id}', [AdminCategoryController::class, 'destroy']); // Delete a category
            });


            // Group for Categories CRUD
            Route::prefix('faq')->group(function () {
                Route::get('/', [AdminFaqController::class, 'index']);          // Get all categories
                Route::post('/', [AdminFaqController::class, 'create']);       // Create new category
                Route::get('/{id}', [AdminFaqController::class, 'show']);      // Show a single category
                Route::post('/{id}', [AdminFaqController::class, 'update']);    // Update a category
                Route::delete('/{id}', [AdminFaqController::class, 'destroy']); // Delete a category
            });
        });
    });
});





// User Part
Route::prefix('user')->group(function () {

    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login', [UserAuthController::class, 'login']);
    Route::delete('/delete', [UserAuthController::class, 'deleteAccount']);


    // static pages
    Route::get('/terms/{id}', [UserTermController::class, 'show']);
    Route::get('/policies/{id}', [UserPolicyController::class, 'show']);
    Route::get('/about-us/{id}', [UserAboutUsController::class, 'show']);


    Route::post('contacts', [UserContactController::class, 'create']);
    Route::get('faq/', [UserFaqController::class, 'index']);
    Route::get('/feedbacks', [UserFeedbackController::class, 'show']);


    Route::get('/home', [UserHomeController::class, 'home']);
    Route::get('/search', [UserHomeController::class, 'searchProvider']);
    Route::get('/search/{categoryId}', [UserHomeController::class, 'searchProvidersByCategory']);
    Route::get('/{categoryId}/providers', [UserHomeController::class, 'getProvidersByCategory']); // 🔍 البحث عن فئة
    Route::get('/provider-details/{id}', [UserHomeController::class, 'providerDetails']); // 🔍 البحث عن فئة


    Route::get('/categories', [UserHomeController::class, 'getAllCategories']); // ✅ جلب كل الفئات
    Route::get('/search-category', [UserHomeController::class, 'searchCategory']); // 🔍 البحث عن فئة

    // Two points of verfiy account to login 
    Route::post('/resend-code', [UserAuthController::class, 'resendResetCode']);
    Route::post('/verify', [UserAuthController::class, 'verifyResetCode']);

    // Three points of forget password
    Route::post('/send-verification-code', [UserAuthController::class, 'sendVerificationCode']);
    Route::post('/verify-code', [UserAuthController::class, 'verifyCode']);
    Route::post('/reset-password', [UserAuthController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {

        // Profile
        Route::get('/show-profile', [UserAuthController::class, 'profile']);
        Route::post('/update-profile', [UserAuthController::class, 'updateProfile']);
        Route::post('/change-password', [UserAuthController::class, 'changePassword']);

        // create Feedback
        Route::post('/create-feed', [UserFeedbackController::class, 'create']);
        Route::post('/logout', [UserAuthController::class, 'logout']);


        // Read Notication 
        Route::get('/notifications', [AdminNotificationController::class, 'getNotificationsAndMarkAsRead']);

        // User delete notifications
        Route::delete('/notifications/{id}', [AdminNotificationController::class, 'destroy']);
        Route::delete('/notifications', [AdminNotificationController::class, 'destroyAll']);


        // Provider favourite
        Route::post('/toggle-favorite', [UserProvFavouriteController::class, 'toggleFavoriteProvider']);
        Route::get('/favorite-providers', [UserProvFavouriteController::class, 'getFavoriteProviders']);

        // Voucher favourite
        Route::post('/toggle-fav', [UserVoucherFavouriteController::class, 'toggleFavoriteVoucher']);
        Route::get('/favorite-vouchers', [UserVoucherFavouriteController::class, 'getFavoriteVouchers']);

        // Get status of voucher of all providers(active,used,expired)
        Route::get('/get-status', [UserVoucherstatusController::class, 'getUserVouchers']);
        Route::get('/voucher-details/{purchaseId}', [UserVoucherstatusController::class, 'getUserVoucherById']);



        // Add to cart 
        Route::prefix('cart')->group(function () {
            Route::post('/add/{voucherId}', [UserCartController::class, 'addVoucherToCart']);
            Route::delete('/remove/{voucherId}', [UserCartController::class, 'removeVoucherFromCart']);
            Route::get('/show', [UserCartController::class, 'getCartDetails']);
        });

        Route::post('/checkout', [UserCheckoutController::class, 'checkoutOrder']);
    });
});


// Provider Part
Route::prefix('provider')->group(function () {

    // Statc pages
    Route::get('/terms/{id}', [ProvTermController::class, 'show']);
    Route::get('/policies/{id}', [ProvPolicyController::class, 'show']);
    Route::get('/about-us/{id}', [ProvAboutUsController::class, 'show']);


    Route::post('contacts', [ProvContactController::class, 'create']);
    Route::get('faq/', [ProvFaqController::class, 'index']);

    Route::post('login', [ProviderAuthController::class, 'login']);

    // Three points of forget password
    Route::post('/send-verification-code', [ProviderAuthController::class, 'sendVerificationCode']);
    Route::post('/verify-code', [ProviderAuthController::class, 'verifyCode']);
    Route::post('/reset-password', [ProviderAuthController::class, 'resetPassword']);

    Route::get('/feedbacks', [UserFeedbackController::class, 'show']);

    Route::middleware('auth:api')->group(function () {

        // Home
        Route::get('/home', [ProvHomeController::class, 'home']);
        Route::get('/{voucherId}/voucher-status', [ProvHomeController::class, 'getUsersByVoucherStatus']);


        // Profile
        Route::get('/show-profile', [ProviderAuthController::class, 'profile']);
        Route::post('/update-profile', [ProviderAuthController::class, 'updateProfile']);
        Route::post('/change-password', [ProviderAuthController::class, 'changePassword']);

        // Scan code
        Route::post('/scan-qr/{id}', [ProvScanController::class, 'scanQrCode']);
        Route::get('/scanned-users', [ProvScanController::class, 'getScannedUsersByProvider']);



        // Read Notication 
        Route::get('/notifications', [AdminNotificationController::class, 'getNotificationsAndMarkAsRead']);

        // Provider delete notifications
        Route::delete('/notifications/{id}', [AdminNotificationController::class, 'destroy']);
        Route::delete('/notifications', [AdminNotificationController::class, 'destroyAll']);


        // Wallet ,Transactions, withdrawal
        Route::get('/show-wallet', [ProvWalletController::class, 'getWallet']);
        Route::post('/request-withdraw', [ProviderWithdrawalRequestController::class, 'withdraw']);

        Route::get('/show-transactions', [ProvTransactionController::class, 'getTransactions']);





        Route::post('/logout', [ProviderAuthController::class, 'logout']);

        // Subscription of provider
        Route::get('/subscriptions', [ProvSubscriptionController::class, 'showAvailableSubscriptions']);
        Route::get('/upgrade-subscriptions', [ProvSubscriptionController::class, 'showUpgradeSubscriptions']);
        Route::get('/sub-details/{id}', [ProvSubscriptionController::class, 'showOneSubscription']);

        Route::post('/subscribe', [ProvSubscriptionController::class, 'subscribeToPlan']);
        Route::post('/cancel-sub', [ProvSubscriptionController::class, 'cancelSubscription']);
        Route::post('/upgrade-sub', [ProvSubscriptionController::class, 'upgradeSubscription']);

        // Voucher Cruds
        Route::prefix('vouchers')->group(function () {
            Route::post('/', [ProvVoucherController::class, 'createVoucher']);
            Route::post('/{voucherId}', [ProvVoucherController::class, 'updateVoucher']);
            Route::get('/{voucherId}', [ProvVoucherController::class, 'getVoucher']);
            Route::get('/', [ProvVoucherController::class, 'getVouchers']);
            Route::delete('/{voucherId}', [ProvVoucherController::class, 'deleteVoucher']);
            Route::post('/toggle/{voucherId}', [ProvVoucherController::class, 'toggleVoucherStatus']); // تفعيل/إلغاء تفعيل القسيمة

        });

        Route::get('/search-vouchers', [ProvVoucherController::class, 'getVoucherByNameOrRandomNum']);
        Route::delete('/search-history/delete', [ProvVoucherController::class, 'deleteSearchHistory']);
    });
});
