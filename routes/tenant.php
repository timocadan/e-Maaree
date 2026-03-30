<?php

declare(strict_types=1);

use App\Http\Controllers\SuperAdmin\LevelsController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'tenant.active',
])->group(function () {

    Auth::routes();

    //Route::get('/test', 'TestController@index')->name('test');
    Route::get('/privacy-policy', 'HomeController@privacy_policy')->name('privacy_policy');
    Route::get('/terms-of-use', 'HomeController@terms_of_use')->name('terms_of_use');


    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', 'HomeController@dashboard')->name('home');
        Route::get('/home', 'HomeController@dashboard')->name('home');
        Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');

        Route::group(['prefix' => 'my_account'], function () {
            Route::get('/', 'MyAccountController@edit_profile')->name('my_account');
            Route::put('/', 'MyAccountController@update_profile')->name('my_account.update');
            Route::put('/change_password', 'MyAccountController@change_pass')->name('my_account.change_pass');
        });

        /*************** Support Team *****************/
        Route::group(['namespace' => 'SupportTeam',], function () {

            /*************** Students *****************/
            Route::group(['prefix' => 'students'], function () {
                Route::get('reset_pass/{st_id}', 'StudentRecordController@reset_pass')->name('st.reset_pass');
                Route::get('graduated', 'StudentRecordController@graduated')->name('students.graduated');
                Route::put('not_graduated/{id}', 'StudentRecordController@not_graduated')->name('st.not_graduated');
                Route::get('list/{class_id}', 'StudentRecordController@listByClass')->name('students.list')->middleware('teamSAT');
                Route::get('my_record', 'StudentRecordController@my_record')->name('students.my_record')->middleware('student');

                /* Promotions */
                Route::post('promote_selector', 'PromotionController@selector')->name('students.promote_selector');
                Route::get('promotion/manage', 'PromotionController@manage')->name('students.promotion_manage');
                Route::delete('promotion/reset/{pid}', 'PromotionController@reset')->name('students.promotion_reset');
                Route::delete('promotion/reset_all', 'PromotionController@reset_all')->name('students.promotion_reset_all');
                Route::get('promotion/{fc?}/{fs?}/{tc?}/{ts?}', 'PromotionController@promotion')->name('students.promotion');
                Route::post('promote/{fc}/{fs}/{tc}/{ts}', 'PromotionController@promote')->name('students.promote');

            });

            /*************** Users *****************/
            Route::group(['prefix' => 'users'], function () {
                Route::get('reset_pass/{id}', 'UserController@reset_pass')->name('users.reset_pass');
                Route::get('parents', 'UserController@parents')->name('users.parents');
                Route::post('parents/reset_pass/{id}', 'UserController@reset_parent_pass')->name('users.parents.reset_pass');
            });

            /*************** Payments *****************/
            Route::group(['prefix' => 'payments'], function () {

                Route::get('manage/{class_id?}', 'PaymentController@manage')->name('payments.manage');
                Route::get('invoice/{id}/{year?}', 'PaymentController@invoice')->name('payments.invoice');
                Route::get('receipts/{id}', 'PaymentController@receipts')->name('payments.receipts');
                Route::get('pdf_receipts/{id}', 'PaymentController@pdf_receipts')->name('payments.pdf_receipts');
                Route::post('select_year', 'PaymentController@select_year')->name('payments.select_year');
                Route::post('select_class', 'PaymentController@select_class')->name('payments.select_class');
                Route::delete('reset_record/{id}', 'PaymentController@reset_record')->name('payments.reset_record');
                Route::post('pay_now/{id}', 'PaymentController@pay_now')->name('payments.pay_now');
            });

            /*************** Pins *****************/
            Route::group(['prefix' => 'pins'], function () {
                Route::get('create', 'PinController@create')->name('pins.create');
                Route::get('/', 'PinController@index')->name('pins.index');
                Route::post('/', 'PinController@store')->name('pins.store');
                Route::get('enter/{id}', 'PinController@enter_pin')->name('pins.enter');
                Route::post('verify/{id}', 'PinController@verify')->name('pins.verify');
                Route::delete('/', 'PinController@destroy')->name('pins.destroy');
            });

            /*************** Marks *****************/
            Route::group(['prefix' => 'marks'], function () {

                // FOR teamSA
                Route::group(['middleware' => 'teamSA'], function () {
                    Route::get('batch_fix', 'MarkController@batch_fix')->name('marks.batch_fix');
                    Route::put('batch_update', 'MarkController@batch_update')->name('marks.batch_update');
                    Route::get('tabulation/{term?}/{class?}/{sec_id?}', 'MarkController@tabulation')->name('marks.tabulation');
                    Route::post('tabulation', 'MarkController@tabulation_select')->name('marks.tabulation_select');
                    Route::post('tabulation/publish', 'MarkController@tabulation_publish')->name('marks.tabulation_publish');
                });
                Route::get('tabulation/print/{term}/{class}/{sec_id}', 'MarkController@print_tabulation')->name('marks.print_tabulation');

                // FOR teamSAT
                Route::group(['middleware' => 'teamSAT'], function () {
                    Route::get('/', 'MarkController@index')->name('marks.index');
                    Route::get('manage/{term}/{class}/{section}/{subject}', 'MarkController@manage')->name('marks.manage');
                    Route::put('update/{term}/{class}/{section}/{subject}', 'MarkController@update')->name('marks.update');
                    Route::post('selector', 'MarkController@selector')->name('marks.selector');
                });

                Route::get('select_year/{id?}', 'MarkController@year_selector')->name('marks.year_selector');
                Route::post('select_year/{id?}', 'MarkController@year_selected')->name('marks.year_select');
                Route::get('show/{id}/{year}', 'MarkController@show')->name('marks.show');
                Route::get('print/{id}/annual/{year}', 'MarkController@print_annual_student')->name('marks.print_annual');
                Route::get('print/{id}/{term}/{year}', 'MarkController@print_view')->name('marks.print');

            });

            /*************** Attendance *****************/
            Route::group(['prefix' => 'attendance', 'middleware' => 'teamSAT'], function () {
                Route::get('/', 'AttendanceController@index')->name('attendance.index');
                Route::get('/grid', 'AttendanceController@showMarkingGrid')->name('attendance.show_marking_grid');
                Route::post('/', 'AttendanceController@store')->name('attendance.store');
                Route::get('/report', 'AttendanceController@report')->name('attendance.report')->middleware('teamSA');
                Route::post('/report', 'AttendanceController@showReport')->name('attendance.report_show')->middleware('teamSA');
            });

            Route::get('class_master', 'ClassMasterController@index')->name('class_master.dashboard');
            Route::post('class_master/generate_ranks', 'ClassMasterController@generateRanks')->name('class_master.generate_ranks');
            Route::get('class_master/print_finalized/{class_id}/{section_id}', 'ClassMasterController@printFinalizedRoster')->name('class_master.print_finalized');
            Route::get('students/search-parents', 'StudentRecordController@searchParents')->name('students.search_parents')->middleware('teamSA');
            Route::resource('students', 'StudentRecordController');
            Route::resource('users', 'UserController');
            Route::get('classes', 'MyClassController@index')->name('classes.index');
            Route::post('classes', 'MyClassController@store')->name('classes.store');
            Route::get('classes/create', 'MyClassController@create')->name('classes.create');
            Route::get('classes/{class_id}/edit', 'MyClassController@edit')->name('classes.edit');
            Route::put('classes/{class_id}', 'MyClassController@update')->name('classes.update');
            Route::delete('classes/{class_id}', 'MyClassController@destroy')->name('classes.destroy');
            Route::get('sections', 'SectionController@index')->name('sections.index');
            Route::post('sections', 'SectionController@store')->name('sections.store');
            Route::get('sections/{section_id}/edit', 'SectionController@edit')->name('sections.edit');
            Route::put('sections/{section_id}', 'SectionController@update')->name('sections.update');
            Route::delete('sections/{section_id}', 'SectionController@destroy')->name('sections.destroy');
            Route::resource('subjects', 'SubjectController');
            Route::get('grades', 'GradeController@index')->name('grades.index');
            Route::get('grades/create', 'GradeController@create')->name('grades.create');
            Route::post('grades', 'GradeController@store')->name('grades.store');
            Route::get('grades/{grade_id}/edit', 'GradeController@edit')->name('grades.edit');
            Route::put('grades/{grade_id}', 'GradeController@update')->name('grades.update');
            Route::get('grades/{grade_id}/delete', 'GradeController@destroy')->name('grades.delete');
            Route::resource('dorms', 'DormController');
            Route::resource('payments', 'PaymentController');

        });

        /************************ AJAX ****************************/
        Route::group(['prefix' => 'ajax'], function () {
            Route::get('get_lga/{state_id}', 'AjaxController@get_lga')->name('get_lga');
            Route::get('get_class_sections/{class_id}', 'AjaxController@get_class_sections')->name('get_class_sections');
            Route::get('get_class_subjects/{class_id}', 'AjaxController@get_class_subjects')->name('get_class_subjects');
            Route::get('get_next_admission_number/{class_id}', [\App\Http\Controllers\AjaxController::class, 'get_next_admission_number'])->name('ajax.get_next_admission_number');
        });

    });

    /************************ SUPER ADMIN ****************************/
    /* Level routes are inside tenancy middleware above; prefix is 'super_admin' so paths are /levels/... only */
    Route::group(['namespace' => 'SuperAdmin', 'middleware' => 'super_admin', 'prefix' => 'super_admin'], function () {

        Route::get('/settings', 'SettingController@index')->name('settings');
        Route::put('/settings', 'SettingController@update')->name('settings.update');
        Route::put('/settings/active_slot', 'SettingController@updateActiveSlot')->name('settings.active_slot');

        Route::get('/levels', [LevelsController::class, 'index'])->name('levels.index');
        Route::post('/levels', [LevelsController::class, 'store'])->name('levels.store');
        Route::post('/levels/{level_id}/update', [LevelsController::class, 'update'])->name('levels.update');
        Route::get('/levels/{level_id}/delete', [LevelsController::class, 'destroy'])->name('levels.delete');
        Route::post('/levels/template', [LevelsController::class, 'storeTemplate'])->name('levels.template.store');
        Route::put('/levels/template/{template_id}', [LevelsController::class, 'updateTemplate'])->name('levels.template.update');
        Route::delete('/levels/template/{template_id}', [LevelsController::class, 'destroyTemplate'])->name('levels.template.delete');
        Route::post('/levels/mapping', [LevelsController::class, 'saveMapping'])->name('levels.mapping.save');

    });

    /************************ PARENT ****************************/
    Route::group(['namespace' => 'MyParent', 'middleware' => 'my_parent',], function () {

        Route::get('/my_children', 'MyController@children')->name('my_children');

    });

});
