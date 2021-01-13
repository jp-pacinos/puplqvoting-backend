<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Admin\App\SelectController;
use App\Http\Controllers\Features\Admin\Parties\PartyController;
use App\Http\Controllers\Features\Admin\Courses\CourseController;
use App\Http\Controllers\Features\Student\Voting\VotingController;
use App\Http\Controllers\Features\Admin\Sessions\SessionController;
use App\Http\Controllers\Features\Admin\Students\StudentController;
use App\Http\Controllers\Features\Admin\Officials\OfficialController;
use App\Http\Controllers\Features\Admin\Positions\PositionController;
use App\Http\Controllers\Features\Admin\Parties\MakeOfficialController;
use App\Http\Controllers\Features\Admin\Students\StudentGroupController;
use App\Http\Controllers\Features\Admin\Parties\StudentsSearchController;
use App\Http\Controllers\Features\Admin\Sessions\SessionActiveController;
use App\Http\Controllers\Features\Admin\Parties\OfficialPictureController;
use App\Http\Controllers\Features\Admin\Parties\OfficialPositionController;
use App\Http\Controllers\Auth\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Features\Student\Voting\StudentCodeSubmitController;
use App\Http\Controllers\Auth\Student\LoginController as StudentLoginController;
use App\Http\Controllers\Features\Admin\Sessions\Stats\StreamStats as ElectionStreamStats;
use App\Http\Controllers\Features\Admin\Sessions\ElectionController as SessionElectionController;
use App\Http\Controllers\Features\Admin\Sessions\Stats\StudentVoteStats as ElectionStudentVoteStats;
use App\Http\Controllers\Features\Admin\Sessions\Actions\SelectController as ElectionSelectController;
use App\Http\Controllers\Features\Admin\Sessions\Settings\FinishedController as ElectionFinishedController;
use App\Http\Controllers\Features\Admin\Sessions\Settings\SettingsController as ElectionSettingsController;
use App\Http\Controllers\Features\Admin\Sessions\Actions\StartElectionController as ElectionStartController;
use App\Http\Controllers\Features\Admin\Sessions\Settings\RegistrationController as ElectionRegistrationController;
use App\Http\Controllers\Features\Admin\Sessions\Settings\VerificationTypeController as ElectionVerificationTypeController;
use App\Http\Controllers\Features\Admin\Sessions\Actions\StartRegistrationController as ElectionStartRegistrationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

/**
 * Auth
 */

Route::prefix('auth/login')->group(function () {
    /**
     * login
     */
    Route::post('/admin', [AdminLoginController::class, 'index']);
    Route::post('/student', [StudentLoginController::class, 'index'])->middleware('voting.open');
});

/**
 * Students
 */

Route::prefix('voting/now')->middleware(['auth:sanctum'])->group(function () {
    /**
     * voting data
     */
    Route::get('/', [VotingController::class, 'index'])->middleware('student.canvote');
    Route::post('/', [VotingController::class, 'store'])->middleware('student.canvote');

    Route::post('/code/{history}/verify', [StudentCodeSubmitController::class, 'store'])->middleware(
        ['session.verification:code', 'student.canvote']
    );
});

/**
 * Administrator
 */

Route::prefix('r')->middleware(['auth:sanctum', 'sanctum.token:user:admin'])->group(function () {
    /**
     * maintenance
     */
    Route::apiResource('sessions', SessionController::class)->except('update');
    Route::apiResource('positions', PositionController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('officials', OfficialController::class);
    Route::apiResource('parties', PartyController::class);

    /**
     * session ~ election
     */
    Route::get('sessions/election/active', [SessionActiveController::class, 'index']);

    // election page
    Route::get('/sessions/{session}/dashboard', [SessionElectionController::class, 'index']);

    // election stats
    Route::prefix('sessions/{session}/stats')->group(function () {
        Route::get('/student-votes', [ElectionStudentVoteStats::class, 'index']);

        // new stats every n secs
        Route::get('/stream', [ElectionStreamStats::class, 'index']);
    });

    // election settins
    Route::get('sessions/{session}/settings', [ElectionSettingsController::class, 'index']);
    Route::prefix('sessions/{session}/settings')->middleware('cache.flush')->group(function () {
        /**
         * maintenance
         */
        Route::post('/details', [ElectionSettingsController::class, 'update']);
        Route::post('/finished', [ElectionFinishedController::class, 'store']);
        Route::post('/registration', [ElectionRegistrationController::class, 'store']);
        Route::post('/verification-type', [ElectionVerificationTypeController::class, 'store']);
    });

    // election reports
    Route::prefix('sessions/{session}/reports')->group(function () {
        // Route::get('/votes', [ElectionVoteResultsReport::class, 'index']);
    });

    // election actions
    Route::prefix('sessions/{session}/actions')->middleware('cache.flush')->group(function () {
        Route::apiResource('/select', ElectionSelectController::class)->only(['store', 'destroy']);
        Route::apiResource('/start', ElectionStartController::class)->only(['store', 'destroy']);
        Route::apiResource('/start-registration', ElectionStartRegistrationController::class)->only(['store', 'destroy']);
    });

    /**
     * party
     */
    Route::get('party/students/find', [StudentsSearchController::class, 'index']);

    // party ~ make official
    Route::apiResource('party/{party}/official', MakeOfficialController::class)->only([
        'store', 'destroy',
    ]);

    // party ~ official actions
    Route::prefix('party/official/{official}')->group(function () {
        Route::post('/picture', [OfficialPictureController::class, 'store']);
        Route::delete('/picture', [OfficialPictureController::class, 'destroy']);
        Route::patch('/position', [OfficialPositionController::class, 'update']);
    });

    /**
     * students group update and delete records
     */
    Route::post('students/group/update', [StudentGroupController::class, 'update']);
    Route::post('students/group/delete', [StudentGroupController::class, 'destroy']);
});

Route::prefix('selects')->middleware(['auth:sanctum', 'sanctum.token:user:admin'])->group(function () {
    /**
     * selects
     */
    Route::get('/', [SelectController::class, 'index']);
});
