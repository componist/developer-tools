<x:miniHelper::layouts.package>
    <div>
        <div class="container px-3 mx-auto ">
            <div class="grid grid-cols-1 gap-7">
                <!-- container grid start-->

                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">Job Testing</p>
                    <pre data-enlighter-language="php">
                    
beforeEach(function () {
    // Any setup required before each test
    Queue::fake(); // Make sure the queue is faked before dispatching jobs
    Bus::fake();
});

//...

it('dispatches an import products from Api', function () {
    ImportFromApiStocksInStorageLocationJob::dispatch();
    // Queue::assertPushed(ImportFromApiStocksInStorageLocationJob::class);
    Bus::assertDispatched(ImportFromApiStocksInStorageLocationJob::class);
});

                    
                    </pre>
                </div>


                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">Auth Route Testing</p>
                    <pre data-enlighter-language="php">
                  
beforeEach(function () {
        $this->user = User::where('email', 'info@componist.de')->where('isAdmin', 1)->first();
        $this->url = 'your_url';
});


//...


it('can get a list of articles from storage locations from the API', function () {

    $url = $this->url;

    $response = Http::withHeaders([
        'Api-Key' => env('Api_API_KEY'),
        'accept' => 'application/json',
    ])->get($url);

    expect($response->status())->toEqual(200);
});

                 

it('call API get Project Inventories Stocke Items from Api', function () {
    $response = $this->actingAs($this->user)
        ->get('/admin/api/getProjectInventories');

    $response->assertStatus(200);
});

                  

it('test show login form', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

it('authenticated user routing test dashboard/profil', function () {
    $this->actingAs($this->user)
        ->get('/dashboard/profil')
        ->assertSeeLivewire('dashboard.profil');
});

                
                  </pre>
                </div>








                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">Route Testing - RoutingTest.php</p>
                    <pre data-enlighter-language="php">
                        

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

test('route stability', function () {

    $routes = collect(Route::getRoutes())
        ->filter(fn ($route) => in_array('GET', $route->methods()) &&
            ! str_contains($route->uri(), '{') &&
            ! str_contains($route->uri(), '_debugbar')
           
        )
        ->pluck('uri');

    foreach ($routes as $uri) {
        $response = $this->get("/$uri");

        if ($response->isOk()) {
            dump("🔍 Teste: /$uri");
        } else {
            dump("❌ Teste: /$uri");

            $errors[] = [
                'uri' => "/$uri",
                'status' => $response->status(),
                'redirect' => $response->isRedirection(),
                // 'content_snippet' => Str::limit(strip_tags($response->getContent()), 200),
            ];
        }
    }

    // Wenn Fehler aufgetreten sind: Zusammenfassung anzeigen und Test scheitern lassen
    expect($errors)->toBeEmpty("❌ Fehlerhafte Routen gefunden:\n".print_r($errors, true));

});
                        </pre>
                </div>

                <!-- container grid ende-->
            </div>
        </div>
    </div>
</x:miniHelper::layouts.package>
