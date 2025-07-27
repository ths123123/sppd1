use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelRequestController;

Route::get('/travel-requests/{id}/status-json', [TravelRequestController::class, 'statusJson'])->name('travel-requests.status-json');
